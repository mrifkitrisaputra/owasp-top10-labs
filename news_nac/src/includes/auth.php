<?php
/**
 * Nac News Portal - Authentication Functions
 */

require_once __DIR__ . '/../config.php';

/**
 * Authenticate user with username and password
 */
function authenticateUser($username, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, password, full_name, role, is_active FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!$row['is_active']) {
            return ['success' => false, 'error' => 'Account is deactivated.'];
        }
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['logged_in'] = true;
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param('i', $row['id']);
            $updateStmt->execute();
            
            return ['success' => true, 'user' => $row];
        }
    }
    return ['success' => false, 'error' => 'Invalid username or password.'];
}

/**
 * Generate remember me token
 * VULNERABILITY: Role is stored in cookie and trusted on verification
 */
function generateRememberToken($userId, $role) {
    $data = json_encode([
        'uid' => $userId,
        'role' => $role,
        'exp' => time() + REMEMBER_ME_LIFETIME
    ]);
    $signature = md5(COOKIE_SECRET . $data);
    return base64_encode($data . '|' . $signature);
}

/**
 * Verify remember me token
 * VULNERABILITY: Trusts the role from the cookie data without database verification
 */
function verifyRememberToken($token) {
    $decoded = base64_decode($token);
    if (!$decoded) return false;
    
    $parts = explode('|', $decoded, 2);
    if (count($parts) !== 2) return false;
    
    $data = $parts[0];
    $signature = $parts[1];
    
    // Verify signature
    if (md5(COOKIE_SECRET . $data) !== $signature) return false;
    
    $payload = json_decode($data, true);
    if (!$payload || !isset($payload['uid']) || !isset($payload['role'])) return false;
    
    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) return false;
    
    // VULNERABILITY: Uses role from cookie instead of querying database
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, full_name FROM users WHERE id = ? AND is_active = 1");
    $stmt->bind_param('i', $payload['uid']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['role'] = $payload['role']; // VULN: role from cookie, not DB
        $_SESSION['logged_in'] = true;
        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    
    // Check remember me cookie
    if (isset($_COOKIE['remember_me'])) {
        return verifyRememberToken($_COOKIE['remember_me']);
    }
    
    return false;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
    ];
}

/**
 * Check if current user has a specific role
 */
function hasRole($requiredRole) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $roleHierarchy = ['subscriber' => 1, 'reporter' => 2, 'editor' => 3, 'admin' => 4];
    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 99;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth($minRole = 'subscriber') {
    if (!isLoggedIn()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if (!hasRole($minRole)) {
        http_response_code(403);
        include __DIR__ . '/header.php';
        echo '<div class="container"><div class="error-page"><h1>403 - Access Denied</h1><p>You do not have permission to access this page.</p><a href="/" class="btn btn-primary">Return Home</a></div></div>';
        include __DIR__ . '/footer.php';
        exit;
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    session_destroy();
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}
