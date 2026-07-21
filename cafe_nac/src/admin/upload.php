<?php
/**
 * NAC Cafe - Admin Media Upload
 * VULNERABLE: Insufficient file upload validation (Flag 11)
 * Only checks Content-Type header (easily spoofable)
 */
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$page_title = 'Admin - Upload Media';
$upload_success = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $file = $_FILES['media'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'Error saat upload file.';
    } elseif ($file['size'] > 5242880) {
        $upload_error = 'Ukuran file maksimal 5MB.';
    } else {
        // VULNERABILITY: Only checks Content-Type header (Flag 11)
        // Content-Type is sent by the client and easily spoofable
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed_types)) {
            $upload_error = 'Tipe file tidak diizinkan. Gunakan format gambar (JPEG, PNG, GIF, WebP).';
        } else {
            // Save with original filename (dangerous!)
            $upload_dir = __DIR__ . '/../uploads/articles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = basename($file['name']);
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $upload_success = 'File berhasil diupload: /uploads/articles/' . $filename;
                log_activity("File uploaded: " . $filename);
            } else {
                $upload_error = 'Gagal menyimpan file.';
            }
        }
    }
}

// List uploaded files
$upload_dir = __DIR__ . '/../uploads/articles/';
$uploaded_files = [];
if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploaded_files[] = [
                'name' => $f,
                'size' => filesize($upload_dir . $f),
                'time' => filemtime($upload_dir . $f)
            ];
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
            <li><a href="/admin/preview.php">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php" class="active">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Upload Media</h2>
            <p>Upload gambar untuk artikel dan konten</p>
        </div>

        <?php if ($upload_success): ?>
            <div class="alert alert-success"><?= safe_output($upload_success) ?></div>
        <?php endif; ?>
        <?php if ($upload_error): ?>
            <div class="alert alert-error"><?= safe_output($upload_error) ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div style="background: var(--bg-white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 24px;">
            <h3 style="margin-bottom: 16px;">Upload File Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="media">Pilih File Gambar</label>
                    <input type="file" id="media" name="media" accept="image/*" onchange="validateUpload(this)" required>
                    <p style="font-size: 12px; color: var(--text-light); margin-top: 4px;">
                        Format yang didukung: JPEG, PNG, GIF, WebP. Maksimal 5MB.
                    </p>
                </div>
                <button type="submit" class="btn-primary">Upload</button>
            </form>
        </div>

        <!-- Uploaded Files List -->
        <div style="background: var(--bg-white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 16px;">File yang Sudah Diupload</h3>

            <?php if (empty($uploaded_files)): ?>
                <p style="color: var(--text-light);">Belum ada file yang diupload.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uploaded_files as $f): ?>
                        <tr>
                            <td><?= safe_output($f['name']) ?></td>
                            <td><?= number_format($f['size'] / 1024, 1) ?> KB</td>
                            <td><?= date('Y-m-d H:i:s', $f['time']) ?></td>
                            <td><a href="/uploads/articles/<?= urlencode($f['name']) ?>" target="_blank" class="btn-small btn-secondary">Lihat</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
