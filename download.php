<?php
$page_title = 'Downloads — PlusWealth PMS';
include 'header.php';
require_once __DIR__ . '/includes/db_config.php';

// Fetch active downloads
try {
    $conn = get_db_connection();
    $result = $conn->query("SELECT * FROM downloads WHERE is_active = 1 ORDER BY upload_date DESC");
    $downloads = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $downloads[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching downloads: " . $e->getMessage());
    $downloads = [];
}

// Function to get file icon and color based on extension
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => ['icon' => 'lni-files', 'color' => '#dc3545', 'bg' => 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)'],
        'doc' => ['icon' => 'lni-files', 'color' => '#2b579a', 'bg' => 'linear-gradient(135deg, #2b579a 0%, #1e4080 100%)'],
        'docx' => ['icon' => 'lni-files', 'color' => '#2b579a', 'bg' => 'linear-gradient(135deg, #2b579a 0%, #1e4080 100%)'],
        'xls' => ['icon' => 'lni-bar-chart', 'color' => '#217346', 'bg' => 'linear-gradient(135deg, #217346 0%, #165c37 100%)'],
        'xlsx' => ['icon' => 'lni-bar-chart', 'color' => '#217346', 'bg' => 'linear-gradient(135deg, #217346 0%, #165c37 100%)'],
        'ppt' => ['icon' => 'lni-gallery', 'color' => '#d24726', 'bg' => 'linear-gradient(135deg, #d24726 0%, #b83b1d 100%)'],
        'pptx' => ['icon' => 'lni-gallery', 'color' => '#d24726', 'bg' => 'linear-gradient(135deg, #d24726 0%, #b83b1d 100%)'],
        'jpg' => ['icon' => 'lni-image', 'color' => '#17a2b8', 'bg' => 'linear-gradient(135deg, #17a2b8 0%, #117a8b 100%)'],
        'jpeg' => ['icon' => 'lni-image', 'color' => '#17a2b8', 'bg' => 'linear-gradient(135deg, #17a2b8 0%, #117a8b 100%)'],
        'png' => ['icon' => 'lni-image', 'color' => '#17a2b8', 'bg' => 'linear-gradient(135deg, #17a2b8 0%, #117a8b 100%)'],
        'zip' => ['icon' => 'lni-archive', 'color' => '#6c757d', 'bg' => 'linear-gradient(135deg, #6c757d 0%, #545b62 100%)'],
        'txt' => ['icon' => 'lni-text-format', 'color' => '#6c757d', 'bg' => 'linear-gradient(135deg, #6c757d 0%, #545b62 100%)'],
    ];

    return $icons[$ext] ?? ['icon' => 'lni-file', 'color' => '#1029a6', 'bg' => 'linear-gradient(135deg, #1029a6 0%, #244ae2 100%)'];
}

function formatFileSize($bytes) {
    $size = (float) $bytes;
    if ($size <= 0) {
        return '-';
    }

    if ($size >= 1024 * 1024) {
        return number_format($size / (1024 * 1024), 2) . ' MB';
    }

    return number_format($size / 1024, 2) . ' KB';
}
?>

<div class="section-wrap" id="downloads">
    <div class="inner">
        <div class="s-eyebrow reveal">Resource Library</div>
        <h2 class="s-title reveal d1">Downloads<br><em>Latest Documents</em></h2>
        <p class="s-body reveal d2" style="margin-top: 16px; max-width: 680px;">
            Access our investment materials, fact sheets, disclosures, and related documents in one place.
        </p>

        <?php if (empty($downloads)): ?>
            <div class="reveal d3" style="margin-top: 36px; border: 1px solid var(--border); background: var(--panel); padding: 44px 28px; text-align: center;">
                <div style="font-family: var(--f-display); font-size: 34px; color: var(--brand-soft); margin-bottom: 8px;">No Downloads</div>
                <p style="font-size: 14px; color: var(--ash);">Check back soon for investment materials and documents.</p>
            </div>
        <?php else: ?>
            <div class="reveal d3" style="margin-top: 36px; overflow-x: auto; border: 1px solid var(--border); background: var(--panel);">
                <table style="width: 100%; min-width: 980px; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border2); background: var(--ink3);">
                            <th style="padding: 14px 16px; text-align: left; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: var(--slate); font-family: var(--f-mono);">Document</th>
                            <th style="padding: 14px 16px; text-align: left; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: var(--slate); font-family: var(--f-mono);">Uploaded</th>
                            <th style="padding: 14px 16px; text-align: right; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: var(--slate); font-family: var(--f-mono);">Size</th>
                            <th style="padding: 14px 16px; text-align: right; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: var(--slate); font-family: var(--f-mono);">Downloads</th>
                            <th style="padding: 14px 16px; text-align: center; font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: var(--slate); font-family: var(--f-mono);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($downloads as $download):
                            $fileIcon = getFileIcon($download['filename'] ?? '');
                            $uploadDate = !empty($download['upload_date']) ? date('M d, Y', strtotime($download['upload_date'])) : '-';
                            $fileSize = !empty($download['file_size']) ? formatFileSize($download['file_size']) : '-';
                            $downloadCount = !empty($download['downloads_count']) ? number_format((int) $download['downloads_count']) : '0';
                        ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 16px; vertical-align: top;">
                                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                                        <div style="width: 36px; height: 36px; border-radius: 6px; background: <?php echo htmlspecialchars($fileIcon['bg'], ENT_QUOTES, 'UTF-8'); ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="lni <?php echo htmlspecialchars($fileIcon['icon'], ENT_QUOTES, 'UTF-8'); ?>" style="font-size: 16px; color: var(--white);"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 15px; color: var(--white); font-weight: 500; line-height: 1.45;">
                                                <?php echo htmlspecialchars($download['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php if (!empty($download['description'])): ?>
                                                <div style="margin-top: 4px; font-size: 13px; color: var(--ash); line-height: 1.6;">
                                                    <?php echo htmlspecialchars($download['description'], ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 16px; color: var(--ash); font-size: 13px; white-space: nowrap; vertical-align: top;">
                                    <?php echo htmlspecialchars($uploadDate, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td style="padding: 16px; color: var(--ash); font-size: 13px; text-align: right; white-space: nowrap; vertical-align: top;">
                                    <?php echo htmlspecialchars($fileSize, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td style="padding: 16px; color: var(--ash); font-size: 13px; text-align: right; white-space: nowrap; vertical-align: top;">
                                    <?php echo htmlspecialchars($downloadCount, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td style="padding: 16px; text-align: center; vertical-align: top;">
                                    <a href="download_file.php?id=<?php echo urlencode((string) ($download['id'] ?? '')); ?>" class="btn-gold" style="padding: 8px 14px; font-size: 12px;">
                                        <i class="lni lni-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
