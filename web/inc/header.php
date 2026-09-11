<?php /** @var string $title */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'ICT2212_STUDENT17') ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
  <nav>
    <a href="/" class="brand">ICT2212_STUDENT17</a>
    <?php if (is_logged_in()): ?>
      <span>Hi, <?= e($_SESSION['username']) ?></span>
      <a href="/logout.php">Log out</a>
    <?php else: ?>
      <a href="/login.php">Log in</a>
      <a href="/register.php">Register</a>
    <?php endif; ?>
  </nav>
  <main>
