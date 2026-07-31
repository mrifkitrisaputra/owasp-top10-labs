<?php
/**
 * NAC Cafe - Admin Articles View Page (included via page.php)
 */

// This file is included from page.php, so $pdo is available
$articles = $pdo->query("SELECT a.*, u.full_name as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id ORDER BY a.created_at DESC")->fetchAll();
?>

<h3>Daftar Semua Artikel</h3>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Kategori</th>
            <th>Views</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $a): ?>
        <tr>
            <td>#<?= $a['id'] ?></td>
            <td><a href="/article.php?id=<?= $a['id'] ?>"><?= safe_output($a['title']) ?></a></td>
            <td><?= safe_output($a['author_name'] ?? 'Unknown') ?></td>
            <td><span class="card-category"><?= safe_output($a['category']) ?></span></td>
            <td><?= number_format($a['views']) ?></td>
            <td><?= $a['published'] ? '✅ Published' : '📝 Draft' ?></td>
            <td><?= format_date($a['published_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
