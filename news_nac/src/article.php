<?php
/**
 * Nac News Portal - Single Article
 */
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: /');
    exit;
}

$article = getArticleBySlug($slug);
if (!$article) {
    $pageTitle = 'Article Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="error-page"><h1>404</h1><p>The article you\'re looking for doesn\'t exist.</p><a href="/" class="btn btn-primary">Back to Home</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $article['title'];
$comments = getComments($article['id']);

// Get related articles
$related = getArticles(['category_id' => $article['category_id'], 'limit' => 3]);
$related = array_filter($related, function($a) use ($article) { return $a['id'] != $article['id']; });

// Get article tags
$conn = getDbConnection();
$tagStmt = $conn->prepare("SELECT t.name, t.slug FROM tags t 
                           JOIN article_tags at ON t.id = at.tag_id 
                           WHERE at.article_id = ?");
$tagStmt->bind_param('i', $article['id']);
$tagStmt->execute();
$tags = $tagStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categoryIcons = [
    'technology' => 'fas fa-microchip',
    'science' => 'fas fa-flask',
    'world' => 'fas fa-globe-americas',
    'business' => 'fas fa-chart-line',
    'culture' => 'fas fa-landmark',
    'opinion' => 'fas fa-comment-dots',
    'health' => 'fas fa-heartbeat',
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <article class="article-single" data-article-id="<?= $article['id'] ?>">
        <!-- Article Header -->
        <div class="article-header">
            <a href="/category.php?slug=<?= e($article['category_slug']) ?>" class="category-badge"><?= e($article['category_name']) ?></a>
            <h1><?= e($article['title']) ?></h1>
            <div class="article-meta">
                <span><i class="far fa-user"></i> <?= e($article['author_name']) ?></span>
                <span><i class="far fa-calendar"></i> <?= formatDate($article['published_at']) ?></span>
                <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?> views</span>
                <span><i class="far fa-comment"></i> <?= count($comments) ?> comments</span>
            </div>
        </div>

        <!-- Feature Image -->
        <div class="article-feature-image" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
            <i class="<?= $categoryIcons[$article['category_slug']] ?? 'fas fa-newspaper' ?>"></i>
        </div>

        <!-- Article Content -->
        <div class="article-content">
            <?= $article['content'] ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div class="article-tags">
            <?php foreach ($tags as $tag): ?>
            <a href="/search.php?q=<?= e($tag['name']) ?>" class="tag">#<?= e($tag['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Author Box -->
        <div class="author-box">
            <div class="author-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="author-info">
                <h4><?= e($article['author_name']) ?></h4>
                <p><?= e($article['author_bio'] ?? 'Staff writer at Nac News.') ?></p>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h3><i class="far fa-comments"></i> Comments (<?= count($comments) ?>)</h3>
            
            <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <div class="comment-header">
                    <span class="comment-author">
                        <i class="far fa-user-circle"></i> 
                        <?= e($comment['full_name'] ?? $comment['author_name'] ?? 'Anonymous') ?>
                    </span>
                    <span class="comment-date"><?= timeAgo($comment['created_at']) ?></span>
                </div>
                <div class="comment-body">
                    <?= e($comment['content']) ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Comment Form -->
            <?php if (isLoggedIn()): ?>
            <form class="comment-form" id="commentForm">
                <h4 style="margin-bottom:12px;">Leave a Comment</h4>
                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                <div class="form-group">
                    <textarea name="content" placeholder="Write your comment..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-paper-plane"></i> Post Comment
                </button>
            </form>
            <?php else: ?>
            <p style="margin-top:20px;color:var(--text-light);font-size:0.9rem;">
                <a href="/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Sign in</a> to leave a comment.
            </p>
            <?php endif; ?>
        </div>
    </article>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
