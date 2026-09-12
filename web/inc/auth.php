<?php
declare(strict_types=1);

// Start a secure session.
if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);

    session_start();
}

//Escape text before printing it in HTML.
function e(string $text): string
{
    return htmlspecialchars(
        $text,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Check whether the user is logged in.
function is_logged_in(): bool
{
    return isset($_SESSION['user_id'])
        && is_int($_SESSION['user_id']);
}

// Require the user to be logged in.
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

// Get the CSRF token for the current session.
function get_csrf_token(): string
{
    if (
        !isset($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token']) ||
        $_SESSION['csrf_token'] === ''
    ) {
        $_SESSION['csrf_token'] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Verify a submitted CSRF token.
function verify_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals(
            $_SESSION['csrf_token'],
            $token
        );
}

//Require a valid CSRF token on POST requests.
function require_csrf_token(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !verify_csrf_token($token)
    ) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}