<?php
/**
 * Nac News Portal - Static Page Viewer
 * VULNERABILITY: Local File Inclusion (LFI) - No path sanitization (Flag 9)
 */
require_once __DIR__ . '/includes/functions.php';

// VULNERABILITY: User input directly used in file path without sanitization
// Allows directory traversal: page.php?page=../../../../etc/passwd
$page = $_GET['page'] ?? 'about.html';

// Attempt to read the file from pages/ directory
$filePath = __DIR__ . '/pages/' . $page;
$content = @file_get_contents($filePath);

if ($content === false) {
    $pageTitle = 'Page Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="error-page"><h1>404</h1><p>The page you requested could not be found.</p><a href="/" class="btn btn-primary">Back to Home</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = ucwords(str_replace(['.html', '-', '_'], ['', ' ', ' '], basename($page)));
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="static-page">
        <?= $content ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
