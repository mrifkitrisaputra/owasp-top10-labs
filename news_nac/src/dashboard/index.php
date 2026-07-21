<?php
/**
 * Nac News Portal - Editor Dashboard
 */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$user = getCurrentUser();
$conn = getDbConnection();

// Get stats
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM articles WHERE author_id = " . intval($user['id']));
$stats['my_articles'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
$stats['published'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status = 'draft'");
$stats['drafts'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM comments WHERE status = 'pending'");
$stats['pending_comments'] = $result->fetch_assoc()['total'];

// Recent articles
$recentArticles = $conn->query("SELECT a.*, c.name as category_name FROM articles a 
                                LEFT JOIN categories c ON a.category_id = c.id 
                                ORDER BY a.created_at DESC LIMIT 10");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
            <ul class="dashboard-nav">
                <li><a href="/dashboard/" class="active"><i class="fas fa-home"></i> Overview</a></li>
                <li><a href="/dashboard/articles.php"><i class="fas fa-newspaper"></i> Articles</a></li>
                <li><a href="/dashboard/create-article.php"><i class="fas fa-plus-circle"></i> New Article</a></li>
                <li><a href="/dashboard/upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Files</a></li>
                <li><a href="/dashboard/preview.php"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="dashboard-body">
            <h2>Welcome, <?= e($user['full_name']) ?></h2>
            <p style="color:var(--text-light);margin-bottom:24px;">Here's an overview of your editorial workspace.</p>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['published'] ?></div>
                    <div class="stat-label">Published Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['drafts'] ?></div>
                    <div class="stat-label">Drafts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['my_articles'] ?></div>
                    <div class="stat-label">My Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['pending_comments'] ?></div>
                    <div class="stat-label">Pending Comments</div>
                </div>
            </div>

            <!-- Recent Articles -->
            <h3 style="font-family:var(--font-serif);margin-bottom:16px;">Recent Articles</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($article = $recentArticles->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="/article.php?slug=<?= e($article['slug']) ?>"><?= e(truncate($article['title'], 50)) ?></a>
                        </td>
                        <td><?= e($article['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="status-badge status-<?= e($article['status']) ?>">
                                <?= ucfirst(e($article['status'])) ?>
                            </span>
                        </td>
                        <td><?= formatDate($article['created_at'], 'M j, Y') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
