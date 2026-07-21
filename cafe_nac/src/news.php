<?php
/**
 * NAC Cafe - News Listing
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Berita';

// Pagination
$per_page = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Category filter
$category = $_GET['category'] ?? '';

$where = "WHERE a.published = 1";
$params = [];
if (!empty($category)) {
    $where .= " AND a.category = ?";
    $params[] = $category;
}

// Count total
$count_sql = "SELECT COUNT(*) as total FROM articles a $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

// Get articles
$sql = "SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id $where ORDER BY a.published_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Get categories
$cats = $pdo->query("SELECT DISTINCT category FROM articles WHERE published = 1 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Berita & Artikel</h1>
        <p>Informasi terkini seputar kopi dan NAC Cafe</p>
    </div>
</div>

<div class="container">
    <!-- Category Filter -->
    <div style="text-align: center; margin-bottom: 32px;">
        <a href="/news.php" class="btn-small <?= empty($category) ? 'btn-primary' : 'btn-secondary' ?>" style="margin: 4px;">Semua</a>
        <?php foreach ($cats as $cat): ?>
        <a href="/news.php?category=<?= urlencode($cat) ?>" class="btn-small <?= $category === $cat ? 'btn-primary' : 'btn-secondary' ?>" style="margin: 4px;"><?= safe_output($cat) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($articles)): ?>
        <div class="alert alert-info">Tidak ada artikel dalam kategori ini.</div>
    <?php else: ?>
    <div class="articles-grid">
        <?php foreach ($articles as $article): ?>
        <a href="/article.php?id=<?= $article['id'] ?>" class="card">
            <div class="card-image">
                <?php
                $icons = ['Berita' => '📰', 'Edukasi' => '📚', 'Tips' => '💡', 'Menu' => '🍽️', 'Cerita' => '✍️'];
                echo $icons[$article['category']] ?? '☕';
                ?>
            </div>
            <div class="card-body">
                <span class="card-category"><?= safe_output($article['category']) ?></span>
                <h3><?= safe_output($article['title']) ?></h3>
                <p><?= safe_output($article['excerpt'] ?: make_excerpt($article['content'])) ?></p>
                <div class="card-meta">
                    <span><?= safe_output($article['author_name']) ?></span>
                    <span><?= format_date($article['published_at']) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>">← Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?><?= $category ? '&category=' . urlencode($category) : '' ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= $category ? '&category=' . urlencode($category) : '' ?>">Selanjutnya →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
