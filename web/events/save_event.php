<?php
declare(strict_types=1);

require __DIR__ . '/../inc/auth.php';
require_login();

require __DIR__ . '/../inc/db.php';

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /events/events.php');
    exit;
}

// Require a valid CSRF token for this request.
require_csrf_token();

// Get the currently logged-in user's ID.
$userId = (int) $_SESSION['user_id'];

// Get submitted form values.
$name = trim($_POST['name'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

// Basic validation.
if (
    $name === '' ||
    $date === '' ||
    $time === '' ||
    $location === ''
) {

    $_SESSION['flash_message'] = 'Please complete all required fields.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

// Validate field lengths.
if (strlen($name) > 150) {

    $_SESSION['flash_message'] = 'Event name is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

if (strlen($location) > 200) {

    $_SESSION['flash_message'] = 'Location is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

if (strlen($description) > 5000) {

    $_SESSION['flash_message'] = 'Description is too long.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

// VaLidate date.
$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $date
);

$dateErrors = DateTime::getLastErrors();

if ($dateErrors === false) {
    $dateErrors = [
        'warning_count' => 0,
        'error_count' => 0
    ];
}

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $date ||
    $dateErrors['warning_count'] > 0 ||
    $dateErrors['error_count'] > 0
) {

    $_SESSION['flash_message'] = 'Please enter a valid date.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

// Validate time.
$timeObject = DateTime::createFromFormat(
    'H:i',
    $time
);

$timeErrors = DateTime::getLastErrors();

if ($timeErrors === false) {
    $timeErrors = [
        'warning_count' => 0,
        'error_count' => 0
    ];
}

if (
    !$timeObject ||
    $timeObject->format('H:i') !== $time ||
    $timeErrors['warning_count'] > 0 ||
    $timeErrors['error_count'] > 0
) {

    $_SESSION['flash_message'] = 'Please enter a valid time.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

// Insert the event into MySQL.
try {

    $stmt = $pdo->prepare(
        'INSERT INTO events
            (user_id, name, date, time, location, description)
         VALUES
            (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $userId,
        $name,
        $date,
        $time,
        $location,
        $description !== '' ? $description : null
    ]);

} catch (PDOException $e) {

    $_SESSION['flash_message'] = 'Unable to save the event. Please try again.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#create-event');
    exit;
}

// Success.
$_SESSION['flash_message'] = 'Event created successfully!';
$_SESSION['flash_type'] = 'success';

header('Location: /events/events.php');
exit;