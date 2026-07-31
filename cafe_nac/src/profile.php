<?php
/**
 * NAC Cafe - User Profile Page
 * VULNERABLE: IDOR - can view any user's profile by changing ID (Flag 3)
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Profil';

// VULNERABILITY: No authorization check - any user can view any profile by ID (Flag 3 - IDOR)
if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
} elseif (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: /login.php');
    exit;
}

// Fetch user profile (includes staff_notes - sensitive!)
$stmt = $pdo->prepare("SELECT id, username, email, full_name, role, bio, staff_notes, loyalty_points, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $page_title = 'Profil Tidak Ditemukan';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="alert alert-error">Pengguna tidak ditemukan.</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = 'Profil - ' . $user['full_name'];

// Get loyalty transactions
$stmt = $pdo->prepare("SELECT * FROM loyalty_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <h2><?= safe_output($user['full_name']) ?></h2>
            <p>@<?= safe_output($user['username']) ?> · <?= safe_output(ucfirst($user['role'])) ?></p>
        </div>

        <div class="profile-body">
            <div class="profile-info">
                <div class="profile-field">
                    <label>User ID</label>
                    <span>#<?= $user['id'] ?></span>
                </div>
                <div class="profile-field">
                    <label>Username</label>
                    <span><?= safe_output($user['username']) ?></span>
                </div>
                <div class="profile-field">
                    <label>Email</label>
                    <span><?= safe_output($user['email']) ?></span>
                </div>
                <div class="profile-field">
                    <label>Role</label>
                    <span><?= safe_output(ucfirst($user['role'])) ?></span>
                </div>
                <div class="profile-field" style="grid-column: 1 / -1;">
                    <label>Bio</label>
                    <span><?= safe_output($user['bio'] ?: 'Belum ada bio.') ?></span>
                </div>
                <div class="profile-field">
                    <label>Bergabung Sejak</label>
                    <span><?= format_date($user['created_at']) ?></span>
                </div>
                <div class="profile-field">
                    <label>Loyalty Points</label>
                    <span><?= number_format($user['loyalty_points']) ?> poin</span>
                </div>

                <?php if (!empty($user['staff_notes'])): ?>
                <div class="profile-field" style="grid-column: 1 / -1;">
                    <label>Catatan Staff</label>
                    <span><?= safe_output($user['staff_notes']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Loyalty Section (only for own profile) -->
            <?php if (is_logged_in() && $_SESSION['user_id'] == $user['id']): ?>
            <div class="loyalty-section">
                <div class="loyalty-label">Loyalty Points Anda</div>
                <div class="loyalty-points" id="loyaltyPoints"><?= number_format($user['loyalty_points']) ?></div>
                <div class="loyalty-label">poin tersedia</div>
                <?php if ($user['loyalty_points'] >= 50): ?>
                <button class="btn-primary" id="redeemBtn" style="margin-top: 16px;">Tukarkan 50 Poin</button>
                <?php endif; ?>
                <p style="margin-top: 12px; font-size: 13px; color: var(--text-light);">
                    Tukarkan poin Anda untuk mendapatkan diskon spesial!
                </p>
            </div>

            <!-- Transaction History -->
            <?php if (!empty($transactions)): ?>
            <div style="margin-top: 24px;">
                <h3 style="margin-bottom: 16px;">Riwayat Transaksi Poin</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Poin</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= format_date($tx['created_at']) ?></td>
                            <td><?= safe_output(ucfirst($tx['type'])) ?></td>
                            <td style="color: <?= $tx['type'] === 'credit' ? '#2E7D32' : '#C62828' ?>">
                                <?= $tx['type'] === 'credit' ? '+' : '-' ?><?= $tx['points'] ?>
                            </td>
                            <td><?= safe_output($tx['description']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
