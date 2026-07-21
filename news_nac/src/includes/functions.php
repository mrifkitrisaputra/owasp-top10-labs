<?php
/**
 * Nac News Portal - Helper Functions
 */

require_once __DIR__ . '/../config.php';

/**
 * Sanitize output for display
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL-friendly slug
 */
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Format relative time
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

/**
 * Truncate text
 */
function truncate($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Format file size to human readable
 */
function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}

/**
 * Get articles with optional filters
 */
function getArticles($options = []) {
    $conn = getDbConnection();
    
    $where = ["a.status = 'published'"];
    $params = [];
    $types = '';
    
    if (!empty($options['category_id'])) {
        $where[] = "a.category_id = ?";
        $params[] = $options['category_id'];
        $types .= 'i';
    }
    
    if (!empty($options['author_id'])) {
        $where[] = "a.author_id = ?";
        $params[] = $options['author_id'];
        $types .= 'i';
    }
    
    if (!empty($options['featured'])) {
        $where[] = "a.is_featured = 1";
    }
    
    $whereClause = implode(' AND ', $where);
    $orderBy = $options['order'] ?? 'a.published_at DESC';
    $limit = $options['limit'] ?? POSTS_PER_PAGE;
    $offset = $options['offset'] ?? 0;
    
    $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, 
            u.full_name as author_name, u.username as author_username
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE $whereClause 
            ORDER BY $orderBy 
            LIMIT $limit OFFSET $offset";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Get single article by slug
 */
function getArticleBySlug($slug) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug, 
                            u.full_name as author_name, u.username as author_username, u.bio as author_bio
                            FROM articles a 
                            LEFT JOIN categories c ON a.category_id = c.id 
                            LEFT JOIN users u ON a.author_id = u.id 
                            WHERE a.slug = ? AND a.status = 'published'");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    
    if ($article) {
        // Increment view count
        $conn->query("UPDATE articles SET views = views + 1 WHERE id = " . (int)$article['id']);
    }
    
    return $article;
}

/**
 * Get categories with article count
 */
function getCategories() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT c.*, COUNT(a.id) as article_count 
                           FROM categories c 
                           LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
                           GROUP BY c.id 
                           ORDER BY c.name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Get comments for an article
 */
function getComments($articleId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT cm.*, u.full_name, u.username, u.avatar 
                           FROM comments cm 
                           LEFT JOIN users u ON cm.user_id = u.id 
                           WHERE cm.article_id = ? AND cm.status = 'approved' 
                           ORDER BY cm.created_at ASC");
    $stmt->bind_param('i', $articleId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get trending articles (most viewed)
 */
function getTrendingArticles($limit = 5) {
    return getArticles(['order' => 'a.views DESC', 'limit' => $limit]);
}

/**
 * Get featured articles
 */
function getFeaturedArticles($limit = 3) {
    return getArticles(['featured' => true, 'limit' => $limit]);
}

/**
 * Count total articles with optional filter
 */
function countArticles($categoryId = null) {
    $conn = getDbConnection();
    $sql = "SELECT COUNT(*) as total FROM articles WHERE status = 'published'";
    if ($categoryId) {
        $stmt = $conn->prepare($sql . " AND category_id = ?");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

/**
 * Encrypt data using internal method
 */
function encryptData($plaintext) {
    $key = ENCRYPTION_KEY;
    $result = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($plaintext); $i++) {
        $result .= chr(ord($plaintext[$i]) ^ ord($key[$i % $keyLen]));
    }
    return base64_encode($result);
}

/**
 * Decrypt data using internal method
 */
function decryptData($encrypted) {
    $key = ENCRYPTION_KEY;
    $data = base64_decode($encrypted);
    $result = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($data); $i++) {
        $result .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
    }
    return $result;
}

/**
 * Get user by ID
 */
function getUserById($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, role, bio, avatar, recovery_email, phone, created_at FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Generate CSRF token
 */
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get site setting
 */
function getSetting($key, $default = '') {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}