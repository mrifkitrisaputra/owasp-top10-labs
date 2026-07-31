<?php
/**
 * Nac News Portal - Create/Edit Article
 */
$pageTitle = 'Create Article';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('editor');

$conn = getDbConnection();
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid session token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';
        $featured_image = trim($_POST['featured_image'] ?? '');
        $tags = trim($_POST['tags'] ?? '');

        if (empty($title)) $errors[] = 'Title is required';
        if (empty($content)) $errors[] = 'Content is required';
        if ($category_id < 1) $errors[] = 'Please select a category';

        if (empty($errors)) {
            if (empty($excerpt)) {
                $excerpt = substr(strip_tags($content), 0, 200) . '...';
            }
            $slug = $slug . '-' . time();
            $author_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("INSERT INTO articles (title, slug, content, excerpt, category_id, author_id, featured_image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('ssssiiss', $title, $slug, $content, $excerpt, $category_id, $author_id, $featured_image, $status);
            
            if ($stmt->execute()) {
                $article_id = $conn->insert_id;

                // Handle tags
                if (!empty($tags)) {
                    $tagList = array_map('trim', explode(',', $tags));
                    foreach ($tagList as $tagName) {
                        if (empty($tagName)) continue;
                        $tagSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $tagName));
                        $conn->query("INSERT IGNORE INTO tags (name, slug) VALUES ('" . $conn->real_escape_string($tagName) . "', '" . $conn->real_escape_string($tagSlug) . "')");
                        $tagRow = $conn->query("SELECT id FROM tags WHERE slug='" . $conn->real_escape_string($tagSlug) . "'")->fetch_assoc();
                        if ($tagRow) {
                            $conn->query("INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES ($article_id, {$tagRow['id']})");
                        }
                    }
                }

                $success = true;
            } else {
                $errors[] = 'Failed to save article. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-tachometer-alt"></i> Dashboard</h3>
            <ul class="dashboard-nav">
                <li><a href="/dashboard/"><i class="fas fa-home"></i> Overview</a></li>
                <li><a href="/dashboard/articles.php"><i class="fas fa-newspaper"></i> Articles</a></li>
                <li><a href="/dashboard/create-article.php" class="active"><i class="fas fa-plus-circle"></i> New Article</a></li>
                <li><a href="/dashboard/upload.php"><i class="fas fa-cloud-upload-alt"></i> Upload Files</a></li>
                <li><a href="/dashboard/preview.php"><i class="fas fa-external-link-alt"></i> URL Preview</a></li>
                <li><a href="/dashboard/import-rss.php"><i class="fas fa-rss"></i> Import RSS</a></li>
                <li><a href="/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <h2>Create Article</h2>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Article saved successfully! 
                <a href="/dashboard/articles.php">View all articles</a>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0;padding-left:20px;">
                    <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" class="article-form">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter article title" value="<?= e($_POST['title'] ?? '') ?>" required>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select category...</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt</label>
                    <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="Brief summary (auto-generated if empty)"><?= e($_POST['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" class="form-control" rows="15" placeholder="Write your article content (HTML supported)" required><?= e($_POST['content'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="featured_image">Featured Image URL</label>
                    <input type="text" id="featured_image" name="featured_image" class="form-control" placeholder="https://example.com/image.jpg" value="<?= e($_POST['featured_image'] ?? '') ?>">
                    <small class="form-help">Or <a href="/dashboard/upload.php">upload an image</a> first</small>
                </div>

                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-control" placeholder="news, technology, security (comma-separated)" value="<?= e($_POST['tags'] ?? '') ?>">
                </div>

                <div class="form-actions" style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Article</button>
                    <a href="/dashboard/articles.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
