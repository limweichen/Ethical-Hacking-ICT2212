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

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check that all fields are completed.
if (
    !is_string($currentPassword) ||
    !is_string($newPassword) ||
    !is_string($confirmPassword) ||
    $currentPassword === '' ||
    $newPassword === '' ||
    $confirmPassword === ''
) {

    $_SESSION['flash_message'] = 'Please complete all password fields.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Check password length.
if (strlen($newPassword) < 8) {

    $_SESSION['flash_message'] = 'Your new password must be at least 8 characters long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

if (strlen($newPassword) > 255) {

    $_SESSION['flash_message'] = 'Your new password is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Make sure the new passwords match.
if ($newPassword !== $confirmPassword) {

    $_SESSION['flash_message'] = 'The new passwords do not match.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Get the current user's password hash.
$stmt = $pdo->prepare(
    'SELECT password_hash
     FROM users
     WHERE id = ?'
);

$stmt->execute([$userId]);
$user = $stmt->fetch();

// Make sure the account exists.
if (!$user) {

    $_SESSION['flash_message'] = 'Unable to find your account.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Verify the current password.
if (!password_verify(
    $currentPassword,
    $user['password_hash']
)) {
    $_SESSION['flash_message'] = 'Your current password is incorrect.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Hash the new password.
$newPasswordHash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

if ($newPasswordHash === false) {

    $_SESSION['flash_message'] = 'Unable to change your password.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /profile/profile.php');
    exit;
}

// Save the new password.
$stmt = $pdo->prepare(
    'UPDATE users
     SET password_hash = ?
     WHERE id = ?'
);

$stmt->execute([
    $newPasswordHash,
    $userId
]);

// Regenerate the session ID after changing the password.
session_regenerate_id(true);

// Show success message.
$_SESSION['flash_message'] = 'Password changed successfully!';
$_SESSION['flash_type'] = 'success';

header('Location: /profile/profile.php');
exit;