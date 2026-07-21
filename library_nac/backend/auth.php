<?php
/**
 * Auth Handler - Refactored Version
 */

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
error_reporting(0); // Mematikan error agar format JSON tidak rusak

require_once 'config.php';

// --- FUNCTIONS ---

/**
 * FLAG 3: Cookie manipulation
 * Mengecek apakah user memiliki session aktif atau cookie bypass.
 */
function checkAuth() {
    if (isset($_COOKIE['valid_user']) && $_COOKIE['valid_user'] === 'true') {
        return true;
    }
    return isset($_SESSION['user_id']);
}

/**
 * FLAG 4: Session analysis
 * Mengecek hak akses admin/librarian.
 */
function isAdmin() {
    if (isset($_SESSION['admin_access']) && $_SESSION['admin_access'] === 'granted') {
        header("X-Admin-Access-Status: granted");

        $role = $_SESSION['role'] ?? '';
        if ($role === 'admin' || $role === 'librarian') {
            return true;
        }
    }

    // Fallback untuk role admin murni tanpa flag session khusus
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// --- ROUTING ---

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'check':
        handleCheck();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        // Opsional: Handle jika action tidak ditemukan
        break;
}

// --- HANDLERS ---

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $data     = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        sendResponse(false, 'Username and password required');
    }

    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $passwordMatch = false;

            // Flag 8 Discovery: Support SHA256 atau Bcrypt
            if (strlen($user['password']) === 64) {
                $passwordMatch = ($user['password'] === hash('sha256', $password));
            } else {
                $passwordMatch = password_verify($password, $user['password']);
            }

            if ($passwordMatch) {
                // Set Session
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                if (in_array($user['role'], ['admin', 'librarian'])) {
                    $_SESSION['admin_access'] = 'granted';
                    header("X-Session-Access-Level: granted");
                }

                // Set Cookies (Flag 3 Vulnerability point)
                setcookie('valid_user', 'true', time() + 86400, '/');
                setcookie('user_role', $user['role'], time() + 86400, '/');

                // Update Last Login
                $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$user['id']]);

                sendResponse(true, 'Login successful', [
                    'user' => ['username' => $user['username'], 'role' => $user['role']]
                ]);
            }
        }

        sendResponse(false, 'Invalid credentials');

    } catch (Exception $e) {
        sendResponse(false, 'Login error');
    }
}

function handleCheck() {
    ob_clean();
    echo json_encode([
        'authenticated' => checkAuth(),
        'is_admin'      => isAdmin(),
        'user'          => [
            'username' => $_SESSION['username'] ?? null,
            'role'     => $_SESSION['role'] ?? 'guest'
        ]
    ]);
    exit();
}

function handleLogout() {
    session_destroy();
    setcookie('valid_user', '', time() - 3600, '/');
    setcookie('user_role', '', time() - 3600, '/');
    
    sendResponse(true, 'Logged out');
}

/**
 * Helper untuk mengirim JSON response secara konsisten
 */
function sendResponse($success, $message, $extraData = []) {
    ob_clean();
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $extraData);
    
    echo json_encode($response);
    exit();
}
