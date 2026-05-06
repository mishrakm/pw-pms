#!/usr/bin/env python3
"""
git_to_ftp.py

Deploys a git working tree to FTP using a JSON config file.

Key behavior:
- Uses a "last deployed commit" state file to compute changes since last deploy.
- First deploy uploads everything under deploy_dir.
- Handles renames (R) and copies (C) properly.
- Optional deletion of remote files that were deleted (D) or renamed away (R).

Run:
  python git_to_ftp.py config.json
"""

import json
import os
import posixpath
import subprocess
import sys
from dataclasses import dataclass
from ftplib import FTP, error_perm
from typing import List, Optional, Tuple


# ----------------------------
# Git helpers
# ----------------------------
def run(cmd: List[str], cwd: str) -> str:
    p = subprocess.run(cmd, cwd=cwd, capture_output=True, text=True)
    if p.returncode != 0:
        raise RuntimeError(
            f"Command failed: {' '.join(cmd)}\n"
            f"cwd={cwd}\n"
            f"stdout:\n{p.stdout}\n"
            f"stderr:\n{p.stderr}"
        )
    return p.stdout


def run_bytes(cmd: List[str], cwd: str) -> bytes:
    p = subprocess.run(cmd, cwd=cwd, capture_output=True)
    if p.returncode != 0:
        raise RuntimeError(
            f"Command failed: {' '.join(cmd)}\n"
            f"cwd={cwd}\n"
            f"stderr:\n{p.stderr.decode(errors='replace')}"
        )
    return p.stdout


def get_head_commit(repo: str) -> str:
    out = run(["git", "rev-parse", "HEAD"], cwd=repo)
    return out.strip()


def ensure_branch(repo: str, branch: str) -> None:
    run(["git", "checkout", branch], cwd=repo)


def git_pull(repo: str, branch: str) -> None:
    ensure_branch(repo, branch)
    run(["git", "fetch", "origin"], cwd=repo)
    run(["git", "pull", "origin", branch], cwd=repo)


def read_last_deploy(repo: str, state_file: str) -> Optional[str]:
    p = os.path.join(repo, state_file)
    if not os.path.isfile(p):
        return None
    with open(p, "r", encoding="utf-8") as f:
        v = f.read().strip()
        return v or None


def write_last_deploy(repo: str, state_file: str, commit: str) -> None:
    p = os.path.join(repo, state_file)
    with open(p, "w", encoding="utf-8") as f:
        f.write(commit + "\n")


# ----------------------------
# Diff parsing (handles renames/copies + spaces in paths)
# ----------------------------
@dataclass
class DiffChange:
    status: str  # 'M','A','D','R','C',...
    old_path: Optional[str]  # for R/C: source path, for D: deleted path
    new_path: Optional[str]  # for R/C: destination path, for M/A: the path


def git_name_status_z(repo: str, rev_range: str) -> List[DiffChange]:
    """
    Uses: git diff --name-status -M -C -z <rev_range>
    Output is NUL separated and robust for spaces/unicode.
    For R/C: tokens are: "R100", "old", "new"
    For M/A/D: tokens are: "M", "path"
    """
    raw = run_bytes(["git", "diff", "--name-status", "-M", "-C", "-z", rev_range], cwd=repo)
    if not raw:
        return []

    # Split by NUL; last item may be empty.
    parts = raw.split(b"\x00")
    parts = [p for p in parts if p != b""]

    changes: List[DiffChange] = []
    i = 0
    while i < len(parts):
        status_tok = parts[i].decode("utf-8", errors="replace")
        i += 1

        if not status_tok:
            continue

        status_letter = status_tok[0]  # e.g. 'R' from 'R100'
        if status_letter in ("R", "C"):
            if i + 1 >= len(parts):
                break
            old_p = parts[i].decode("utf-8", errors="replace")
            new_p = parts[i + 1].decode("utf-8", errors="replace")
            i += 2
            changes.append(DiffChange(status=status_letter, old_path=old_p, new_path=new_p))
        else:
            if i >= len(parts):
                break
            pth = parts[i].decode("utf-8", errors="replace")
            i += 1
            if status_letter == "D":
                changes.append(DiffChange(status="D", old_path=pth, new_path=None))
            else:
                changes.append(DiffChange(status=status_letter, old_path=None, new_path=pth))

    return changes


