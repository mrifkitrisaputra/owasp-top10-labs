<?php
/**
 * NAC Cafe - Login Page
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        // Using MD5 (intentionally weak hashing)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = MD5(?)");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['role'] === 'disabled') {
                $error = 'Akun ini telah dinonaktifkan.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Set cookie-based session (intentionally vulnerable - Flag 4)
                set_session_cookie($user);

                log_activity("Login successful: " . $user['username']);
                header('Location: /');
                exit;
            }
        } else {
            $error = 'Username atau password salah.';
            log_activity("Login failed: " . $username);
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-box">
            <h2>Login</h2>
            <p class="subtitle">Masuk ke akun NAC Cafe Anda</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= safe_output($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required
                           value="<?= safe_output($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Login</button>
            </form>

            <div class="auth-links">
                <p>Belum punya akun? <a href="/register.php">Daftar di sini</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
