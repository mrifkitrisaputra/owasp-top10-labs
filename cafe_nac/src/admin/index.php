<?php
/**
 * NAC Cafe - Admin Dashboard
 * Access controlled by cookie (intentionally vulnerable - Flag 4)
 */
require_once __DIR__ . '/../includes/functions.php';

// Check admin access via cookie (vulnerable - Flag 4)
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    $page_title = 'Akses Ditolak';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="container"><div class="alert alert-error">Anda tidak memiliki akses ke halaman ini. Hanya administrator yang diizinkan.</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$page_title = 'Admin Dashboard';

// Stats
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Revenue data (Flag 4 - this is the flag value displayed as "hidden revenue")
$secret_revenue = 847293650;

// Recent articles
$recent_articles = $pdo->query("SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll();

// Recent users
$recent_users = $pdo->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>☕ Admin Panel</h3>
        <ul>
            <li><a href="/admin/" class="active">📊 Dashboard</a></li>
            <li><a href="/admin/page.php?view=articles">📝 Kelola Artikel</a></li>
            <li><a href="/admin/preview.php">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Dashboard</h2>
            <p>Selamat datang di panel admin NAC Cafe</p>
        </div>

        <!-- Statistics -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $total_articles ?></div>
                <div class="stat-label">Total Artikel</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_comments ?></div>
                <div class="stat-label">Total Komentar</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $total_contacts ?></div>
                <div class="stat-label">Pesan Masuk</div>
            </div>
        </div>

        <!-- Secret Revenue Card (Flag 4) -->
        <div class="stat-grid">
            <div class="stat-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, #1a237e, #311b92); color: white;">
                <div class="stat-label" style="color: rgba(255,255,255,0.7);">Total Pendapatan Kotor (Rahasia)</div>
                <div class="stat-value" style="color: #FFD600; font-size: 40px;">Rp <?= number_format($secret_revenue, 0, ',', '.') ?></div>
                <div class="stat-label" style="color: rgba(255,255,255,0.5); margin-top: 8px;">
                    Kode Internal: <?= $secret_revenue ?>
                    <!-- Angka ini merupakan data rahasia perusahaan -->
                </div>
            </div>
        </div>

        <!-- Recent Articles -->
        <h3 style="margin: 24px 0 16px;">Artikel Terbaru</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Views</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_articles as $a): ?>
                <tr>
                    <td>#<?= $a['id'] ?></td>
                    <td><?= safe_output($a['title']) ?></td>
                    <td><?= safe_output($a['author_name'] ?? 'Unknown') ?></td>
                    <td><span class="card-category"><?= safe_output($a['category']) ?></span></td>
                    <td><?= number_format($a['views']) ?></td>
                    <td><?= format_date($a['published_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Recent Users -->
        <h3 style="margin: 24px 0 16px;">Pengguna Terbaru</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Role</th>
                    <th>Bergabung</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td><?= safe_output($u['username']) ?></td>
                    <td><?= safe_output($u['full_name']) ?></td>
                    <td><?= safe_output(ucfirst($u['role'])) ?></td>
                    <td><?= format_date($u['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="alert alert-warning" style="margin-top: 24px;">
            <strong>Catatan Keamanan:</strong> Pastikan backup data terenkripsi selalu tersedia. Cek halaman <a href="/admin/backup.php">Backup</a> secara berkala. File konfigurasi internal disimpan di <code>/config/secret.php</code>.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
