<?php
/**
 * Nac News Portal - Configuration
 * Environment: Production
 */

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'nac');
define('DB_PASS', getenv('DB_PASS') ?: 'alex_db_2024_s3cure');
define('DB_NAME', getenv('DB_NAME') ?: 'nac_news');

// Site Configuration
define('SITE_NAME', 'Nac News');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('SITE_DESCRIPTION', 'Your trusted source for technology, science, and world news');

// Session & Cookie Configuration
define('COOKIE_SECRET', 'aLx_s3cr3t_k3y_2024_nEwsP0rtal');
define('SESSION_LIFETIME', 3600);
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Encryption Configuration
define('ENCRYPTION_KEY', 'PTOLEMY');
define('ENCRYPTION_METHOD', 'internal');

// Pagination
define('POSTS_PER_PAGE', 10);

// Debug mode (disabled in production)
define('DEBUG_MODE', false);

// Database connection
function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            if (DEBUG_MODE) {
                die('Database connection failed: ' . $conn->connect_error);
            }
            die('Service temporarily unavailable. Please try again later.');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
