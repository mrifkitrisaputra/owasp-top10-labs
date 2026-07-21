<?php
/**
 * NAC Cafe - Admin Backup Page
 * Contains encrypted financial backup (Flag 9)
 */
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$page_title = 'Admin - Backup Data';

// Read encrypted backup file
$encrypted_file = __DIR__ . '/data/financial_report.enc';
$encrypted_content = '';
if (file_exists($encrypted_file)) {
    $encrypted_content = file_get_contents($encrypted_file);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>☕ Admin Panel</h3>
        <ul>
            <li><a href="/admin/">📊 Dashboard</a></li>
            <li><a href="/admin/page.php?view=articles">📝 Kelola Artikel</a></li>
            <li><a href="/admin/preview.php">🔗 Preview URL</a></li>
            <li><a href="/admin/backup.php" class="active">💾 Backup Data</a></li>
            <li><a href="/admin/loyalty.php">🎖️ Loyalty System</a></li>
            <li><a href="/admin/upload.php">📁 Upload Media</a></li>
            <li><a href="/">← Kembali ke Situs</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <div class="admin-header">
            <h2>Backup Data</h2>
            <p>Kelola backup dan data terenkripsi NAC Cafe</p>
        </div>

        <!-- Database Info -->
        <div style="background: var(--bg-white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 24px;">
            <h3 style="margin-bottom: 16px;">📊 Status Database</h3>
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn() ?></div>
                    <div class="stat-label">Artikel</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?></div>
                    <div class="stat-label">Pengguna</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn() ?></div>
                    <div class="stat-label">Komentar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn() ?></div>
                    <div class="stat-label">Menu Items</div>
                </div>
            </div>
        </div>

        <!-- Encrypted Financial Backup (Flag 9) -->
        <div style="background: var(--bg-white); padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 24px;">
            <h3 style="margin-bottom: 16px;">🔐 Laporan Keuangan Terenkripsi</h3>
            <p style="color: var(--text-light); margin-bottom: 16px;">
                File backup laporan keuangan yang dienkripsi untuk keamanan. 
                Metode enkripsi: <strong>XOR</strong> dengan kunci internal API.
                Hanya dapat didekripsi oleh personel yang memiliki akses ke konfigurasi internal.
            </p>

            <?php if ($encrypted_content): ?>
            <div class="encrypted-display">
                <pre><?= htmlspecialchars($encrypted_content) ?></pre>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                File backup terenkripsi belum tersedia. Jalankan proses backup terlebih dahulu.
            </div>
            <?php endif; ?>

            <div class="alert alert-info" style="margin-top: 16px;">
                <strong>Petunjuk Dekripsi:</strong><br>
                1. Ambil data yang dienkripsi (bagian antara BEGIN dan END ENCRYPTED DATA)<br>
                2. Decode dari Base64<br>
                3. XOR dengan kunci enkripsi (API key internal - lihat konfigurasi server)<br>
                4. Hasil dekripsi berisi laporan keuangan lengkap
            </div>
        </div>

        <!-- Old Backup Notice -->
        <div class="alert alert-warning">
            <strong>Perhatian:</strong> Backup lama mungkin masih tersedia di direktori <code>/old-config/</code>. 
            Pastikan untuk menghapus file backup yang sudah tidak diperlukan untuk keamanan.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
