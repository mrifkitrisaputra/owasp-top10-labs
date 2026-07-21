<?php
/**
 * Nac News Portal - Homepage
 */
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$featured = getFeaturedArticles(3);
$latest = getArticles(['limit' => 8]);
$trending = getTrendingArticles(5);
$categories = getCategories();

$categoryIcons = [
    'technology' => 'fas fa-microchip',
    'science' => 'fas fa-flask',
    'world' => 'fas fa-globe-americas',
    'business' => 'fas fa-chart-line',
    'culture' => 'fas fa-landmark',
    'opinion' => 'fas fa-comment-dots',
    'health' => 'fas fa-heartbeat',
];

$categoryColors = [
    'technology' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'science' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'world' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'business' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'culture' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'opinion' => 'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
    'health' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
];
?>

<div class="container">
    <!-- Hero Section -->
    <?php if (!empty($featured)): ?>
    <section class="hero-section">
        <div class="hero-grid">
            <!-- Main Featured Article -->
            <div class="hero-main">
                <div class="hero-image" style="background: <?= $categoryColors[$featured[0]['category_slug'] ?? 'technology'] ?>">
                    <span class="hero-category"><?= e($featured[0]['category_name']) ?></span>
                    <i class="<?= $categoryIcons[$featured[0]['category_slug'] ?? 'technology'] ?? 'fas fa-newspaper' ?>"></i>
                </div>
                <div class="hero-body">
                    <h2><a href="/article.php?slug=<?= e($featured[0]['slug']) ?>"><?= e($featured[0]['title']) ?></a></h2>
                    <p class="excerpt"><?= e($featured[0]['excerpt']) ?></p>
                    <div class="article-meta">
                        <span><i class="far fa-user"></i> <?= e($featured[0]['author_name']) ?></span>
                        <span><i class="far fa-calendar"></i> <?= formatDate($featured[0]['published_at']) ?></span>
                        <span><i class="far fa-eye"></i> <?= number_format($featured[0]['views']) ?> views</span>
                    </div>
                </div>
            </div>
            
            <!-- Side Featured -->
            <div class="hero-sidebar">
                <?php for ($i = 1; $i < min(3, count($featured)); $i++): ?>
                <div class="hero-side-card">
                    <div class="side-card-image" style="background: <?= $categoryColors[$featured[$i]['category_slug'] ?? 'science'] ?>">
                        <i class="<?= $categoryIcons[$featured[$i]['category_slug'] ?? 'science'] ?? 'fas fa-newspaper' ?>"></i>
                    </div>
                    <div class="side-card-body">
                        <a href="/category.php?slug=<?= e($featured[$i]['category_slug']) ?>" class="category-badge"><?= e($featured[$i]['category_name']) ?></a>
                        <h3><a href="/article.php?slug=<?= e($featured[$i]['slug']) ?>"><?= e($featured[$i]['title']) ?></a></h3>
                        <div class="article-meta">
                            <span><i class="far fa-clock"></i> <?= timeAgo($featured[$i]['published_at']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Articles -->
        <div class="articles-section">
            <h2 class="section-title"><i class="far fa-newspaper"></i> Latest News</h2>
            <div class="article-list">
                <?php foreach ($latest as $article): ?>
                <article class="article-card">
                    <div class="article-card-image" style="background: <?= $categoryColors[$article['category_slug'] ?? 'technology'] ?>">
                        <i class="<?= $categoryIcons[$article['category_slug'] ?? 'technology'] ?? 'fas fa-newspaper' ?>"></i>
                    </div>
                    <div class="article-card-body">
                        <a href="/category.php?slug=<?= e($article['category_slug']) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
                        <h3><a href="/article.php?slug=<?= e($article['slug']) ?>"><?= e($article['title']) ?></a></h3>
                        <p class="excerpt"><?= e(truncate($article['excerpt'] ?? $article['content'], 150)) ?></p>
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
        </div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Trending -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-fire"></i> Trending</h3>
                <ol class="trending-list">
                    <?php foreach ($trending as $idx => $item): ?>
                    <li>
                        <span class="trending-number"><?= $idx + 1 ?></span>
                        <div class="trending-content">
                            <h4><a href="/article.php?slug=<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h4>
                            <span class="meta"><?= number_format($item['views']) ?> views &bull; <?= timeAgo($item['published_at']) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <!-- Categories -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-th-large"></i> Categories</h3>
                <ul class="category-list">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="/category.php?slug=<?= e($cat['slug']) ?>">
                            <span><?php if ($cat['icon']): ?><i class="<?= e($cat['icon']) ?>"></i> <?php endif; ?><?= e($cat['name']) ?></span>
                            <span class="category-count"><?= $cat['article_count'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- About Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-info-circle"></i> About Us</h3>
                <p style="font-size:0.9rem;color:var(--text-secondary);line-height:1.7;">Nac News is a premier digital news platform covering technology, science, business, and world affairs. Founded in 2019, we are committed to illuminating truth through rigorous journalism.</p>
                <a href="/page.php?page=about.html" class="btn btn-outline btn-sm btn-block" style="margin-top:12px">Learn More</a>
            </div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
