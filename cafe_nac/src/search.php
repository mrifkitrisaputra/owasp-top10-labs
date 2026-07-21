<?php
/**
 * NAC Cafe - Search Page
 * VULNERABLE: SQL Injection via search query (Flag 2)
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Pencarian';
$query = $_GET['q'] ?? '';
$results = [];

if (!empty($query)) {
    // VULNERABILITY: Direct string concatenation in SQL query (Flag 2 - SQL Injection)
    // Using mysqli intentionally for the vulnerable query
    $sql = "SELECT a.*, u.full_name as author_name 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.published = 1 
            AND (a.title LIKE '%{$query}%' OR a.content LIKE '%{$query}%')
            ORDER BY a.published_at DESC";

    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }

    log_activity("Search query: " . $query);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Pencarian</h1>
        <p>Cari artikel, berita, dan informasi di NAC Cafe</p>
    </div>
</div>

<div class="container">
    <div class="search-container">
        <form method="GET" action="/search.php">
            <div class="search-box">
                <input type="text" name="q" placeholder="Cari artikel, berita, tips..." 
                       value="<?= safe_output($query) ?>" autofocus>
                <button type="submit" class="btn-primary">Cari</button>
            </div>
        </form>

        <?php if (!empty($query)): ?>
        <div class="search-results">
            <h3>Hasil pencarian untuk "<?= safe_output($query) ?>" (<?= count($results) ?> ditemukan)</h3>

            <?php if (empty($results)): ?>
                <div class="alert alert-info">Tidak ada hasil yang ditemukan untuk pencarian Anda.</div>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                <div class="search-result-item">
                    <h3><a href="/article.php?id=<?= $row['id'] ?>"><?= safe_output($row['title']) ?></a></h3>
                    <p><?= safe_output(make_excerpt(strip_tags($row['content']), 200)) ?></p>
                    <div class="card-meta">
                        <span><?= safe_output($row['author_name'] ?? 'Admin') ?></span>
                        <span><?= format_date($row['published_at']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
