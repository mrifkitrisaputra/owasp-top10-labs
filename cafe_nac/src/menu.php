<?php
/**
 * NAC Cafe - Menu Page
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Menu';

// Get all menu items grouped by category
$stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category, name");
$all_items = $stmt->fetchAll();

$grouped = [];
foreach ($all_items as $item) {
    $grouped[$item['category']][] = $item;
}

$category_icons = [
    'Kopi' => '☕',
    'Non-Kopi' => '🍵',
    'Makanan' => '🥐',
    'Spesial' => '⭐'
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Menu Kami</h1>
        <p>Pilihan kopi dan makanan terbaik untuk Anda</p>
    </div>
</div>

<div class="container">
    <?php foreach ($grouped as $category => $items): ?>
    <h2 class="menu-category-title"><?= $category_icons[$category] ?? '🍽️' ?> <?= safe_output($category) ?></h2>

    <div class="menu-grid">
        <?php foreach ($items as $item): ?>
        <div class="menu-card">
            <div class="menu-card-image" style="background: linear-gradient(135deg, <?= $category === 'Kopi' ? '#5D4037, #3E2723' : ($category === 'Non-Kopi' ? '#2E7D32, #1B5E20' : ($category === 'Spesial' ? '#FF8F00, #E65100' : '#1565C0, #0D47A1')) ?>);">
                <?= $category_icons[$category] ?? '🍽️' ?>
            </div>
            <div class="menu-card-body">
                <h3><?= safe_output($item['name']) ?></h3>
                <p><?= safe_output($item['description']) ?></p>
                <span class="menu-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div style="text-align: center; margin: 48px 0 0; padding: 32px; background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow);">
        <h3 style="margin-bottom: 8px;">🤫 Tahukah Anda?</h3>
        <p style="color: var(--text-light);">Kami juga memiliki menu rahasia yang tidak tercantum di sini. Tanyakan kepada barista kami jika Anda penasaran!</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