def get_changes_since_last(repo: str, last_commit: str, head_commit: str) -> List[DiffChange]:
    rev_range = f"{last_commit}..{head_commit}"
    return git_name_status_z(repo, rev_range)


# ----------------------------
# FTP helpers
# ----------------------------
def ensure_remote_dirs(ftp: FTP, remote_dir: str) -> None:
    if remote_dir in ("", "/"):
        return

    parts = [p for p in remote_dir.split("/") if p]
    cur = "/" if remote_dir.startswith("/") else ""
    for part in parts:
        cur = posixpath.join(cur, part) if cur else part
        try:
            ftp.mkd(cur)
        except error_perm as e:
            # 550 usually means exists
            if not str(e).startswith("550"):
                raise


def upload_file(ftp: FTP, local_path: str, remote_path: str) -> None:
    ensure_remote_dirs(ftp, posixpath.dirname(remote_path))
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)


def delete_remote_file(ftp: FTP, remote_path: str) -> None:
    try:
        ftp.delete(remote_path)
    except error_perm as e:
        if not str(e).startswith("550"):
            raise


# ----------------------------
# Deploy helpers
# ----------------------------
def normalize_ftp_root(root: str) -> str:
    root = (root or "/").strip()
    if not root.startswith("/"):
        root = "/" + root
    root = root.rstrip("/")
    return root if root else "/"


def is_under_deploy_dir(rel_repo_path: str, deploy_dir: str) -> Tuple[bool, str]:
    """
    Returns (should_deploy, path_relative_to_deploy_dir)
    """
    rel_norm = os.path.normpath(rel_repo_path)

    if deploy_dir == ".":
        return True, rel_norm

    deploy_dir_norm = os.path.normpath(deploy_dir)
    prefix = deploy_dir_norm + os.sep

    if rel_norm == deploy_dir_norm:
        return True, ""
    if rel_norm.startswith(prefix):
        stripped = rel_norm[len(deploy_dir_norm):].lstrip(os.sep)
        return True, stripped

    return False, ""


def upload_all_under_deploy_dir(repo: str, deploy_dir: str, ftp: FTP, ftp_root: str) -> int:
    base = repo if deploy_dir == "." else os.path.join(repo, deploy_dir)
    if not os.path.isdir(base):
        raise RuntimeError(f"deploy_dir does not exist: {base}")

    uploaded = 0
    for root, _, files in os.walk(base):
        for fn in files:
            local_file = os.path.join(root, fn)
            rel_under_deploy = os.path.relpath(local_file, base).replace("\\", "/")
            remote_file = posixpath.join(ftp_root, rel_under_deploy)
            print(f"Upload (all): {rel_under_deploy}")
            upload_file(ftp, local_file, remote_file)
            uploaded += 1
    return uploaded


