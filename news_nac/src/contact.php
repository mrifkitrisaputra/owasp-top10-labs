<?php
/**
 * Nac News Portal - Contact Page
 */
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success = 'Thank you for your message! We\'ll get back to you within 24-48 hours.';
        } else {
            $error = 'Failed to send message. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="search-header" style="text-align:center;margin-bottom:30px;">
        <h1><i class="fas fa-envelope"></i> Contact Us</h1>
        <p>Have a tip, question, or feedback? We'd love to hear from you.</p>
    </div>

    <div class="contact-grid">
        <div class="contact-info-card">
            <h3><i class="fas fa-building"></i> Get in Touch</h3>
            
            <div class="contact-detail">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <strong>Address</strong><br>
                    1247 Library Avenue, Suite 400<br>
                    Boston, MA 02101
                </div>
            </div>
            
            <div class="contact-detail">
                <i class="fas fa-envelope"></i>
                <div>
                    <strong>Email</strong><br>
                    contact@nac-news.com<br>
                    tips@nac-news.com
                </div>
            </div>
            
            <div class="contact-detail">
                <i class="fas fa-phone"></i>
                <div>
                    <strong>Phone</strong><br>
                    +1 (555) 010-0100
                </div>
            </div>
            
            <div class="contact-detail">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Office Hours</strong><br>
                    Mon - Fri: 9:00 AM - 6:00 PM EST<br>
                    Sat - Sun: Closed
                </div>
            </div>
        </div>

        <div class="form-container" style="max-width:none;">
            <h3 style="font-family:var(--font-serif);margin-bottom:20px;"><i class="fas fa-paper-plane"></i> Send a Message</h3>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/contact.php">
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" placeholder="Your name" value="<?= e($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="What's this about?" value="<?= e($_POST['subject'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" placeholder="Your message..." rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
