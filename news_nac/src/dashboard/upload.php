<?php
/**
 * Nac News Portal - File Upload
 * 
 * VULNERABILITY: Insecure File Upload (Flag 6)
 * 
 * The upload validation only checks Content-Type header (easily spoofable)
 * and the basic extension check doesn't block .phtml files.
 * Players can upload a PHP shell disguised as .phtml and read server files.
 * Flag: sk_live_pharos_9x7k2m (in /var/www/secrets/api_credentials.txt)
 */
$pageTitle = 'Upload Files';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$conn = getDbConnection();
$uploadMsg = null;
$uploadError = null;

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
// Intentionally incomplete extension blacklist - missing .phtml, .php5, .phar
$blockedExtensions = ['php', 'php3', 'php4', 'exe', 'sh', 'bat', 'cmd'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $uploadError = 'Invalid session token.';
    } else {
        $file = $_FILES['upload_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = 'Upload failed. Error code: ' . $file['error'];
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $uploadError = 'File too large. Maximum size is 5MB.';
        } else {
            // Check Content-Type (VULNERABILITY: only checks MIME from header, easily spoofable)
            if (!in_array($file['type'], $allowedTypes)) {
                $uploadError = 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP, PDF. Detected type: ' . e($file['type']);
            } else {
                $originalName = basename($file['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                // Check extension blacklist (VULNERABILITY: incomplete blacklist)
                if (in_array($ext, $blockedExtensions)) {
                    $uploadError = 'File extension .' . e($ext) . ' is not allowed for security reasons.';
                } else {
                    $newFilename = uniqid('upload_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
                    $uploadDir = __DIR__ . '/../uploads/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $destPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        // Record in database
                        $stmt = $conn->prepare("INSERT INTO uploaded_files (original_name, stored_name, file_path, file_size, mime_type, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $filePath = '/uploads/' . $newFilename;
                        $stmt->bind_param('sssisi', $originalName, $newFilename, $filePath, $file['size'], $file['type'], $_SESSION['user_id']);
                        $stmt->execute();
                        
                        $uploadMsg = 'File uploaded successfully! Path: <code>' . e($filePath) . '</code>';
                    } else {
                        $uploadError = 'Failed to save file to server.';
                    }
                }
            }
        }
    }
}

// Get recent uploads
$recentUploads = $conn->query("SELECT uf.*, u.username FROM uploaded_files uf LEFT JOIN users u ON uf.uploaded_by = u.id ORDER BY uf.uploaded_at DESC LIMIT 20");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
            <ul class="dashboard-nav">
                <li><a href="/dashboard/"><i class="fas fa-home"></i> Overview</a></li>
                <li><a href="/dashboard/articles.php"><i class="fas fa-newspaper"></i> Articles</a></li>
                <li><a href="/dashboard/create-article.php"><i class="fas fa-plus-circle"></i> New Article</a></li>
                <li><a href="/dashboard/upload.php" class="active"><i class="fas fa-cloud-upload-alt"></i> Upload Files</a></li>
                <li><a href="/dashboard/preview.php"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <h2><i class="fas fa-cloud-upload-alt"></i> Upload Files</h2>
            <p class="text-muted">Upload images and documents for use in your articles.</p>

            <?php if ($uploadMsg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $uploadMsg ?>
            </div>
            <?php endif; ?>

            <?php if ($uploadError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= e($uploadError) ?>
            </div>
            <?php endif; ?>

            <div class="upload-area" style="background:#f8f9fa;border:2px dashed #ccc;border-radius:12px;padding:40px;text-align:center;margin-bottom:30px;">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    
                    <div id="dropZone" style="cursor:pointer;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:48px;color:#999;margin-bottom:15px;"></i>
                        <h3 style="color:#555;">Drop files here or click to upload</h3>
                        <p style="color:#999;margin-bottom:20px;">Supported formats: JPEG, PNG, GIF, WebP, PDF (max 5MB)</p>
                        
                        <input type="file" name="upload_file" id="fileInput" style="display:none;" accept="image/*,.pdf">
                        <button type="button" onclick="document.getElementById('fileInput').click();" class="btn btn-primary">
                            <i class="fas fa-folder-open"></i> Choose File
                        </button>
                    </div>
                    
                    <div id="filePreview" style="display:none;margin-top:20px;">
                        <div id="previewContent"></div>
                        <p id="fileName" style="font-weight:600;margin:10px 0 5px;"></p>
                        <p id="fileSize" style="color:#666;margin:0 0 15px;"></p>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                        <button type="button" class="btn btn-outline" onclick="resetUpload()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Security Info (intentional hint) -->
            <div style="background:#e8f4f8;border:1px solid #bee5eb;border-radius:8px;padding:15px;margin-bottom:25px;">
                <h4><i class="fas fa-shield-alt" style="color:#0c5460;"></i> Upload Security</h4>
                <ul style="margin:8px 0 0;padding-left:20px;color:#0c5460;font-size:14px;">
                    <li>Files are validated by <strong>Content-Type</strong> header</li>
                    <li>Blocked extensions: .php, .php3, .php4, .exe, .sh, .bat, .cmd</li>
                    <li>Maximum file size: 5MB</li>
                    <li>Files are stored in the <code>/uploads/</code> directory</li>
                </ul>
            </div>

            <!-- Recent Uploads -->
            <h3 style="margin-bottom:15px;">Recent Uploads</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentUploads && $recentUploads->num_rows > 0): ?>
                        <?php while ($upload = $recentUploads->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <i class="fas fa-<?= strpos($upload['mime_type'], 'image') !== false ? 'image' : 'file-pdf' ?>"></i>
                                <?= e(truncate($upload['original_name'], 30)) ?>
                            </td>
                            <td><?= e($upload['mime_type']) ?></td>
                            <td><?= formatFileSize($upload['file_size']) ?></td>
                            <td><?= e($upload['username'] ?? 'Unknown') ?></td>
                            <td><?= formatDate($upload['uploaded_at'], 'M j, H:i') ?></td>
                            <td>
                                <a href="<?= e($upload['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:30px;color:#999;">No files uploaded yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatSize(file.size);
    document.getElementById('filePreview').style.display = 'block';
    document.getElementById('dropZone').querySelector('h3').style.display = 'none';
    document.getElementById('dropZone').querySelector('p').style.display = 'none';
    document.getElementById('dropZone').querySelector('button').style.display = 'none';

    const preview = document.getElementById('previewContent');
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML = '<img src="' + ev.target.result + '" style="max-width:200px;max-height:200px;border-radius:8px;">';
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<i class="fas fa-file" style="font-size:64px;color:#999;"></i>';
    }
});

function resetUpload() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').style.display = 'none';
    document.getElementById('dropZone').querySelector('h3').style.display = '';
    document.getElementById('dropZone').querySelector('p').style.display = '';
    document.getElementById('dropZone').querySelector('button').style.display = '';
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
