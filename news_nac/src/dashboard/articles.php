<?php
/**
 * Nac News Portal - Articles Management
 */
$pageTitle = 'Manage Articles';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$conn = getDbConnection();
$status = $_GET['status'] ?? 'all';

$sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.author_id = u.id";

if ($status !== 'all') {
    $stmt = $conn->prepare($sql . " WHERE a.status = ? ORDER BY a.created_at DESC");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $articles = $stmt->get_result();
} else {
    $articles = $conn->query($sql . " ORDER BY a.created_at DESC");
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
            <ul class="dashboard-nav">
                <li><a href="/dashboard/"><i class="fas fa-home"></i> Overview</a></li>
                <li><a href="/dashboard/articles.php" class="active"><i class="fas fa-newspaper"></i> Articles</a></li>
                <li><a href="/dashboard/create-article.php"><i class="fas fa-plus-circle"></i> New Article</a></li>
                <li><a href="/dashboard/upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Files</a></li>
                <li><a href="/dashboard/preview.php"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2>Articles</h2>
                <a href="/dashboard/create-article.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Article
                </a>
            </div>

            <!-- Filter Tabs -->
            <div style="margin-bottom:20px;display:flex;gap:8px;">
                <a href="?status=all" class="btn <?= $status === 'all' ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
                <a href="?status=published" class="btn <?= $status === 'published' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Published</a>
                <a href="?status=draft" class="btn <?= $status === 'draft' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Drafts</a>
                <a href="?status=archived" class="btn <?= $status === 'archived' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Archived</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($article = $articles->fetch_assoc()): ?>
                    <tr>
                        <td><?= $article['id'] ?></td>
                        <td>
                            <?php if ($article['status'] === 'published'): ?>
                                <a href="/article.php?slug=<?= e($article['slug']) ?>"><?= e(truncate($article['title'], 45)) ?></a>
                            <?php else: ?>
                                <?= e(truncate($article['title'], 45)) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e($article['author_name'] ?? '-') ?></td>
                        <td><?= e($article['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="status-badge status-<?= e($article['status']) ?>">
                                <?= ucfirst(e($article['status'])) ?>
                            </span>
                        </td>
                        <td><?= number_format($article['views']) ?></td>
                        <td><?= formatDate($article['created_at'], 'M j, Y') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
