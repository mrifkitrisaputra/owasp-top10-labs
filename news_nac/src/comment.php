<?php
/**
 * Nac News Portal - Comment Handler (AJAX)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to comment']);
    exit;
}

$articleId = intval($_POST['article_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$user = getCurrentUser();

if (empty($articleId) || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Article ID and comment content are required']);
    exit;
}

if (strlen($content) > 2000) {
    echo json_encode(['success' => false, 'error' => 'Comment is too long (max 2000 characters)']);
    exit;
}

$conn = getDbConnection();

// Verify article exists
$stmt = $conn->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
$stmt->bind_param('i', $articleId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Article not found']);
    exit;
}

$status = 'pending';
$stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iiss', $articleId, $user['id'], $content, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment submitted for moderation']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to submit comment']);
}
