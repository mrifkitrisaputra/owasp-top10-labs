<?php
/**
 * Nac News Portal - Category Page
 */
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: /');
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->bind_param('s', $slug);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    $pageTitle = 'Category Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="error-page"><h1>404</h1><p>Category not found.</p><a href="/" class="btn btn-primary">Back to Home</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * POSTS_PER_PAGE;
$total = countArticles($category['id']);
$totalPages = ceil($total / POSTS_PER_PAGE);

$articles = getArticles([
    'category_id' => $category['id'],
    'limit' => POSTS_PER_PAGE,
    'offset' => $offset
]);

$pageTitle = $category['name'];
require_once __DIR__ . '/includes/header.php';

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
        <h1>
            <?php if ($category['icon']): ?><i class="<?= e($category['icon']) ?>"></i> <?php endif; ?>
            <?= e($category['name']) ?>
        </h1>
        <p><?= e($category['description']) ?></p>
        <p style="color:var(--text-light);font-size:0.85rem;margin-top:4px;"><?= $total ?> articles</p>
    </div>

    <?php if (!empty($articles)): ?>
    <div class="article-list">
        <?php foreach ($articles as $article): ?>
        <article class="article-card">
            <div class="article-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="<?= $categoryIcons[$slug] ?? 'fas fa-newspaper' ?>"></i>
            </div>
            <div class="article-card-body">
                <h3><a href="/article.php?slug=<?= e($article['slug']) ?>"><?= e($article['title']) ?></a></h3>
                <p class="excerpt"><?= e(truncate($article['excerpt'] ?? $article['content'], 160)) ?></p>
                <div class="article-meta">
                    <span><i class="far fa-user"></i> <?= e($article['author_name']) ?></span>
                    <span><i class="far fa-clock"></i> <?= timeAgo($article['published_at']) ?></span>
                    <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?></span>
                </div>
                <a href="/article.php?slug=<?= e($article['slug']) ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?slug=<?= e($slug) ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?slug=<?= e($slug) ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?slug=<?= e($slug) ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="error-page">
        <p>No articles in this category yet.</p>
        <a href="/" class="btn btn-primary">Back to Home</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
