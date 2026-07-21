<?php
/**
 * Nac News Portal - Import RSS Feed
 * 
 * VULNERABILITY: XML External Entity (XXE) Injection (Flag 7)
 * 
 * The RSS import uses PHP's simplexml_load_string with default settings,
 * which processes external entities. Players can craft a malicious XML
 * to read files from the server.
 * Flag: tkn_hypatia_scroll_88f2 (in /etc/nac/service_token)
 */
$pageTitle = 'Import RSS Feed';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$importResult = null;
$importError = null;
$rssContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $importError = 'Invalid session token.';
    } else {
        $inputMethod = $_POST['input_method'] ?? 'url';
        $rssXml = '';
        
        if ($inputMethod === 'url') {
            $rssUrl = trim($_POST['rss_url'] ?? '');
            if (empty($rssUrl)) {
                $importError = 'Please enter an RSS feed URL.';
            } else {
                $ch = curl_init($rssUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT => 'Nac-Bot/1.0 (RSS Reader)',
                ]);
                $rssXml = curl_exec($ch);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    $importError = 'Failed to fetch RSS feed: ' . $curlError;
                }
            }
        } elseif ($inputMethod === 'paste') {
            $rssXml = $_POST['rss_xml'] ?? '';
            $rssContent = $rssXml;
            if (empty($rssXml)) {
                $importError = 'Please paste your RSS/XML content.';
            }
        }
        
        if (!$importError && !empty($rssXml)) {
            // VULNERABILITY: XML parsing with external entities enabled
            // libxml_disable_entity_loader is deprecated in PHP 8+, and
            // LIBXML_NOENT flag actually SUBSTITUTES entities (processes them)
            libxml_use_internal_errors(true);
            
            $xml = simplexml_load_string($rssXml, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_DTDLOAD);
            
            if ($xml === false) {
                $xmlErrors = libxml_get_errors();
                $importError = 'Invalid XML format. ';
                foreach ($xmlErrors as $err) {
                    $importError .= trim($err->message) . ' ';
                }
                libxml_clear_errors();
            } else {
                $importResult = [];
                
                // Standard RSS 2.0
                if (isset($xml->channel)) {
                    $channel = $xml->channel;
                    $importResult['feed_title'] = (string)($channel->title ?? 'Unknown Feed');
                    $importResult['feed_description'] = (string)($channel->description ?? '');
                    $importResult['items'] = [];
                    
                    foreach ($channel->item as $item) {
                        $importResult['items'][] = [
                            'title' => (string)($item->title ?? 'Untitled'),
                            'link' => (string)($item->link ?? ''),
                            'description' => (string)($item->description ?? ''),
                            'pubDate' => (string)($item->pubDate ?? ''),
                        ];
                    }
                }
                // Atom feed
                elseif (isset($xml->entry)) {
                    $importResult['feed_title'] = (string)($xml->title ?? 'Unknown Feed');
                    $importResult['feed_description'] = (string)($xml->subtitle ?? '');
                    $importResult['items'] = [];
                    
                    foreach ($xml->entry as $entry) {
                        $importResult['items'][] = [
                            'title' => (string)($entry->title ?? 'Untitled'),
                            'link' => (string)($entry->link['href'] ?? ''),
                            'description' => (string)($entry->summary ?? $entry->content ?? ''),
                            'pubDate' => (string)($entry->updated ?? $entry->published ?? ''),
                        ];
                    }
                }
                // Fallback - just display what we got
                else {
                    $importResult['feed_title'] = (string)($xml->getName() ?? 'Unknown');
                    $importResult['feed_description'] = 'Unrecognized feed format. Raw content displayed below.';
                    $importResult['items'] = [];
                    $importResult['raw'] = $xml->asXML();
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
                <li><a href="/dashboard/preview.php"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php" class="active"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <h2><i class="fas fa-rss"></i> Import RSS Feed</h2>
            <p class="text-muted">Import articles from external RSS/Atom feeds. You can preview feed items before importing them as articles.</p>

            <?php if ($importError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= e($importError) ?>
            </div>
            <?php endif; ?>

            <!-- Import Form -->
            <div class="import-form" style="background:#f8f9fa;border-radius:8px;padding:25px;margin-bottom:30px;">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    
                    <!-- Input Method Tabs -->
                    <div style="display:flex;gap:10px;margin-bottom:20px;">
                        <button type="button" class="btn btn-sm tab-btn active" onclick="switchTab('url')" id="tab-url">
                            <i class="fas fa-link"></i> From URL
                        </button>
                        <button type="button" class="btn btn-sm btn-outline tab-btn" onclick="switchTab('paste')" id="tab-paste">
                            <i class="fas fa-paste"></i> Paste XML
                        </button>
                    </div>

                    <!-- URL Input -->
                    <div id="input-url">
                        <input type="hidden" name="input_method" value="url" id="inputMethod">
                        <div class="form-group">
                            <label for="rss_url">RSS Feed URL</label>
                            <input type="text" id="rss_url" name="rss_url" class="form-control" 
                                   placeholder="https://example.com/feed.xml">
                        </div>
                    </div>

                    <!-- Paste Input -->
                    <div id="input-paste" style="display:none;">
                        <div class="form-group">
                            <label for="rss_xml">RSS/XML Content</label>
                            <textarea id="rss_xml" name="rss_xml" class="form-control" rows="12" 
                                      placeholder="<?= e('<?xml version="1.0" encoding="UTF-8"?>') ?>&#10;Paste your RSS or Atom XML content here..."><?= e($rssContent) ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Parse Feed
                    </button>
                </form>
            </div>

            <?php if ($importResult): ?>
            <!-- Feed Results -->
            <div class="feed-result" style="border:1px solid #dee2e6;border-radius:8px;overflow:hidden;">
                <div style="background:#1a1a2e;color:#fff;padding:20px;">
                    <h3 style="margin:0;"><?= e($importResult['feed_title']) ?></h3>
                    <?php if (!empty($importResult['feed_description'])): ?>
                    <p style="margin:8px 0 0;opacity:0.8;"><?= e($importResult['feed_description']) ?></p>
                    <?php endif; ?>
                    <span style="font-size:12px;opacity:0.6;">
                        <?= count($importResult['items']) ?> item(s) found
                    </span>
                </div>

                <?php if (!empty($importResult['items'])): ?>
                <div style="padding:20px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                                <th>Title</th>
                                <th>Published</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($importResult['items'] as $i => $item): ?>
                            <tr>
                                <td><input type="checkbox" class="item-check" value="<?= $i ?>"></td>
                                <td>
                                    <?php if (!empty($item['link'])): ?>
                                    <a href="<?= e($item['link']) ?>" target="_blank"><?= e(truncate($item['title'], 50)) ?></a>
                                    <?php else: ?>
                                    <?= e(truncate($item['title'], 50)) ?>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space:nowrap;"><?= e($item['pubDate'] ? date('M j, Y', strtotime($item['pubDate'])) : '-') ?></td>
                                <td><?= e(truncate(strip_tags($item['description']), 80)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top:15px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Import feature coming soon! Items previewed successfully.')">
                            <i class="fas fa-file-import"></i> Import Selected
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($importResult['raw'])): ?>
                <div style="padding:20px;">
                    <h4>Raw Content:</h4>
                    <pre style="background:#f8f9fa;padding:15px;border-radius:4px;overflow:auto;max-height:300px;font-size:13px;"><?= e($importResult['raw']) ?></pre>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div style="margin-top:30px;padding:20px;background:#f0f0f0;border-radius:8px;">
                <h4><i class="fas fa-question-circle"></i> About RSS Import</h4>
                <p style="font-size:14px;color:#666;">
                    Our RSS importer supports standard RSS 2.0 and Atom feed formats. 
                    The XML parser processes all entities and DTD declarations for maximum compatibility 
                    with various feed sources. You can either provide a URL to fetch or paste XML content directly.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.add('btn-outline'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('tab-' + tab).classList.remove('btn-outline');
    
    document.getElementById('input-url').style.display = tab === 'url' ? '' : 'none';
    document.getElementById('input-paste').style.display = tab === 'paste' ? '' : 'none';
    document.getElementById('inputMethod').value = tab;
}

document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.item-check').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
