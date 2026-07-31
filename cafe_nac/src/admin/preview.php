<?php
/**
 * NAC Cafe - Admin URL Preview
 * VULNERABLE: Server-Side Request Forgery (Flag 7)
 */
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$page_title = 'Admin - Preview URL';
$preview_content = '';
$preview_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];

    // VULNERABILITY: No SSRF protection (Flag 7)
    // Allows fetching internal URLs
    if (empty($url)) {
        $preview_error = 'URL tidak boleh kosong.';
    } else {
        log_activity("URL Preview requested: " . $url);

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'NAC-Cafe-Preview-Bot/1.0'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $preview_error = 'Gagal mengambil URL: ' . $error;
        } elseif ($http_code >= 400) {
            $preview_error = 'URL mengembalikan status error: HTTP ' . $http_code;
        } else {
            $preview_content = $response;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>☕ Admin Panel</h3>
        <ul>
            <li><a href="/admin/">📊 Dashboard</a></li>
            <li><a href="/admin/page.php?view=articles">📝 Kelola Artikel</a></li>
            <li><a href="/admin/preview.php" class="active">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Preview URL Eksternal</h2>
            <p>Preview konten dari URL untuk referensi artikel. Fitur ini juga digunakan untuk monitoring perangkat internal.</p>
        </div>

        <div style="background: var(--bg-white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <form method="POST" id="previewForm">
                <div class="form-group">
                    <label for="url">URL</label>
                    <input type="text" id="url" name="url" placeholder="https://example.com/article" 
                           value="<?= safe_output($_POST['url'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">Preview</button>
            </form>
        </div>

        <?php if ($preview_error): ?>
            <div class="alert alert-error" style="margin-top: 20px;"><?= safe_output($preview_error) ?></div>
        <?php endif; ?>

        <?php if ($preview_content): ?>
        <div class="preview-box" style="margin-top: 20px;">
            <h4 style="margin-bottom: 12px;">Hasil Preview:</h4>
            <pre><?= htmlspecialchars($preview_content) ?></pre>
        </div>
        <?php endif; ?>

        <div class="alert alert-info" style="margin-top: 20px;">
            <strong>Tip:</strong> Gunakan fitur ini untuk preview konten artikel dari sumber berita lain sebagai referensi, atau untuk memeriksa status perangkat internal melalui endpoint monitoring.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
