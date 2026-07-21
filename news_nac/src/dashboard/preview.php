<?php
/**
 * Nac News Portal - URL Preview Tool
 * 
 * VULNERABILITY: Server-Side Request Forgery (SSRF)
 * Flag 5: v3.7.2-ptolemy
 * 
 * This tool fetches URLs for preview, but doesn't restrict internal URLs.
 * Players can access internal-api:5000/status to find the flag.
 */
$pageTitle = 'URL Preview';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$previewData = null;
$previewError = null;
$previewUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['url'])) {
    $previewUrl = $_POST['url'] ?? $_GET['url'] ?? '';
    
    if (!empty($previewUrl)) {
        // "Security" check - only block file:// protocol (intentionally incomplete)
        if (stripos($previewUrl, 'file://') === 0) {
            $previewError = 'file:// protocol is not allowed for security reasons.';
        } else {
            // Add http:// if no protocol specified
            if (!preg_match('/^https?:\/\//i', $previewUrl)) {
                $previewUrl = 'http://' . $previewUrl;
            }
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $previewUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'Nac-Bot/1.0 (URL Preview)',
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                $previewError = 'Failed to fetch URL: ' . $error;
            } elseif ($httpCode >= 400) {
                $previewError = "Server returned HTTP $httpCode";
            } else {
                $previewData = [
                    'url' => $effectiveUrl,
                    'http_code' => $httpCode,
                    'content_type' => $contentType,
                    'body' => $response,
                ];
                
                // Try to extract title and meta
                if (stripos($contentType, 'text/html') !== false) {
                    if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $response, $m)) {
                        $previewData['title'] = html_entity_decode(trim($m[1]));
                    }
                    if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)/si', $response, $m)) {
                        $previewData['description'] = html_entity_decode(trim($m[1]));
                    }
                    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']*)/si', $response, $m)) {
                        $previewData['image'] = $m[1];
                    }
                }
            }
        }
    }
}

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
                <li><a href="/dashboard/upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Files</a></li>
                <li><a href="/dashboard/preview.php" class="active"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <h2><i class="fas fa-external-link-alt"></i> URL Preview Tool</h2>
            <p class="text-muted">Preview external content before embedding in your articles. Paste a URL to fetch its metadata and content preview.</p>

            <form method="POST" class="preview-form" style="margin-bottom:30px;">
                <div class="form-group">
                    <label for="url">URL to Preview</label>
                    <div style="display:flex;gap:10px;">
                        <input type="text" id="url" name="url" class="form-control" 
                               placeholder="https://example.com/article" 
                               value="<?= e($previewUrl) ?>" required style="flex:1;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Preview
                        </button>
                    </div>
                    <small class="form-help">Enter a full URL including protocol (http/https)</small>
                </div>
            </form>

            <?php if ($previewError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= e($previewError) ?>
            </div>
            <?php endif; ?>

            <?php if ($previewData): ?>
            <div class="preview-result" style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:25px;">
                <h3 style="margin-bottom:15px;color:#333;">Preview Result</h3>
                
                <div class="preview-meta" style="margin-bottom:20px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="padding:5px 15px 5px 0;font-weight:600;color:#666;width:120px;">URL:</td>
                            <td style="padding:5px 0;"><?= e($previewData['url']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 15px 5px 0;font-weight:600;color:#666;">HTTP Status:</td>
                            <td style="padding:5px 0;"><?= $previewData['http_code'] ?></td>
                        </tr>
                        <tr>
                            <td style="padding:5px 15px 5px 0;font-weight:600;color:#666;">Content-Type:</td>
                            <td style="padding:5px 0;"><?= e($previewData['content_type']) ?></td>
                        </tr>
                        <?php if (!empty($previewData['title'])): ?>
                        <tr>
                            <td style="padding:5px 15px 5px 0;font-weight:600;color:#666;">Title:</td>
                            <td style="padding:5px 0;"><?= e($previewData['title']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($previewData['description'])): ?>
                        <tr>
                            <td style="padding:5px 15px 5px 0;font-weight:600;color:#666;">Description:</td>
                            <td style="padding:5px 0;"><?= e($previewData['description']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <?php if (!empty($previewData['image'])): ?>
                <div style="margin-bottom:15px;">
                    <strong>OG Image:</strong><br>
                    <img src="<?= e($previewData['image']) ?>" alt="Preview" style="max-width:400px;max-height:250px;margin-top:8px;border-radius:4px;border:1px solid #ddd;">
                </div>
                <?php endif; ?>

                <div class="preview-body">
                    <h4 style="margin-bottom:10px;">Response Body:</h4>
                    <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:15px;max-height:400px;overflow:auto;">
                        <pre style="margin:0;white-space:pre-wrap;word-break:break-all;font-size:13px;"><?= e(substr($previewData['body'], 0, 5000)) ?><?php if (strlen($previewData['body']) > 5000) echo "\n\n[... truncated ...]"; ?></pre>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div style="margin-top:30px;padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;">
                <h4><i class="fas fa-info-circle" style="color:#856404;"></i> Usage Tips</h4>
                <ul style="margin:10px 0 0;padding-left:20px;color:#856404;">
                    <li>This tool fetches the URL from our server for preview purposes</li>
                    <li>Supported protocols: HTTP and HTTPS</li>
                    <li>Maximum response size: 5KB preview (full content is fetched)</li>
                    <li>Use this to check sources before linking in articles</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
