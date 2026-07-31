<?php
require_once 'config.php';
require_once 'auth.php';

// FLAG 8: IDOR vulnerability - can access any log_id without proper authorization
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logs') {
    // Weak authentication check
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }

    try {
        $conn = getDBConnection();

        // FLAG 8: IDOR - can access specific log by ID without checking ownership
        if (isset($_GET['log_id'])) {
            $logId = (int)$_GET['log_id'];

            // Vulnerable: No authorization check - bisa akses log siapa saja
            // JOIN dengan users untuk dapat username
            $stmt = $conn->prepare("SELECT al.*, u.username 
                                   FROM admin_logs al 
                                   LEFT JOIN users u ON al.admin_id = u.id 
                                   WHERE al.id = ?");
            $stmt->execute([$logId]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($log) {
                echo json_encode(['success' => true, 'data' => $log]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Log not found']);
            }
            exit();
        }

        // List logs - HANYA menampilkan log milik user yang sedang login
        // Ini membuat FLAG 8 tersembunyi karena log admin lain tidak muncul di list
        if (isAdmin()) {
            // Hanya tampilkan logs yang dibuat oleh admin/librarian yang sedang login
            // JOIN dengan users untuk dapat username
            $stmt = $conn->prepare("SELECT al.*, u.username 
                                   FROM admin_logs al 
                                   LEFT JOIN users u ON al.admin_id = u.id 
                                   WHERE al.admin_id = ? 
                                   ORDER BY al.log_date DESC LIMIT 25");
            $stmt->execute([$_SESSION['user_id']]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Hint: Berikan petunjuk bahwa ada logs lain (tanpa menunjukkan isinya)
            $countStmt = $conn->query("SELECT COUNT(*) as total FROM admin_logs");
            $totalLogs = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo json_encode([
                'success' => true, 
                'data' => $logs,
                'meta' => [
                    'showing' => count($logs),
                    'total_in_system' => $totalLogs,
                    'note' => 'Only showing your activity logs'
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
        }

    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching logs']);
    }
    exit();
}

// FLAG 9 - Stage 1: User Session Info Endpoint (Info Disclosure Vulnerability)
// This endpoint leaks session structure and privilege information
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'user_session') {
    // Weak check: requires login but doesn't validate who can query which user
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }

    try {
        $conn = getDBConnection();
        
        // IDOR vulnerability: Can query any user_id
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];
        
        $stmt = $conn->prepare("SELECT id, username, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Info disclosure: Reveals session token structure
            // Player can use this to understand how to forge privilege token
            $sessionData = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'privilege_level' => ($user['role'] === 'superadmin') ? 'elevated' : 'standard',
                // Hint: Shows token format (predictable generation)
                'session_token' => hash('sha256', $user['id'] . $user['username'] . $user['role']),
                'created_at' => $user['created_at']
            ];
            
            echo json_encode(['success' => true, 'data' => $sessionData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching session info']);
    }
    exit();
}

// FLAG 9 - Stage 2: System Config (Privilege Escalation via Header Injection)
// Requires X-Privilege-Token header with valid superadmin token
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'config') {
    // Basic admin check
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit();
    }

    try {
        $conn = getDBConnection();
        
        $showHidden = isset($_GET['show_hidden']) && $_GET['show_hidden'] === 'true';

        if ($showHidden) {
            // FLAG 9: Privilege check menggunakan custom header
            // Vulnerability: Dapat diforge jika player tahu format token
            $privilegeToken = isset($_SERVER['HTTP_X_PRIVILEGE_TOKEN']) ? $_SERVER['HTTP_X_PRIVILEGE_TOKEN'] : '';
            
            if (empty($privilegeToken)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Hidden configurations require superadmin privilages',
                    'hint' => 'Superadmin privilege token required in X-Privilege-Token header'
                ]);
                exit();
            }
            
            // Validate privilege token
            // Get superadmin user info to verify token
            $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE role = 'superadmin' LIMIT 1");
            $stmt->execute();
            $superadmin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$superadmin) {
                echo json_encode(['success' => false, 'message' => 'System error: No superadmin found']);
                exit();
            }
            
            // Generate expected token
            $expectedToken = hash('sha256', $superadmin['id'] . $superadmin['username'] . $superadmin['role']);
            
            // Verify token
            if ($privilegeToken !== $expectedToken) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Invalid privilege token',
                    'debug' => 'Token verification failed'
                ]);
                exit();
            }
            
            // Token valid! Grant access to hidden configs
            $stmt = $conn->query("SELECT * FROM system_config ORDER BY is_hidden ASC, config_key ASC");
        } else {
            // Show only visible configs
            $stmt = $conn->query("SELECT * FROM system_config WHERE is_hidden = 0 ORDER BY config_key ASC");
        }

        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $configs]);

    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching config']);
    }
    exit();
}

// Get archive information (FLAG 6)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'archive') {
    if (!checkAuth()) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }

    try {
        $conn = getDBConnection();

        // Query langsung ke archive_books (tanpa join / subquery)
        $stmt = $conn->query("
            SELECT
                id,
                original_book_id,
                title,
                notes,
                archive_code,
                archived_date,
                storage_location
            FROM archive_books
            ORDER BY id ASC
        ");

        $archives = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $archives]);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching archives']);
    }
    exit();
}

// Dashboard stats
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'stats') {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit();
    }

    try {
        $conn = getDBConnection();

        $stats = [];

        // Total books
        $stmt = $conn->query("SELECT COUNT(*) as count FROM books");
        $stats['total_books'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Active borrowings
        $stmt = $conn->query("SELECT COUNT(*) as count FROM borrowings WHERE status = 'borrowed'");
        $stats['active_borrowings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Overdue books
        $stmt = $conn->query("SELECT COUNT(*) as count FROM borrowings WHERE status = 'overdue'");
        $stats['overdue_books'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo json_encode(['success' => true, 'data' => $stats]);

    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching stats']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid admin request']);
?>
