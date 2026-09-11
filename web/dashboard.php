<?php
declare(strict_types=1);
require __DIR__ . '/inc/auth.php';
require_login();
require __DIR__ . '/inc/db.php';

$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$title = 'Dashboard';
require __DIR__ . '/inc/header.php';
?>
<h1>Dashboard</h1>
<p>Welcome, <strong><?= e($_SESSION['username']) ?></strong>. You are logged in.</p>
<p>Registered users: <?= $count ?></p>
<?php require __DIR__ . '/inc/footer.php'; ?>
