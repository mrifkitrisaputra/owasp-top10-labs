<?php
require_once 'config.php';
require_once 'auth.php';

// FLAG 6: File inclusion via debug parameter (LFI vulnerability)
// MUST BE CHECKED FIRST before other endpoints!
if (isset($_GET['debug']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    
    // Vulnerable to LFI - allows reading local files
    // Try different paths to make it work
    $filePaths = [
        $file,                          // Direct path: config.php
        __DIR__ . '/' . $file,          // Same directory: /backend/config.php
        '../' . $file,                  // Parent directory
        __DIR__ . '/../' . $file,       // Absolute parent
    ];
    
    $found = false;
    foreach ($filePaths as $tryPath) {
        if (file_exists($tryPath)) {
            // Show file contents as plain text (not executed)
            header('Content-Type: text/plain');
            echo "=== DEBUG MODE: Reading file: $file ===\n\n";
            echo file_get_contents($tryPath);
            $found = true;
            exit();
        }
    }
    
    if (!$found) {
        header('Content-Type: text/plain');
        echo "DEBUG ERROR: File not found: $file\n";
        echo "Tried paths:\n";
        foreach ($filePaths as $p) {
            echo "  - $p " . (file_exists($p) ? "[EXISTS]" : "[NOT FOUND]") . "\n";
        }
        exit();
    }
}

// Get all books
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!isset($_GET['action']) || $_GET['action'] === 'books')) {
    try {
        $conn = getDBConnection();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        $category = $_GET['category'] ?? null;
        
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM books WHERE 1=1";
        $countParams = [];
        if ($category) {
            $countSql .= " AND category = ?";
            $countParams[] = $category;
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $books,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching books']);
    }
    exit();
}

// Get single book
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'book' && isset($_GET['id'])) {
    try {
        $conn = getDBConnection();
        $id = (int)$_GET['id'];
        
        $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($book) {
            echo json_encode(['success' => true, 'data' => $book]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Book not found']);
        }
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching book']);
    }
    exit();
}

// FLAG 1: Search with SQL injection vulnerability
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search') {
    try {
        $conn = getDBConnection();
        $query = $_GET['q'] ?? '';
        
        // Intentionally vulnerable to SQL injection (FLAG 1)
        $sql = "SELECT * FROM books WHERE title LIKE '%$query%' OR author LIKE '%$query%' OR isbn LIKE '%$query%'";
        
        $stmt = $conn->query($sql);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $books]);
        
    } catch(PDOException $e) {
        // Show error for exploitation
        echo json_encode(['success' => false, 'message' => 'Search error: ' . $e->getMessage()]);
    }
    exit();
}

// Get categories
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'categories') {
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['success' => true, 'data' => $categories]);
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching categories']);
    }
    exit();
}

// Get user borrowings
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'borrowings') {
    if (!checkAuth()) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }
    
    try {
        $conn = getDBConnection();
        $userId = $_SESSION['user_id'];
        
        $sql = "SELECT b.*, bk.title, bk.author, bk.isbn 
                FROM borrowings b 
                JOIN books bk ON b.book_id = bk.id 
                WHERE b.user_id = ? 
                ORDER BY b.borrow_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $borrowings]);
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error fetching borrowings']);
    }
    exit();
}

// Borrow a book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'borrow') {
    if (!checkAuth()) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $bookId = $data['book_id'] ?? null;
    
    if (!$bookId) {
        echo json_encode(['success' => false, 'message' => 'Book ID required']);
        exit();
    }
    
    try {
        $conn = getDBConnection();
        
        // Check availability
        $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$book || $book['available_copies'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Book not available']);
            exit();
        }
        
        // Create borrowing record
        $userId = $_SESSION['user_id'];
        $borrowDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+14 days'));
        
        $stmt = $conn->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
        $stmt->execute([$userId, $bookId, $borrowDate, $dueDate]);
        
        // Update available copies
        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
        $stmt->execute([$bookId]);
        
        echo json_encode(['success' => true, 'message' => 'Book borrowed successfully']);
        
    } catch(PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error borrowing book']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
