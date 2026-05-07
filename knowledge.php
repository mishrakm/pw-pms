<?php
$page_title = 'Knowledge Center - PlusWealth PMS';
require_once __DIR__ . '/includes/db_config.php';
include 'header.php';

$downloads = [];

function get_file_meta(string $filename): array {
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $map = [
    'pdf' => ['label' => 'PDF', 'bg' => 'linear-gradient(135deg,#dc3545 0%,#b42318 100%)'],
    'doc' => ['label' => 'DOC', 'bg' => 'linear-gradient(135deg,#2b579a 0%,#1e4080 100%)'],
    'docx' => ['label' => 'DOCX', 'bg' => 'linear-gradient(135deg,#2b579a 0%,#1e4080 100%)'],
    'xls' => ['label' => 'XLS', 'bg' => 'linear-gradient(135deg,#217346 0%,#165c37 100%)'],
    'xlsx' => ['label' => 'XLSX', 'bg' => 'linear-gradient(135deg,#217346 0%,#165c37 100%)'],
    'ppt' => ['label' => 'PPT', 'bg' => 'linear-gradient(135deg,#d24726 0%,#b83b1d 100%)'],
    'pptx' => ['label' => 'PPTX', 'bg' => 'linear-gradient(135deg,#d24726 0%,#b83b1d 100%)'],
    'jpg' => ['label' => 'JPG', 'bg' => 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)'],
    'jpeg' => ['label' => 'JPEG', 'bg' => 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)'],
    'png' => ['label' => 'PNG', 'bg' => 'linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%)'],
    'zip' => ['label' => 'ZIP', 'bg' => 'linear-gradient(135deg,#6b7280 0%,#374151 100%)'],
    'txt' => ['label' => 'TXT', 'bg' => 'linear-gradient(135deg,#6b7280 0%,#374151 100%)'],
  ];

  return $map[$ext] ?? ['label' => strtoupper($ext ?: 'FILE'), 'bg' => 'linear-gradient(135deg,#015cd3 0%,#244ae2 100%)'];
}

try {
  $conn = get_db_connection();
  $result = $conn->query('SELECT * FROM downloads WHERE is_active = 1 ORDER BY upload_date DESC');
  if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
      $downloads[] = $row;
    }
  }
} catch (Throwable $e) {
  error_log('Error fetching downloads for knowledge center: ' . $e->getMessage());
}
?>

<div class="section-wrap" id="knowledge-center">
  <div class="inner">
    <div style="max-width:940px;margin:0 auto;">
      <div class="s-eyebrow reveal" style="justify-content:flex-start;">Knowledge Center</div>
      <h1 class="s-title reveal d1">Research notes, factsheets, and documents</h1>
      <p class="s-body reveal d2" style="margin-top:12px;">
        Access downloadable materials published by the PlusWealth team.
      </p>

      <?php if (empty($downloads)): ?>
        <div class="reveal d3" style="margin-top:28px;padding:22px;border:1px solid var(--border);border-radius:10px;background:var(--panel);">
          <h3 style="margin:0 0 8px 0;font-size:20px;">No files available yet</h3>
          <p style="margin:0;color:var(--ash);">Please check back soon for new resources.</p>
        </div>
      <?php else: ?>
        <div style="margin-top:26px;display:grid;gap:14px;">
          <?php foreach ($downloads as $index => $download): ?>
            <?php $meta = get_file_meta((string)($download['filename'] ?? '')); ?>
            <div class="reveal d<?= ($index % 5) + 1 ?>" style="display:grid;grid-template-columns:72px 1fr auto;gap:16px;align-items:center;padding:18px;border:1px solid var(--border);border-radius:10px;background:var(--panel);">
              <div style="width:56px;height:56px;border-radius:12px;background:<?= htmlspecialchars($meta['bg'], ENT_QUOTES) ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;letter-spacing:.8px;">
                <?= htmlspecialchars($meta['label'], ENT_QUOTES) ?>
              </div>

              <div>
                <h3 style="margin:0 0 6px 0;font-size:19px;color:var(--white);">
                  <?= htmlspecialchars((string)($download['title'] ?? ''), ENT_QUOTES) ?>
                </h3>
                <?php if (!empty($download['description'])): ?>
                  <p style="margin:0 0 8px 0;color:var(--ash);font-size:14px;line-height:1.55;">
                    <?= htmlspecialchars((string)$download['description'], ENT_QUOTES) ?>
                  </p>
                <?php endif; ?>
                <div style="font-size:12px;color:var(--ash);display:flex;gap:14px;flex-wrap:wrap;">
                  <span><?= htmlspecialchars(date('M d, Y', strtotime((string)($download['upload_date'] ?? 'now'))), ENT_QUOTES) ?></span>
                  <?php if (!empty($download['file_size'])): ?>
                    <span><?= htmlspecialchars(number_format(((float)$download['file_size']) / 1024, 2), ENT_QUOTES) ?> KB</span>
                  <?php endif; ?>
                  <?php if (!empty($download['downloads_count'])): ?>
                    <span><?= htmlspecialchars(number_format((int)$download['downloads_count']), ENT_QUOTES) ?> downloads</span>
                  <?php endif; ?>
                </div>
              </div>

              <div>
                <a href="download_file.php?id=<?= (int)$download['id'] ?>" class="btn-gold" style="padding:10px 18px;font-size:13px;white-space:nowrap;">Download</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="reveal d4" style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
        <a class="btn" href="contact.php">Request a document</a>
        <a class="btn ghost" href="index.php">Back to home</a>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
