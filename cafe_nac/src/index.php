<?php
/**
 * NAC Cafe - Homepage
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Beranda';

// Get latest articles
$stmt = $pdo->query("SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.published = 1 ORDER BY a.published_at DESC LIMIT 5");
$articles = $stmt->fetchAll();

// Get featured article (most views)
$stmt = $pdo->query("SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.published = 1 ORDER BY a.views DESC LIMIT 1");
$featured = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Selamat Datang di NAC Cafe</h1>
        <p>Portal berita dan informasi seputar kopi Nusantara. Temukan cerita, tips, dan update terbaru dari kami.</p>
        <a href="/news.php" class="btn-primary">Baca Berita Terbaru</a>
    </div>
</section>

<div class="container">
    <!-- Featured Article -->
    <?php if ($featured): ?>
    <div class="section-title">
        <h2>Artikel Pilihan</h2>
        <p>Berita dan cerita terpopuler dari NAC Cafe</p>
    </div>

    <div class="articles-grid">
        <a href="/article.php?id=<?= $featured['id'] ?>" class="featured-article">
            <div class="card-image">☕</div>
            <div class="card-body">
                <span class="card-category"><?= safe_output($featured['category']) ?></span>
                <h3><?= safe_output($featured['title']) ?></h3>
                <p><?= safe_output($featured['excerpt'] ?: make_excerpt($featured['content'])) ?></p>
                <div class="card-meta">
                    <span>Oleh <?= safe_output($featured['author_name']) ?></span>
                    <span><?= format_date($featured['published_at']) ?></span>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Latest Articles -->
    <div class="section-title" style="margin-top: 60px;">
        <h2>Berita Terbaru</h2>
        <p>Update terkini dari dunia kopi dan NAC Cafe</p>
    </div>

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

    <div style="text-align: center; margin-top: 32px;">
        <a href="/news.php" class="btn-secondary">Lihat Semua Berita →</a>
    </div>

    <!-- Quick Menu Preview -->
    <div class="section-title" style="margin-top: 60px;">
        <h2>Menu Favorit</h2>
        <p>Pilihan menu terlaris di NAC Cafe</p>
    </div>

    <?php
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY RAND() LIMIT 4");
    $menu_items = $stmt->fetchAll();
    ?>
    <div class="menu-grid">
        <?php foreach ($menu_items as $item): ?>
        <div class="menu-card">
            <div class="menu-card-image">
                <?php
                $menu_icons = ['Kopi' => '☕', 'Non-Kopi' => '🍵', 'Makanan' => '🥐', 'Spesial' => '⭐'];
                echo $menu_icons[$item['category']] ?? '🍽️';
                ?>
            </div>
            <div class="menu-card-body">
                <h3><?= safe_output($item['name']) ?></h3>
                <p><?= safe_output($item['description']) ?></p>
                <span class="menu-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 32px;">
        <a href="/menu.php" class="btn-secondary">Lihat Menu Lengkap →</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
