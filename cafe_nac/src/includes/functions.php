<?php
/**
 * NAC Cafe - Helper Functions
 */

session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Theme Manager class - handles user theme preferences
 * Used for cookie-based theme loading
 */
class ThemeManager {
    public $theme_file = '/var/www/html/assets/themes/default.css';
    public $theme_content = '';

    public function __construct($file = null) {
        if ($file) {
            $this->theme_file = $file;
        }
    }

    public function __wakeup() {
        if (file_exists($this->theme_file)) {
            $this->theme_content = file_get_contents($this->theme_file);
        }
    }

    public function __toString() {
        return $this->theme_content;
    }

    public function getTheme() {
        return $this->theme_content;
    }
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data from cookie (intentionally vulnerable - Flag 4)
 * Uses base64 encoded JSON in cookie instead of secure session
 */
function get_user_role() {
    if (isset($_COOKIE['nac_session'])) {
        $data = json_decode(base64_decode($_COOKIE['nac_session']), true);
        if ($data && isset($data['role'])) {
            return $data['role'];
        }
    }
    return 'guest';
}

/**
 * Check if current user is admin (based on cookie - vulnerable!)
 */
function is_admin() {
    return get_user_role() === 'admin';
}

/**
 * Get user info from cookie
 */
function get_cookie_user() {
    if (isset($_COOKIE['nac_session'])) {
        $data = json_decode(base64_decode($_COOKIE['nac_session']), true);
        return $data;
    }
    return null;
}

/**
 * Set session cookie after login
 */
function set_session_cookie($user) {
    $session_data = json_encode([
        'user_id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name']
    ]);
    setcookie('nac_session', base64_encode($session_data), time() + 86400, '/');
}

/**
 * Process user preferences cookie (intentionally uses unserialize - Flag 8)
 */
function get_user_preferences() {
    $theme_output = '';
    if (isset($_COOKIE['user_prefs'])) {
        $prefs = @unserialize(base64_decode($_COOKIE['user_prefs']));
        if ($prefs instanceof ThemeManager) {
            $theme_output = (string)$prefs;
        }
    }
    return $theme_output;
}

/**
 * Sanitize output (used in most places, but NOT in comments - Flag 5)
 */
function safe_output($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Get site settings
 */
function get_setting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : '';
}

/**
 * Format date in Indonesian
 */
function format_date($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $ts = strtotime($date);
    $day = date('d', $ts);
    $month = $months[(int)date('m', $ts)];
    $year = date('Y', $ts);
    return "$day $month $year";
}

/**
 * Generate excerpt
 */
function make_excerpt($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get article count
 */
function get_article_count() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM articles WHERE published = 1");
    return $stmt->fetch()['count'];
}

/**
 * Log activity
 */
function log_activity($message) {
    $log_file = '/var/www/html/logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    @file_put_contents($log_file, "[{$timestamp}] [{$ip}] {$message}\n", FILE_APPEND);
}
