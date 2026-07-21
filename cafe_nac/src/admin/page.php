<?php
/**
 * NAC Cafe - Admin Page Viewer
 * VULNERABLE: Local File Inclusion (Flag 6)
 */
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$page_title = 'Admin - Halaman';
$view = $_GET['view'] ?? 'articles';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>☕ Admin Panel</h3>
        <ul>
            <li><a href="/admin/">📊 Dashboard</a></li>
            <li><a href="/admin/page.php?view=articles" class="active">📝 Kelola Artikel</a></li>
            <li><a href="/admin/preview.php">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Kelola Halaman</h2>
            <p>Viewer untuk halaman internal</p>
        </div>

        <div class="preview-box">
            <?php
            // VULNERABILITY: Local File Inclusion (Flag 6)
            // Weak sanitization - str_replace can be bypassed with ....//
            $view = str_replace('../', '', $view);

            $file_path = __DIR__ . '/pages/' . $view . '.php';

            if (file_exists($file_path)) {
                include($file_path);
            } else {
                // Fallback: try without forced extension for flexibility
                // This allows reading PHP source as well
                $alt_path = __DIR__ . '/pages/' . $view;
                if (file_exists($alt_path)) {
                    echo '<pre>' . htmlspecialchars(file_get_contents($alt_path)) . '</pre>';
                } else {
                    echo '<div class="alert alert-warning">Halaman "' . safe_output($view) . '" tidak ditemukan.</div>';
                    echo '<p>Halaman yang tersedia:</p>';
                    echo '<ul><li><a href="?view=articles">articles</a></li></ul>';
                }
            }
            ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
