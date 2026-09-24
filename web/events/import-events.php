<?php
declare(strict_types=1);

require __DIR__ . '/../inc/auth.php';
require_login();


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
    $_SESSION['flash_message'] = 'The file is too large. Maximum size is 1 MB.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

// Store uploaded files on the server.
$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Keep the original filename for the vulnerability demonstration.
$filename = basename((string) ($file['name'] ?? ''));

if ($filename === '') {
    $_SESSION['flash_message'] = 'Invalid filename.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

$destination = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['flash_message'] = 'Unable to save the uploaded file.';
    $_SESSION['flash_type'] = 'error';

    header('Location: /events/events.php#import-events');
    exit;
}

$_SESSION['flash_message'] = 'File uploaded successfully!';
$_SESSION['flash_type'] = 'success';

header('Location: /events/events.php#import-events');
exit;