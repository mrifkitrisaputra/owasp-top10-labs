<?php
/**
 * NAC Cafe - Article Detail + Comments
 * Comments are NOT sanitized (intentional - Flag 5: Stored XSS)
 */
require_once __DIR__ . '/includes/functions.php';

$article_id = (int)($_GET['id'] ?? 0);
if (!$article_id) {
    header('Location: /news.php');
    exit;
}

// Get article
$stmt = $pdo->prepare("SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = ? AND a.published = 1");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    $page_title = 'Artikel Tidak Ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="alert alert-error">Artikel tidak ditemukan.</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment views
$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article_id]);

// Handle comment submission
$comment_error = '';
$comment_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    if (!is_logged_in()) {
        $comment_error = 'Anda harus login untuk berkomentar.';
    } elseif (empty(trim($_POST['content']))) {
        $comment_error = 'Komentar tidak boleh kosong.';
    } else {
        // INTENTIONALLY NOT SANITIZING comment content (Flag 5 - Stored XSS)
        $content = $_POST['content'];
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];

        $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, username, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$article_id, $user_id, $username, $content]);

        log_activity("Comment posted on article {$article_id} by {$username}");
        $comment_success = 'Komentar berhasil ditambahkan!';
    }
}

// Get comments
$stmt = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? ORDER BY created_at DESC");
$stmt->execute([$article_id]);
$comments = $stmt->fetchAll();

$page_title = $article['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Beranda</a>
        <span class="separator">/</span>
        <a href="/news.php">Berita</a>
        <span class="separator">/</span>
        <span><?= safe_output($article['title']) ?></span>
    </div>

    <div class="article-detail">
        <div class="article-header">
            <span class="card-category"><?= safe_output($article['category']) ?></span>
            <h1><?= safe_output($article['title']) ?></h1>
            <div class="article-meta">
                <span>✍️ <?= safe_output($article['author_name']) ?></span>
                <span>📅 <?= format_date($article['published_at']) ?></span>
                <span>👁️ <?= number_format($article['views']) ?> views</span>
            </div>
        </div>

        <div class="article-content">
            <?= $article['content'] ?>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <h3>Komentar (<?= count($comments) ?>)</h3>

        <?php if ($comment_error): ?>
            <div class="alert alert-error"><?= safe_output($comment_error) ?></div>
        <?php endif; ?>
        <?php if ($comment_success): ?>
            <div class="alert alert-success"><?= safe_output($comment_success) ?></div>
        <?php endif; ?>

        <!-- Comment Form -->
        <?php if (is_logged_in()): ?>
        <div class="comment-form">
            <h4>Tulis Komentar</h4>
            <form method="POST" id="commentForm">
                <div class="form-group">
                    <textarea name="content" placeholder="Tulis komentar Anda..." rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Kirim Komentar</button>
            </form>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <a href="/login.php">Login</a> untuk menulis komentar.
        </div>
        <?php endif; ?>

        <!-- Comments List -->
        <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <div class="comment-header">
                <span class="comment-author"><?= safe_output($comment['username']) ?></span>
                <span class="comment-date"><?= format_date($comment['created_at']) ?></span>
            </div>
            <div class="comment-content">
                <?php
                // VULNERABILITY: Comment content is output WITHOUT sanitization
                // This enables Stored XSS (Flag 5)
                echo $comment['content'];
                ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($comments)): ?>
            <p style="text-align: center; color: var(--text-light); padding: 24px;">Belum ada komentar. Jadilah yang pertama!</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
