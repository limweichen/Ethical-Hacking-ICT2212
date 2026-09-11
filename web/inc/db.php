<?php
// Connects to MySQL using the settings in /secrets/app.env.
// After including this file, use $pdo to run queries.
declare(strict_types=1);

$env = parse_ini_file('/secrets/app.env', false, INI_SCANNER_RAW);
if ($env === false) {
    http_response_code(500);
    exit('Settings file missing.');
}

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'],
            $env['DB_PORT'] ?? '3306',
            $env['DB_NAME']
        ),
        $env['DB_USER'],
        $env['DB_PASS'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed.');
}
