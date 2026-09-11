<?php
declare(strict_types=1);
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/db.php';

if (is_logged_in()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);   // new session ID after login
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: /dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}

$title = 'Log in';
require __DIR__ . '/inc/header.php';
?>
<h1>Log in</h1>
<?php if (isset($_GET['registered'])): ?><p class="success">Account created. You can log in now.</p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post">
  <label>Username <input name="username" value="<?= e($username) ?>" required></label>
  <label>Password <input type="password" name="password" required></label>
  <button type="submit">Log in</button>
</form>
<p>No account yet? <a href="/register.php">Register</a></p>
<?php require __DIR__ . '/inc/footer.php'; ?>
