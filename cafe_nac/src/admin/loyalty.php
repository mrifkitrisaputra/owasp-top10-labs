<?php
/**
 * NAC Cafe - Admin Loyalty Management
 * Shows loyalty transactions and debug info
 */
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$page_title = 'Admin - Loyalty System';

// Get all users with loyalty points
$users = $pdo->query("SELECT id, username, full_name, loyalty_points FROM users WHERE role != 'disabled' ORDER BY loyalty_points DESC")->fetchAll();

// Get recent transactions
$transactions = $pdo->query("SELECT lt.*, u.username, u.full_name FROM loyalty_transactions lt LEFT JOIN users u ON lt.user_id = u.id ORDER BY lt.created_at DESC LIMIT 20")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>☕ Admin Panel</h3>
        <ul>
            <li><a href="/admin/">📊 Dashboard</a></li>
            <li><a href="/admin/page.php?view=articles">📝 Kelola Artikel</a></li>
            <li><a href="/admin/preview.php">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php" class="active">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Loyalty System Management</h2>
            <p>Kelola poin loyalitas pelanggan NAC Cafe</p>
        </div>

        <!-- User Points Overview -->
        <h3 style="margin-bottom: 16px;">Saldo Poin Pengguna</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Poin</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td><?= safe_output($u['username']) ?></td>
                    <td><?= safe_output($u['full_name']) ?></td>
                    <td style="font-weight: 600; color: <?= $u['loyalty_points'] < 0 ? '#C62828' : '#2E7D32' ?>;">
                        <?= number_format($u['loyalty_points']) ?>
                    </td>
                    <td>
                        <?php if ($u['loyalty_points'] < 0): ?>
                            <span style="color: #C62828;">⚠️ Anomali</span>
                        <?php else: ?>
                            <span style="color: #2E7D32;">✅ Normal</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Transaction History -->
        <h3 style="margin: 24px 0 16px;">Riwayat Transaksi Terbaru</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Tipe</th>
                    <th>Poin</th>
                    <th>Keterangan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td>#<?= $tx['id'] ?></td>
                    <td><?= safe_output($tx['username'] ?? 'Unknown') ?></td>
                    <td><?= safe_output(ucfirst($tx['type'])) ?></td>
                    <td style="color: <?= $tx['type'] === 'credit' ? '#2E7D32' : '#C62828' ?>;">
                        <?= $tx['type'] === 'credit' ? '+' : '-' ?><?= $tx['points'] ?>
                    </td>
                    <td><?= safe_output($tx['description']) ?></td>
                    <td><?= format_date($tx['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="alert alert-info" style="margin-top: 24px;">
            <strong>Info:</strong> Sistem redemption poin dapat diakses oleh pengguna melalui halaman profil mereka. 
            Setiap penukaran mengurangi 50 poin. Perhatikan jika ada anomali poin negatif - ini bisa mengindikasikan 
            masalah pada sistem concurrency.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
