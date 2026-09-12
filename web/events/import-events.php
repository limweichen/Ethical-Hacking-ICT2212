<?php
declare(strict_types=1);

require __DIR__ . '/../inc/auth.php';
require_login();

require __DIR__ . '/../inc/db.php';


// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /events/events.php#import-events');
    exit;
}

// Require a valid CSRF token.
require_csrf_token();

// Check that a file was uploaded.
if (
    !isset($_FILES['event_file']) ||
    !is_array($_FILES['event_file']) ||
    $_FILES['event_file']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['flash_message'] = 'Upload failed. Please select a file and try again.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

$file = $_FILES['event_file'];


// Limit the uploaded file size. (1MB)
$maxFileSize = 1024 * 1024;

if (($file['size'] ?? 0) > $maxFileSize) {

    $_SESSION['flash_message'] = 'The JSON file is too large. Maximum size is 1 MB.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}


// Only allow JSON files.
$extension = strtolower(
    pathinfo(
        (string) ($file['name'] ?? ''),
        PATHINFO_EXTENSION
    )
);

if ($extension !== 'json') {

    $_SESSION['flash_message'] = 'Please upload a JSON file.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

// Read the uploaded file.
$content = file_get_contents($file['tmp_name']);

if ($content === false) {

    $_SESSION['flash_message'] = 'Unable to read the uploaded file.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

// Decode JSON.
$importedEvents = json_decode(
    $content,
    true
);

if (
    !is_array($importedEvents) ||
    json_last_error() !== JSON_ERROR_NONE
) {

    $_SESSION['flash_message'] = 'Invalid JSON file. Please check the file format.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

// Allow importing either: A single event object / An array of events
if (isset($importedEvents['name'])) {
    $importedEvents = [$importedEvents];
}

// Limit the number of imported events.
$maxEvents = 100;

if (count($importedEvents) > $maxEvents) {

    $_SESSION['flash_message'] = 'You can import a maximum of 100 events at a time.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}


// Get the currently logged-in user.
$userId = (int) $_SESSION['user_id'];


// Prepare the INSERT statement once.
$stmt = $pdo->prepare(
    'INSERT INTO events
        (user_id, name, date, time, location, description)
     VALUES
        (?, ?, ?, ?, ?, ?)'
);

// Import valid events.
$importedCount = 0;

foreach ($importedEvents as $event) {

    if (!is_array($event)) {
        continue;
    }

    // Read and normalize fields.
    $name = trim(
        (string) ($event['name'] ?? '')
    );

    $date = trim(
        (string) ($event['date'] ?? '')
    );

    $time = trim(
        (string) ($event['time'] ?? '')
    );

    $location = trim(
        (string) ($event['location'] ?? '')
    );

    $description = trim(
        (string) ($event['description'] ?? '')
    );

    // Skip incomplete events.
    if (
        $name === '' ||
        $date === '' ||
        $time === '' ||
        $location === ''
    ) {
        continue;
    }

    // Validate field lengths.
    if (strlen($name) > 150) {
        continue;
    }

    if (strlen($location) > 200) {
        continue;
    }

    if (strlen($description) > 5000) {
        continue;
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
        continue;
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
        continue;
    }

    // Insert the event.
    $stmt->execute([
        $userId,
        $name,
        $date,
        $time,
        $location,
        $description !== '' ? $description : null
    ]);

    $importedCount++;
}

// Make sure at least one valid event was found.
if ($importedCount === 0) {

    $_SESSION['flash_message'] = 'No valid events were found in the file.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}


// Show success notification.
if ($importedCount === 1) {

    $_SESSION['flash_message'] = '1 event imported successfully!';
} else {

    $_SESSION['flash_message'] = $importedCount . ' events imported successfully!';
}

$_SESSION['flash_type'] = 'success';


// Return to My Events.
header('Location: /events/events.php#import-events');
exit;