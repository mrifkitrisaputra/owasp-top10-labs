<?php
/**
 * NAC Cafe - Registration Page
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Daftar';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            // Register new user (MD5 - intentionally weak)
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, loyalty_points) VALUES (?, MD5(?), ?, ?, 'guest', 100)");
            $stmt->execute([$username, $password, $email, $full_name]);

            // Log welcome bonus
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO loyalty_transactions (user_id, points, type, description) VALUES (?, 100, 'credit', 'Welcome bonus - pendaftaran akun baru')");
            $stmt->execute([$user_id]);

            log_activity("New registration: " . $username);
            $success = 'Pendaftaran berhasil! Silakan login.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-box">
            <h2>Daftar</h2>
            <p class="subtitle">Buat akun NAC Cafe baru</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= safe_output($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= safe_output($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="/register.php">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Masukkan nama lengkap" required
                           value="<?= safe_output($_POST['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required
                           value="<?= safe_output($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" required
                           value="<?= safe_output($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Daftar</button>
            </form>

            <div class="auth-links">
                <p>Sudah punya akun? <a href="/login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
