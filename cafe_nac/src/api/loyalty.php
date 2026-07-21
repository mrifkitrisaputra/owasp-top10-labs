<?php
/**
 * NAC Cafe - Loyalty Points API
 * VULNERABLE: Race condition in point redemption (Flag 10)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get user from session or cookie
$user_data = get_cookie_user();
if (!$user_data || !isset($user_data['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Anda harus login terlebih dahulu']);
    exit;
}

$user_id = (int)$user_data['user_id'];
$action = $_POST['action'] ?? '';
$points_to_redeem = (int)($_POST['points'] ?? 50);

if ($action !== 'redeem') {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

if ($points_to_redeem < 10 || $points_to_redeem > 100) {
    echo json_encode(['error' => 'Jumlah poin tidak valid (10-100)']);
    exit;
}

// VULNERABILITY: Race condition (Flag 10)
// Step 1: Read current points (no locking!)
$stmt = $pdo->prepare("SELECT loyalty_points FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'Pengguna tidak ditemukan']);
    exit;
}

$current_points = (int)$user['loyalty_points'];

// Step 2: Check if enough points
if ($current_points < $points_to_redeem) {
    echo json_encode(['error' => 'Poin tidak mencukupi. Saldo: ' . $current_points]);
    exit;
}

// INTENTIONAL DELAY - makes race condition exploitable
// Simulates processing time (database transaction, external API call, etc.)
usleep(200000); // 200ms delay

// Step 3: Deduct points (without proper transaction/locking)
$stmt = $pdo->prepare("UPDATE users SET loyalty_points = loyalty_points - ? WHERE id = ?");
$stmt->execute([$points_to_redeem, $user_id]);

// Log transaction
$stmt = $pdo->prepare("INSERT INTO loyalty_transactions (user_id, points, type, description) VALUES (?, ?, 'debit', 'Penukaran poin loyalty')");
$stmt->execute([$user_id, $points_to_redeem]);

// Step 4: Read updated points
$stmt = $pdo->prepare("SELECT loyalty_points FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$updated = $stmt->fetch();
$new_points = (int)$updated['loyalty_points'];

// Check for anomaly (negative points = race condition exploited!)
if ($new_points < 0) {
    // Debug info exposed when points go negative (Flag 10)
    log_activity("CRITICAL: Negative loyalty points detected for user {$user_id}! Points: {$new_points}");

    echo json_encode([
        'success' => false,
        'error' => 'CRITICAL ERROR: Anomali terdeteksi pada sistem loyalitas!',
        'remaining' => $new_points,
        'debug' => [
            'message' => 'Race condition detected in loyalty system',
            'internal_code' => 'supplier_hutang_3bulan',
            'details' => 'Financial anomaly - concurrency issue in point redemption. Supplier payment records affected.',
            'timestamp' => date('Y-m-d H:i:s'),
            'trace' => 'loyalty_api.php:line_67 -> deduct_points() -> no_mutex_lock'
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Berhasil menukarkan ' . $points_to_redeem . ' poin!',
    'redeemed' => $points_to_redeem,
    'remaining' => $new_points
]);
