<?php
/**
 * Nac News Portal - Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAuth('admin');

$conn = getDbConnection();

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalArticles = $conn->query("SELECT COUNT(*) as c FROM articles")->fetch_assoc()['c'];
$totalComments = $conn->query("SELECT COUNT(*) as c FROM comments")->fetch_assoc()['c'];
$totalViews = $conn->query("SELECT SUM(views) as v FROM articles")->fetch_assoc()['v'] ?? 0;
$totalContacts = $conn->query("SELECT COUNT(*) as c FROM contact_messages")->fetch_assoc()['c'];
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$recentContacts = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="dashboard admin-dashboard">
        <div class="dashboard-sidebar">
            <h3><i class="fas fa-crown" style="color:gold;"></i> Admin Panel</h3>
            <ul class="dashboard-nav">
                <li><a href="/admin/" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/admin/diagnostics.php"><i class="fas fa-stethoscope"></i> Diagnostics</a></li>
                <li><a href="/admin/vault.php"><i class="fas fa-vault"></i> Vault</a></li>
                <li><a href="/dashboard/"><i class="fas fa-edit"></i> Editor Panel</a></li>
                <li><a href="/"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            </ul>
        </div>

        <div class="dashboard-body">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Welcome, <?= e($_SESSION['full_name'] ?? 'Administrator') ?>. Full system overview and management.</p>

            <!-- Stats -->
            <div class="stats-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:15px;margin-bottom:30px;">
                <div class="stat-card">
                    <i class="fas fa-users" style="font-size:24px;color:#e74c3c;"></i>
                    <div class="stat-number"><?= number_format($totalUsers) ?></div>
                    <div class="stat-label">Users</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-newspaper" style="font-size:24px;color:#3498db;"></i>
                    <div class="stat-number"><?= number_format($totalArticles) ?></div>
                    <div class="stat-label">Articles</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comments" style="font-size:24px;color:#2ecc71;"></i>
                    <div class="stat-number"><?= number_format($totalComments) ?></div>
                    <div class="stat-label">Comments</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-eye" style="font-size:24px;color:#9b59b6;"></i>
                    <div class="stat-number"><?= number_format($totalViews) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-envelope" style="font-size:24px;color:#f39c12;"></i>
                    <div class="stat-number"><?= number_format($totalContacts) ?></div>
                    <div class="stat-label">Messages</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">
                <!-- Users Table -->
                <div>
                    <h3 style="margin-bottom:15px;">Recent Users</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recentUsers->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= e($user['username']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $user['role'] === 'admin' ? 'published' : ($user['role'] === 'editor' ? 'draft' : 'archived') ?>">
                                        <?= ucfirst(e($user['role'])) ?>
                                    </span>
                                </td>
                                <td><?= formatDate($user['created_at'], 'M j') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Contact Messages -->
                <div>
                    <h3 style="margin-bottom:15px;">Recent Messages</h3>
                    <?php while ($msg = $recentContacts->fetch_assoc()): ?>
                    <div style="background:#f8f9fa;padding:12px;border-radius:6px;margin-bottom:10px;">
                        <strong><?= e($msg['name']) ?></strong>
                        <span style="color:#999;font-size:12px;"><?= formatDate($msg['created_at'], 'M j') ?></span>
                        <p style="margin:5px 0 0;font-size:14px;color:#555;"><?= e(truncate($msg['message'], 80)) ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- System Info -->
            <div style="margin-top:30px;background:#1a1a2e;color:#fff;padding:20px;border-radius:8px;">
                <h3 style="color:#fff;margin-bottom:15px;"><i class="fas fa-server"></i> System Information</h3>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;font-size:14px;">
                    <div>
                        <span style="opacity:0.6;">PHP Version:</span><br>
                        <strong><?= phpversion() ?></strong>
                    </div>
                    <div>
                        <span style="opacity:0.6;">Server:</span><br>
                        <strong><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></strong>
                    </div>
                    <div>
                        <span style="opacity:0.6;">Database:</span><br>
                        <strong>MySQL <?= $conn->server_info ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
