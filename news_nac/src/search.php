<?php
/**
 * Nac News Portal - Search
 * VULNERABILITY: SQL Injection in search query (Flag 2)
 */
$pageTitle = 'Search';
require_once __DIR__ . '/includes/header.php';

$query = $_GET['q'] ?? '';
$results = [];
$total = 0;

if (!empty($query)) {
    $conn = getDbConnection();
    
    // VULNERABLE: Direct string concatenation in SQL query
    // This allows UNION-based SQL injection
    $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, 
            u.full_name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.status = 'published' 
            AND (a.title LIKE '%" . $query . "%' OR a.content LIKE '%" . $query . "%')
            ORDER BY a.published_at DESC 
            LIMIT 20";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $total = count($results);
    }
}

$categoryIcons = [
    'technology' => 'fas fa-microchip',
    'science' => 'fas fa-flask',
    'world' => 'fas fa-globe-americas',
    'business' => 'fas fa-chart-line',
    'culture' => 'fas fa-landmark',
    'opinion' => 'fas fa-comment-dots',
    'health' => 'fas fa-heartbeat',
];
?>

<div class="container">
    <div class="search-header">
        <h1><i class="fas fa-search"></i> Search Results</h1>
        <?php if (!empty($query)): ?>
            <p>Found <strong><?= $total ?></strong> results for "<strong><?= e($query) ?></strong>"</p>
        <?php else: ?>
            <p>Enter a search term to find articles.</p>
        <?php endif; ?>
    </div>

    <!-- Search Form -->
    <div style="max-width:600px;margin:0 auto 30px;">
        <form action="/search.php" method="GET" class="search-form" style="border:2px solid var(--border-color);border-radius:var(--radius-md);overflow:hidden;">
            <input type="text" name="q" placeholder="Search articles, topics, authors..." 
                   value="<?= e($query) ?>" style="flex:1;border:none;padding:14px 18px;font-size:1rem;outline:none;">
            <button type="submit" style="background:var(--primary);color:white;border:none;padding:14px 20px;cursor:pointer;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <!-- Results -->
    <?php if (!empty($results)): ?>
    <div class="article-list">
        <?php foreach ($results as $article): ?>
        <article class="article-card">
            <div class="article-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-width:200px;">
                <i class="<?= $categoryIcons[$article['category_slug'] ?? 'technology'] ?? 'fas fa-newspaper' ?>"></i>
            </div>
            <div class="article-card-body">
                <?php if (!empty($article['category_name'])): ?>
                <a href="/category.php?slug=<?= e($article['category_slug'] ?? '') ?>" class="category-badge"><?= e($article['category_name'] ?? 'News') ?></a>
                <?php endif; ?>
                <h3><a href="/article.php?slug=<?= e($article['slug']) ?>"><?= e($article['title']) ?></a></h3>
                <p class="excerpt"><?= e(truncate($article['excerpt'] ?? strip_tags($article['content'] ?? ''), 160)) ?></p>
                <div class="article-meta">
                    <span><i class="far fa-user"></i> <?= e($article['author_name'] ?? 'Staff') ?></span>
                    <span><i class="far fa-calendar"></i> <?= formatDate($article['published_at'] ?? $article['created_at'] ?? '') ?></span>
                    <span><i class="far fa-eye"></i> <?= number_format($article['views'] ?? 0) ?> views</span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php elseif (!empty($query)): ?>
    <div class="error-page">
        <h1 style="font-size:2rem;"><i class="fas fa-search" style="color:var(--text-light);"></i></h1>
        <p>No articles found matching your search. Try different keywords.</p>
        <a href="/" class="btn btn-primary">Back to Home</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
