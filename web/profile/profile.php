<?php
    declare(strict_types=1);

    require __DIR__ . '/../inc/auth.php';
    require_login();

    require __DIR__ . '/../inc/db.php';

    // Get the currently logged-in user.
    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare(
        'SELECT id, username, name, email
        FROM users
        WHERE id = ?'
    );

    $stmt->execute([$userId]);
    $user = $stmt->fetch();


    // If the account no longer exists, log the user out.
    if (!$user) {
        header('Location: /logout.php');
        exit;
    }


    // Profile information.
    $profile = [
        'name' => $user['name'],
        'email' => $user['email']
    ];

    // Get event statistics for this user only.
    $stmt = $pdo->prepare(
        'SELECT
            COUNT(*) AS total_events,
            SUM(
                CASE
                    WHEN date >= CURDATE() THEN 1
                    ELSE 0
                END
            ) AS upcoming_events
        FROM events
        WHERE user_id = ?'
    );

    $stmt->execute([$userId]);
    $eventStats = $stmt->fetch();

    $totalEvents = (int) ($eventStats['total_events'] ?? 0);
    $upcomingEvents = (int) ($eventStats['upcoming_events'] ?? 0);

    // Shared page settings.
    $title = 'Profile';
    $activePage = 'profile';

    // Use shared header.
    require __DIR__ . '/../inc/header.php';
?>

// PROFILE HEADER
<section class="welcome">

    <div>

        <p class="eyebrow">
            MY ACCOUNT
        </p>

        <h1>
            My Profile
        </h1>

        <p class="subtitle">
            Manage your personal information and event preferences.
        </p>

    </div>

</section>


// PROFILE LAYOUT

<section class="profile-grid">

    // Profile summary
    <div class="panel profile-card">

        <div class="profile-avatar">
            <?= e($initial) ?>
        </div>

        <h2>
            <?= e($profile['name']) ?>
        </h2>

        <p class="profile-role">
            Personal Account
        </p>

        <p class="profile-description">
            Your personal EventHub profile for organising and keeping track of your events.
        </p>

    </div>

    // Personal information
    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Personal Information
                </h2>

                <p>
                    Update your basic account information.
                </p>

            </div>

        </div>

        <form
            action="/profile/save_profile.php"
            method="POST"
            class="profile-form"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(get_csrf_token()) ?>"
            >

            <div class="form-group">

                <label for="name">
                    Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($profile['name']) ?>"
                    maxlength="100"
                    autocomplete="name"
                    required
                >

            </div>

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($profile['email']) ?>"
                    maxlength="255"
                    autocomplete="email"
                    required
                >

            </div>

            <div class="form-actions">

                <button
                    type="submit"
                    class="primary-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</section>

// SECURITY
<section class="panel profile-security">

    <div class="panel-header">

        <div>

            <h2>
                Security
            </h2>

            <p>
                Change your account password.
            </p>

        </div>

    </div>

    <form
        action="/profile/change_password.php"
        method="POST"
        class="profile-form"
    >
        <input
        type="hidden"
        name="csrf_token"
        value="<?= e(get_csrf_token()) ?>"
        >

        <div class="form-group">

            <label for="current_password">
                Current Password
            </label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                autocomplete="current-password"
                required
            >

        </div>

        <div class="form-group">

            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
                required
            >

            <small>
                Use at least 8 characters.
            </small>

        </div>

        <div class="form-group">

            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
                required
            >

        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="primary-button"
            >
                Change Password
            </button>

        </div>

    </form>

</section>

// EVENT OVERVIEW
<section class="stats profile-stats">

    <div class="stat-card">

        <div class="stat-icon">
            ◫
        </div>

        <div>

            <p>
                Events Created
            </p>

            <h2>
                <?= $totalEvents ?>
            </h2>

        </div>

    </div>

    <div class="stat-card">

        <div class="stat-icon">
            ◷
        </div>

        <div>

            <p>
                Upcoming Events
            </p>

            <h2>
                <?= $upcomingEvents ?>
            </h2>

        </div>

    </div>

</section>

// Use shared footer.
<?php require __DIR__ . '/../inc/footer.php'; ?>