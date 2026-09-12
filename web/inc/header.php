<?php
    declare(strict_types=1);

    require __DIR__ . '/db.php';

    $flashMessage = $_SESSION['flash_message'] ?? null;
    $flashType = $_SESSION['flash_type'] ?? 'success';

    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);

    $isLoggedIn = is_logged_in();

    $profile = [
        'name' => $_SESSION['username'] ?? 'User',
        'email' => ''
    ];

    if ($isLoggedIn) {
        $stmt = $pdo->prepare(
            'SELECT username, name, email
            FROM users
            WHERE id = ?'
        );
        $stmt->execute([(int) $_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $profile['name'] = $user['name'];
            $profile['email'] = $user['email'];
        }
    }

    $initial = strtoupper(
        substr(
            $profile['name'] !== '' ? $profile['name'] : 'A',
            0,
            1
        )
    );

    $activePage = $activePage ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e($title ?? 'EventHub') ?>
    </title>

    <link
        rel="stylesheet"
        href="/css/style.css"
    >
</head>

<body>

<header class="topbar">

    <a href="/dashboard.php" class="brand">
        <div class="brand-icon">E</div>
        <span>EventHub</span>
    </a>

    <?php if ($isLoggedIn): ?>

        <nav>
            <a href="/dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>

            <a href="/events/events.php" class="<?= $activePage === 'events' ? 'active' : '' ?>">
                My Events
            </a>

            <a href="/about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">
                About
            </a>
        </nav>

        <div class="account-menu">

            <button
                type="button"
                class="account-button"
                id="accountButton"
                aria-expanded="false"
                aria-controls="accountDropdown"
            >
                <span class="user-name">
                    <?= e($profile['name']) ?>
                </span>

                <div class="avatar">
                    <?= e($initial) ?>
                </div>
            </button>

            <div
                class="account-dropdown"
                id="accountDropdown"
            >

                <div class="account-dropdown-header">

                    <div class="dropdown-avatar">
                        <?= e($initial) ?>
                    </div>

                    <div>
                        <strong>
                            <?= e($profile['name']) ?>
                        </strong>

                        <span>
                            <?= e($profile['email']) ?>
                        </span>
                    </div>

                </div>

                <div class="dropdown-divider"></div>

                <a
                    href="/profile/profile.php"
                    class="dropdown-item"
                >
                    <span class="dropdown-icon">👤</span>
                    <span>Profile</span>
                </a>

                <a
                    href="/logout.php"
                    class="dropdown-item"
                >
                    <span class="dropdown-icon">↪</span>
                    <span>Log out</span>
                </a>

            </div>

        </div>

    <?php else: ?>

        <nav>
            <a href="/about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">
                About
            </a>
        </nav>

        <div class="logged-out-actions">

            <a href="/login.php" class="secondary-button">
                Log in
            </a>

            <a href="/register.php" class="primary-button">
                Sign up
            </a>

        </div>

    <?php endif; ?>

</header>

<main class="container">