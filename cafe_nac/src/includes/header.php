<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? safe_output($page_title) . ' - NAC Cafe' : 'NAC Cafe - Berita & Cerita Kopi Nusantara' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php
    // Process theme preferences (intentionally vulnerable - Flag 8)
    $theme_extra = get_user_preferences();
    if (!empty($theme_extra)) {
        echo "<!-- Custom Theme: " . $theme_extra . " -->\n";
    }
    ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <a href="/">
                        <span class="logo-icon">☕</span>
                        <span class="logo-text">NAC <span class="logo-accent">Cafe</span></span>
                    </a>
                    <p class="tagline">Berita & Cerita Kopi Nusantara</p>
                </div>
                <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="/" <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : '' ?>>Beranda</a></li>
                        <li><a href="/news.php" <?= basename($_SERVER['PHP_SELF']) == 'news.php' ? 'class="active"' : '' ?>>Berita</a></li>
                        <li><a href="/menu.php" <?= basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'class="active"' : '' ?>>Menu</a></li>
                        <li><a href="/contact.php" <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'class="active"' : '' ?>>Kontak</a></li>
                        <?php if (is_logged_in()): ?>
                            <li><a href="/profile.php" <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : '' ?>>Profil</a></li>
                            <li><a href="/logout.php" class="btn-nav">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/login.php" class="btn-nav">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="site-main">
