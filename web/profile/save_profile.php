<?php
declare(strict_types=1);

require __DIR__ . '/../inc/auth.php';
require_login();

require __DIR__ . '/../inc/db.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile/profile.php');
    exit;
}

// Require a valid CSRF token.
require_csrf_token();

$userId = (int) $_SESSION['user_id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Basic validation.
if ($name === '' || $email === '') {

    $_SESSION['flash_message'] = 'Please complete all required fields.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

if (strlen($name) > 100) {

    $_SESSION['flash_message'] = 'Your name is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

if (strlen($email) > 255) {

    $_SESSION['flash_message'] = 'Your email address is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['flash_message'] = 'Please enter a valid email address.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Make sure the email is not already used by another account. (Exclude the current user's own email.)
$stmt = $pdo->prepare(
    'SELECT id
     FROM users
     WHERE email = ?
       AND id != ?'
);

$stmt->execute([
    $email,
    $userId
]);

if ($stmt->fetch()) {

    $_SESSION['flash_message'] = 'That email address is already in use by another account.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Update the current user's profile.
$stmt = $pdo->prepare(
    'UPDATE users
     SET name = ?,
         email = ?
     WHERE id = ?'
);

$stmt->execute([
    $name,
    $email,
    $userId
]);

// Make sure the account still exists.
if ($stmt->rowCount() === 0) {

    $checkStmt = $pdo->prepare(
        'SELECT id
         FROM users
         WHERE id = ?'
    );

    $checkStmt->execute([$userId]);

    if (!$checkStmt->fetch()) {

        $_SESSION['flash_message'] = 'Unable to find your account.';
        $_SESSION['flash_type'] = 'error';

        header('Location: /profile/profile.php');
        exit;
    }
}

// Update session values so the new information appears immediately.
$_SESSION['username'] = $name;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;

// Show success message.
$_SESSION['flash_message'] = 'Profile updated successfully!';
$_SESSION['flash_type'] = 'success';

header('Location: /profile/profile.php');
exit;