<?php
/**
 * Nac News Portal - Login
 */
$pageTitle = 'Login';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = authenticateUser($username, $password);
        if ($result['success']) {
            // Set remember me cookie if requested
            if ($remember) {
                $token = generateRememberToken($result['user']['id'], $result['user']['role']);
                setcookie('remember_me', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
            }
            
            $redirectTo = $_POST['redirect'] ?? '/';
            header('Location: ' . $redirectTo);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="form-page">
        <div class="form-container">
            <h2><i class="fas fa-sign-in-alt"></i> Sign In</h2>
            <p class="subtitle">Welcome back to Nac News</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/login.php">
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" 
                           value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me for 30 days</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <p class="text-center" style="margin-top: 20px; font-size: 0.9rem; color: var(--text-light);">
                Don't have an account? <a href="/register.php">Create one</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
