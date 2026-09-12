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
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_csrf_token();
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (
        $username === '' ||
        $name === '' ||
        $email === '' ||
        $password === ''
    ) {
        $error = 'Please fill in all fields.';

    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {

        $error =
            'Username must be 3 to 50 letters, numbers or underscores.';

    } elseif (strlen($name) > 100) {

        $error = 'Name is too long.';

    } elseif (
        strlen($email) > 255 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 8) {

        $error = 'Password must be at least 8 characters.';

    } elseif ($password !== $confirm) {

        $error = 'Passwords do not match.';

    } else {

        try {

            $stmt = $pdo->prepare(
                'INSERT INTO users
                    (username, name, email, password_hash)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $username,
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            header('Location: /login.php?registered=1');
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $error =
                    'Username or email is already registered.';

            } else {

                $error =
                    'Something went wrong. Please try again.';
            }
        }
    }
}

$title = 'Sign Up';

require __DIR__ . '/inc/header.php';
?>

<section class="auth-page">

<div class="auth-card">

    <div class="auth-logo">
        E
    </div>

    <h1>
        Create your account
    </h1>

    <p class="auth-subtitle">
        Sign up to start organising your personal events.
    </p>

    <?php if ($error !== ''): ?>

        <div class="auth-error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        action="/register.php"
        class="auth-form"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(get_csrf_token()) ?>"
        >

        <label for="username">
            Username
        </label>

        <input
            type="text"
            id="username"
            name="username"
            placeholder="Choose a username"
            value="<?= e($username) ?>"
            maxlength="50"
            required
        >

        <label for="name">
            Name
        </label>

        <input
            type="text"
            id="name"
            name="name"
            placeholder="Your name"
            value="<?= e($name) ?>"
            maxlength="100"
            required
        >

        <label for="email">
            Email
        </label>

        <input
            type="email"
            id="email"
            name="email"
            placeholder="alex@example.com"
            value="<?= e($email) ?>"
            maxlength="255"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Create a password"
            required
        >

        <label for="confirm">
            Confirm password
        </label>

        <input
            type="password"
            id="confirm"
            name="confirm"
            placeholder="Enter your password again"
            required
        >

        <button
            type="submit"
            class="primary-button auth-submit"
        >
            Create Account
        </button>

    </form>

    <p class="auth-switch">

        Already have an account?

        <a href="/login.php">
            Log in
        </a>

    </p>

</div>

</section>

<?php require __DIR__ . '/inc/footer.php'; ?>