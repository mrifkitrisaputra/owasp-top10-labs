<?php
/**
 * NAC Cafe - Contact Page
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Kontak';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Nama, email, dan pesan harus diisi.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = 'Pesan Anda berhasil dikirim! Kami akan menghubungi Anda segera.';
        log_activity("Contact form submitted by: " . $name);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Hubungi Kami</h1>
        <p>Pertanyaan, saran, atau ingin bekerja sama? Hubungi kami!</p>
    </div>
</div>

<div class="container">
    <div class="contact-grid">
        <div class="contact-info">
            <h3>Informasi Kontak</h3>
            
            <div class="contact-item">
                <span class="contact-icon">📍</span>
                <div>
                    <strong>Alamat</strong><br>
                    Jl. Sudirman No. 123<br>
                    Jakarta Selatan, 12190
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">📞</span>
                <div>
                    <strong>Telepon</strong><br>
                    +62 812-3456-7890
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">✉️</span>
                <div>
                    <strong>Email</strong><br>
                    info@naccafe.id
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">🕐</span>
                <div>
                    <strong>Jam Operasional</strong><br>
                    Senin - Jumat: 07:00 - 22:00<br>
                    Sabtu - Minggu: 08:00 - 23:00
                </div>
            </div>

            <div class="contact-item">
                <span class="contact-icon">📱</span>
                <div>
                    <strong>Media Sosial</strong><br>
                    Instagram: @naccafe.id<br>
                    Twitter: @naccafe_id
                </div>
            </div>
        </div>

        <div class="contact-form-box">
            <h3>Kirim Pesan</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= safe_output($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= safe_output($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="/contact.php">
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" id="name" name="name" placeholder="Nama Anda" required
                           value="<?= safe_output($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="email@contoh.com" required
                           value="<?= safe_output($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="subject">Subjek</label>
                    <input type="text" id="subject" name="subject" placeholder="Subjek pesan"
                           value="<?= safe_output($_POST['subject'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="message">Pesan</label>
                    <textarea id="message" name="message" placeholder="Tulis pesan Anda..." required><?= safe_output($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Kirim Pesan</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
