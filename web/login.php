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

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM users
         WHERE username = ?'
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: /dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
}

$title = 'Log In';

require __DIR__ . '/inc/header.php';
?>

<section class="auth-page">

<div class="auth-card">

    <div class="auth-logo">
        E
    </div>

    <h1>
        Welcome back
    </h1>

    <p class="auth-subtitle">
        Log in to manage your personal events.
    </p>

    <?php if (isset($_GET['registered'])): ?>

        <div class="auth-success">
            Account created successfully. You can log in now.
        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="auth-error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="/login.php"
        class="auth-form"
    >

        <label for="username">
            Username
        </label>

        <input
            type="text"
            id="username"
            name="username"
            placeholder="Enter your username"
            value="<?= e($username) ?>"
            maxlength="50"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button
            type="submit"
            class="primary-button auth-submit"
        >
            Log In
        </button>

    </form>

    <p class="auth-switch">

        Don't have an account?

        <a href="/register.php">
            Create one
        </a>

    </p>

</div>

</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
