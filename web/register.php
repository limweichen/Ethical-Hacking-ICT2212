<?php
declare(strict_types=1);
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/db.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $error = 'Username must be 3 to 50 letters, numbers or underscores.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: /login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            // 23000 = duplicate value in a UNIQUE column
            $error = $e->getCode() === '23000' ? 'Username already taken.' : 'Something went wrong.';
        }
    }
}

$title = 'Register';
require __DIR__ . '/inc/header.php';
?>
<h1>Register</h1>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
<form method="post">
  <label>Username <input name="username" value="<?= e($username) ?>" required></label>
  <label>Password <input type="password" name="password" required></label>
  <label>Confirm password <input type="password" name="confirm" required></label>
  <button type="submit">Create account</button>
</form>
<p>Already have an account? <a href="/login.php">Log in</a></p>
<?php require __DIR__ . '/inc/footer.php'; ?>
