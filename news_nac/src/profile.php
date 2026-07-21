<?php
/**
 * Nac News Portal - User Profile
 * VULNERABILITY: IDOR - No access control on viewing other users' profiles (Flag 3)
 */
$pageTitle = 'Profile';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// VULNERABILITY: Any authenticated user can view any profile by changing the id parameter
// No authorization check - just authentication
if (!isLoggedIn()) {
    header('Location: /login.php?redirect=/profile.php');
    exit;
}

$currentUser = getCurrentUser();

// VULNERABILITY: Uses user-supplied ID without authorization check
$userId = isset($_GET['id']) ? intval($_GET['id']) : $currentUser['id'];

$user = getUserById($userId);

if (!$user) {
    $pageTitle = 'User Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="error-page"><h1>404</h1><p>User not found.</p><a href="/" class="btn btn-primary">Back to Home</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $user['full_name'] . ' - Profile';

// Get user's articles if they're a reporter/editor
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT COUNT(*) as article_count FROM articles WHERE author_id = ? AND status = 'published'");
$stmt->bind_param('i', $userId);
$stmt->execute();
$articleCount = $stmt->get_result()->fetch_assoc()['article_count'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="profile-page">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2><?= e($user['full_name']) ?></h2>
                <span>@<?= e($user['username']) ?></span>
                <div class="profile-role"><?= e(ucfirst($user['role'])) ?></div>
            </div>
            
            <div class="profile-body">
                <?php if ($user['bio']): ?>
                <div style="margin-bottom:24px;">
                    <h3 style="font-family:var(--font-serif);font-size:1.1rem;margin-bottom:8px;">About</h3>
                    <p style="color:var(--text-secondary);line-height:1.7;"><?= e($user['bio']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="profile-info">
                    <div class="info-item">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <span><?= e($user['email']) ?></span>
                    </div>
                    
                    <?php if ($user['phone']): ?>
                    <div class="info-item">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <span><?= e($user['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <label><i class="fas fa-calendar"></i> Member Since</label>
                        <span><?= formatDate($user['created_at']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label><i class="fas fa-newspaper"></i> Articles Published</label>
                        <span><?= $articleCount ?></span>
                    </div>
                    
                    <?php if ($user['recovery_email']): ?>
                    <div class="info-item">
                        <label><i class="fas fa-shield-alt"></i> Recovery Email</label>
                        <span><?= e($user['recovery_email']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($userId == $currentUser['id']): ?>
                <div style="margin-top:24px;text-align:center;">
                    <a href="/dashboard/" class="btn btn-outline btn-sm">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
