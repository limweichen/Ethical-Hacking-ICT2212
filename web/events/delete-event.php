<?php
declare(strict_types=1);

require __DIR__ . '/../inc/auth.php';
require_login();

require __DIR__ . '/../inc/db.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /events/events.php');
    exit;
}

// Require a valid CSRF token.
require_csrf_token();

$userId = (int) $_SESSION['user_id'];
$eventId = trim($_POST['id'] ?? '');

// Validate the event ID.
if ($eventId === '' || !ctype_digit($eventId)) {

    $_SESSION['flash_message'] = 'Invalid event.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php');
    exit;
}

// Delete the event only if it belongs to the currently logged-in user.
$stmt = $pdo->prepare(
    'DELETE FROM events
     WHERE id = ?
     AND user_id = ?'
);

$stmt->execute([
    (int) $eventId,
    $userId
]);


// Check whether an event was actually deleted.
if ($stmt->rowCount() === 0) {

    $_SESSION['flash_message'] = 'Event not found or you are not allowed to delete it.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php');
    exit;
}

// Delete success.
$_SESSION['flash_message'] = 'Event deleted successfully!';
$_SESSION['flash_type'] = 'success';

header('Location: /events/events.php');
exit;