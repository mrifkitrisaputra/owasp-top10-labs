<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(SITE_DESCRIPTION) ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📰</text></svg>">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span class="current-date"><i class="far fa-calendar-alt"></i> <?= date('l, F j, Y') ?></span>
                </div>
                <div class="top-bar-right">
                    <?php if ($currentUser): ?>
                        <span class="welcome-text">Welcome, <?= e($currentUser['full_name']) ?></span>
                        <?php if (hasRole('editor')): ?>
                            <a href="/dashboard/"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <?php endif; ?>
                        <?php if (hasRole('admin')): ?>
                            <a href="/admin/"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="/profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="/register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <span class="logo-icon"><i class="fas fa-scroll"></i></span>
                    <div class="logo-text">
                        <h1>Nac<span>News</span></h1>
                        <p class="tagline">Illuminating Truth Since 2019</p>
                    </div>
                </a>
                <div class="header-search">
                    <form action="/search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search articles..." value="<?= e($_GET['q'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <button class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/" class="<?= $currentPage === 'index' ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a></li>
                <?php
                $navCategories = getCategories();
                foreach ($navCategories as $cat):
                ?>
                <li>
                    <a href="/category.php?slug=<?= e($cat['slug']) ?>" 
                       class="<?= (isset($_GET['slug']) && $_GET['slug'] === $cat['slug']) ? 'active' : '' ?>">
                        <?php if ($cat['icon']): ?><i class="<?= e($cat['icon']) ?>"></i> <?php endif; ?>
                        <?= e($cat['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <li><a href="/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
