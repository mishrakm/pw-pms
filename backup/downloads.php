<?php
/**
 * Downloads Page
 * Public-facing page listing all downloadable materials
 */
require_once __DIR__ . '/includes/db_config.php';
include 'includes/header.php';

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
?>

<!-- ========================= hero-section start ========================= -->
        <section id="home" class="hero-section" style="background-image: url('assets/img/money-coins-tree-growing-jar_50039-1102.jpg');">
            <div class="shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            <div class="hero-overlay"></div>
            <div class="container hero-container">
                <div class="row align-items-center h-100">
                    <div class="col-lg-8 mx-auto">
                        <div class="hero-content-wrapper text-center">
                            <h2 class="text-white wow fadeInDown" data-wow-delay=".2s">Downloads</h2>
                            <p class="text-white wow fadeInLeft"  data-wow-delay=".4s">Access our investment materials, fact sheets, and regulatory documents</p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= hero-section end ========================= -->


<!-- ========================= downloads-section start ========================= -->
<section id="downloads" class="downloads-section pt-120 pb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <?php if (empty($downloads)): ?>
                    <div class="no-downloads text-center" style="padding: 60px 20px;">
                        <i class="lni lni-download" style="font-size: 64px; color: var(--pw-blue); margin-bottom: 20px;"></i>
                        <h3 style="color: var(--pw-heading); margin-bottom: 15px;">No Downloads Available</h3>
                        <p style="color: var(--pw-text);">Check back soon for investment materials and documents.</p>
                    </div>
                <?php else: ?>
                    <div class="downloads-list">
                        <?php foreach ($downloads as $download): 
                            $fileIcon = getFileIcon($download['filename']);
                        ?>
                            <div class="download-item wow fadeInUp" data-wow-delay=".2s" style="background: white; border-radius: 12px; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); transition: all 0.3s;">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center mb-3 mb-md-0">
                                        <div class="file-icon" style="width: 60px; height: 60px; background: <?php echo $fileIcon['bg']; ?>; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                            <i class="lni <?php echo $fileIcon['icon']; ?>" style="font-size: 28px; color: white;"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h4 style="color: var(--pw-heading); margin-bottom: 8px; font-size: 20px;"><?php echo htmlspecialchars($download['title']); ?></h4>
                                        <?php if (!empty($download['description'])): ?>
                                            <p style="color: var(--pw-text); margin-bottom: 8px; font-size: 14px;"><?php echo htmlspecialchars($download['description']); ?></p>
                                        <?php endif; ?>
                                        <div style="font-size: 13px; color: var(--pw-muted);">
                                            <span><i class="lni lni-calendar"></i> <?php echo date('M d, Y', strtotime($download['upload_date'])); ?></span>
                                            <?php if ($download['file_size']): ?>
                                                <span style="margin-left: 15px;"><i class="lni lni-code-alt"></i> <?php echo number_format($download['file_size'] / 1024, 2); ?> KB</span>
                                            <?php endif; ?>
                                            <?php if ($download['downloads_count'] > 0): ?>
                                                <span style="margin-left: 15px;"><i class="lni lni-download"></i> <?php echo number_format($download['downloads_count']); ?> downloads</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
                                        <a href="download_file.php?id=<?php echo $download['id']; ?>" class="theme-btn" style="display: inline-block; padding: 12px 28px; text-decoration: none;">
                                            <i class="lni lni-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- ========================= downloads-section end ========================= -->

<style>
.download-item:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    transform: translateY(-2px);
}
</style>

<?php
include 'includes/footer.php';
?>
