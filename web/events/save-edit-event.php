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

// Require a valid CSRF token
require_csrf_token();

$userId = (int) $_SESSION['user_id'];

$id = trim($_POST['id'] ?? '');
$name = trim($_POST['name'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

// Basic validation.
if (
    $id === '' ||
    !ctype_digit($id) ||
    $name === '' ||
    $date === '' ||
    $time === '' ||
    $location === ''
) {
    $_SESSION['flash_message'] = 'Please complete all required fields.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

// Validate field lengths.
if (strlen($name) > 150) {

    $_SESSION['flash_message'] = 'Event name is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

if (strlen($location) > 200) {

    $_SESSION['flash_message'] = 'Location is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

if (strlen($description) > 5000) {

    $_SESSION['flash_message'] = 'Description is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

// Validate date.
$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $date
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $date
) {

    $_SESSION['flash_message'] = 'Please enter a valid date.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

// Validate time.
$timeObject = DateTime::createFromFormat(
    'H:i',
    $time
);

if (
    !$timeObject ||
    $timeObject->format('H:i') !== $time
) {

    $_SESSION['flash_message'] = 'Please enter a valid time.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/edit-event.php?id=' . urlencode($id));
    exit;
}

// Update the event.
$stmt = $pdo->prepare(
    'UPDATE events
     SET
        name = ?,
        date = ?,
        time = ?,
        location = ?,
        description = ?
     WHERE id = ?
       AND user_id = ?'
);

$stmt->execute([
    $name,
    $date,
    $time,
    $location,
    $description !== '' ? $description : null,
    (int) $id,
    $userId
]);

// Check whether an event belonging to this user actually existed.
if ($stmt->rowCount() === 0) {

    // Check whether the event exists before deciding it was not found.
    $checkStmt = $pdo->prepare(
        'SELECT id
         FROM events
         WHERE id = ?
         AND user_id = ?'
    );

    $checkStmt->execute([
        (int) $id,
        $userId
    ]);

    if (!$checkStmt->fetch()) {

        $_SESSION['flash_message'] = 'Event not found or you are not allowed to edit it.';
        $_SESSION['flash_type'] = 'error';

        header('Location: /events/events.php');
        exit;
    }
}

// Show success message.
$_SESSION['flash_message'] = 'Event updated successfully!';
$_SESSION['flash_type'] = 'success';

// Return to the updated event.
header('Location: /events/event-detail.php?id=' . urlencode($id));
exit;