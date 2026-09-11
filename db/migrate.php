<?php
// Runs when the web container starts: waits for MySQL, then applies schema.sql.
declare(strict_types=1);

$env = parse_ini_file('/secrets/app.env', false, INI_SCANNER_RAW);
if ($env === false) {
    fwrite(STDERR, "ERROR: /secrets/app.env is missing\n");
    exit(1);
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $env['DB_HOST'],
    $env['DB_PORT'] ?? '3306',
    $env['DB_NAME']
);

// MySQL can take a while on first start, so retry for up to ~60 seconds
for ($attempt = 1; ; $attempt++) {
    try {
        $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        break;
    } catch (PDOException $e) {
        if ($attempt >= 30) {
            fwrite(STDERR, "ERROR: cannot connect to database: {$e->getMessage()}\n");
            exit(1);
        }
        echo "Waiting for database ({$attempt}/30)...\n";
        sleep(2);
    }
}

$pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
echo "Database tables ready.\n";