# ----------------------------
# Main
# ----------------------------
def main() -> None:
    if len(sys.argv) != 2:
        print("Usage: python git_to_ftp.py config.json")
        sys.exit(1)

    config_path = sys.argv[1]
    with open(config_path, "r", encoding="utf-8") as f:
        cfg = json.load(f)

    repo = os.path.abspath(cfg["repo_path"])
    branch = cfg.get("branch", "main")
    deploy_dir = os.path.normpath(cfg.get("deploy_dir", "."))
    delete_remote = bool(cfg.get("delete_remote", False))
    pull_before_deploy = bool(cfg.get("pull_before_deploy", False))
    state_file = cfg.get("deploy_state_file", ".last_deploy")

    if not os.path.isdir(repo):
        raise SystemExit(f"Repo folder does not exist: {repo}")

    # Ensure we're on expected branch
    if pull_before_deploy:
        print("⬇️ Pulling from origin before deploy...")
        git_pull(repo, branch)
    else:
        ensure_branch(repo, branch)

    head = get_head_commit(repo)
    last = read_last_deploy(repo, state_file)

    ftp_cfg = cfg["ftp"]
    ftp_host = ftp_cfg["host"]
    ftp_user = ftp_cfg["user"]

    if "password" in ftp_cfg:
        ftp_pass = ftp_cfg["password"]
    elif "password_env" in ftp_cfg:
        env_name = ftp_cfg["password_env"]
        ftp_pass = os.getenv(env_name)
        if not ftp_pass:
            raise RuntimeError(f"Environment variable not set: {env_name}")
    else:
        raise RuntimeError("FTP password not provided. Use ftp.password or ftp.password_env")

    ftp_root = normalize_ftp_root(ftp_cfg.get("root", "/"))

    # Decide what to deploy
    first_deploy = last is None
    changes: List[DiffChange] = []

    if first_deploy:
        print("🔍 No deploy state found; first deploy will upload EVERYTHING in deploy_dir.")
    else:
        print(f"🔍 Checking changes since last deploy: {last} -> {head}")
        changes = get_changes_since_last(repo, last, head)
        if not changes:
            print("✅ No changes to deploy.")
            return

        # Summarize
        counts = {}
        for c in changes:
            counts[c.status] = counts.get(c.status, 0) + 1
        summary = ", ".join([f"{k}:{v}" for k, v in sorted(counts.items())])
        print(f"📦 Changes: {summary}")

    # Connect to FTP
    print("🔌 Connecting to FTP...")
    ftp = FTP(ftp_host, timeout=60)
    ftp.login(ftp_user, ftp_pass)

    uploaded = 0
    try:
        if first_deploy:
            uploaded += upload_all_under_deploy_dir(repo, deploy_dir, ftp, ftp_root)
        else:
            # For renames: delete old remote path (if enabled) and upload new file
            for ch in changes:
                if ch.status in ("M", "A", "T"):  # treat type changes as upload
                    if not ch.new_path:
                        continue
                    should, rel_under = is_under_deploy_dir(ch.new_path, deploy_dir)
                    if not should:
                        continue
                    local_file = os.path.join(repo, os.path.normpath(ch.new_path))
                    if not os.path.isfile(local_file):
                        continue
                    remote_rel = rel_under.replace("\\", "/")
                    remote_file = posixpath.join(ftp_root, remote_rel)
                    print(f"Upload: {remote_rel}")
                    upload_file(ftp, local_file, remote_file)
                    uploaded += 1

                elif ch.status == "D":
                    if not delete_remote or not ch.old_path:
                        continue
                    should, rel_under = is_under_deploy_dir(ch.old_path, deploy_dir)
                    if not should or not rel_under:
                        continue
                    remote_rel = rel_under.replace("\\", "/")
                    remote_file = posixpath.join(ftp_root, remote_rel)
                    print(f"🗑️ Delete: {remote_file}")
                    delete_remote_file(ftp, remote_file)

                elif ch.status == "R":
                    # Rename: delete old, upload new
                    if delete_remote and ch.old_path:
                        should_old, rel_old = is_under_deploy_dir(ch.old_path, deploy_dir)
                        if should_old and rel_old:
                            remote_old = posixpath.join(ftp_root, rel_old.replace("\\", "/"))
                            print(f"🗑️ Delete (rename old): {remote_old}")
                            delete_remote_file(ftp, remote_old)

                    if ch.new_path:
                        should_new, rel_new = is_under_deploy_dir(ch.new_path, deploy_dir)
                        if should_new:
                            local_file = os.path.join(repo, os.path.normpath(ch.new_path))
                            if os.path.isfile(local_file):
                                remote_new = posixpath.join(ftp_root, rel_new.replace("\\", "/"))
                                remote_rel = rel_new.replace("\\", "/")
                                print(f"Upload (rename new): {remote_rel}")
                                upload_file(ftp, local_file, remote_new)
                                uploaded += 1

                elif ch.status == "C":
                    # Copy: upload new only
                    if not ch.new_path:
                        continue
                    should, rel_under = is_under_deploy_dir(ch.new_path, deploy_dir)
                    if not should:
                        continue
                    local_file = os.path.join(repo, os.path.normpath(ch.new_path))
                    if not os.path.isfile(local_file):
                        continue
                    remote_rel = rel_under.replace("\\", "/")
                    remote_file = posixpath.join(ftp_root, remote_rel)
                    print(f"Upload (copy): {remote_rel}")
                    upload_file(ftp, local_file, remote_file)
                    uploaded += 1

                else:
                    # Other statuses exist, e.g. 'U' (unmerged). Safer to stop.
                    raise RuntimeError(f"Unhandled git status '{ch.status}'. Resolve git state and retry.")

        ftp.quit()

    except Exception:
        try:
            ftp.quit()
        except Exception:
            pass
        raise

    write_last_deploy(repo, state_file, head)
    print(f"✅ Done. Uploaded {uploaded} file(s). Saved deploy state to {state_file}.")


if __name__ == "__main__":
    main()