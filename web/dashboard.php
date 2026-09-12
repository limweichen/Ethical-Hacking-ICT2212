<?php
declare(strict_types=1);

require __DIR__ . '/inc/auth.php';
require_login();
require __DIR__ . '/inc/db.php';

// Get the currently logged-in user's information.
$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT name
     FROM users
     WHERE id = ?'
);
$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    header('Location: /logout.php');
    exit;
}

$userName = $user['name'];


// Get all events belonging to this user.
$stmt = $pdo->prepare(
    'SELECT id, name, date, time, location, description
     FROM events
     WHERE user_id = ?
     ORDER BY date ASC, time ASC'
);
$stmt->execute([$userId]);
$events = $stmt->fetchAll();


// Calculate dashboard statistics.
$totalEvents = count($events);

$today = date('Y-m-d');
$currentMonth = date('Y-m');

$upcomingEvents = 0;
$eventsThisMonth = 0;

foreach ($events as $event) {

    $eventDate = $event['date'];

    if ($eventDate >= $today) {
        $upcomingEvents++;
    }

    if (strpos($eventDate, $currentMonth) === 0) {
        $eventsThisMonth++;
    }
}

// Get the first 3 upcoming events.
$upcomingList = [];

foreach ($events as $event) {

    if ($event['date'] >= $today) {

        $upcomingList[] = $event;

        if (count($upcomingList) >= 3) {
            break;
        }
    }
}

//Page settings.
$title = 'Dashboard';
$activePage = 'dashboard';

require __DIR__ . '/inc/header.php';
?>

<section class="welcome">

    <div>

        <p class="eyebrow">
            EVENT PLANNING
        </p>

        <h1>
            Welcome back, <?= e($userName) ?>.
        </h1>

        <p class="subtitle">
            Keep your personal events organised and easy to manage.
        </p>

    </div>

    <a href="/events/events.php#create-event" class="primary-button">
        + Create Event
    </a>

</section>

<section class="stats">

    <div class="stat-card">

        <div class="stat-icon">
            ◫
        </div>

        <div>

            <p>
                Total Events
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

    <div class="stat-card">

        <div class="stat-icon">
            ▣
        </div>

        <div>

            <p>
                Events This Month
            </p>

            <h2>
                <?= $eventsThisMonth ?>
            </h2>

        </div>

    </div>

</section>


<section class="content-grid">

    <!-- Upcoming Events -->
    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Upcoming Events
                </h2>

                <p>
                    Your next scheduled events
                </p>

            </div>

            <a href="/events/events.php">
                View all
            </a>

        </div>

        <div class="event-list">

            <?php if (empty($upcomingList)): ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        ◫
                    </div>

                    <h3>
                        No upcoming events
                    </h3>

                    <p>
                        Create an event to get started.
                    </p>

                </div>

            <?php else: ?>

                <?php foreach ($upcomingList as $event): ?>

                    <a href="/events/event-detail.php?id=<?= (int) $event['id'] ?>" class="event-row">

                        <div class="date-box">

                            <strong>
                                <?= e(
                                    date(
                                        'd',
                                        strtotime($event['date'])
                                    )
                                ) ?>
                            </strong>

                            <span>
                                <?= e(
                                    strtoupper(
                                        date(
                                            'M',
                                            strtotime($event['date'])
                                        )
                                    )
                                ) ?>
                            </span>

                        </div>

                        <div class="event-info">

                            <h3>
                                <?= e($event['name']) ?>
                            </h3>

                            <p>
                                <?= e($event['location']) ?>
                                ·
                                <?= e(
                                    date(
                                        'g:i A',
                                        strtotime($event['time'])
                                    )
                                ) ?>
                            </p>

                        </div>

                        <span class="status upcoming">
                            Upcoming
                        </span>

                        <span class="event-arrow">
                            →
                        </span>

                    </a>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>


    <!-- Quick Actions -->
    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Quick Actions
                </h2>

                <p>
                    Manage your events
                </p>

            </div>

        </div>

        <div class="quick-actions">

            <a href="/events/events.php#create-event" class="quick-action">

                <span class="quick-action-icon">
                    +
                </span>

                <div>

                    <strong>
                        Create Event
                    </strong>

                    <p>
                        Add a new event
                    </p>

                </div>

            </a>

            <a href="/events/events.php#import-events" class="quick-action">

                <span class="quick-action-icon">
                    ↑
                </span>

                <div>

                    <strong>
                        Import Events
                    </strong>

                    <p>
                        Import event data
                    </p>

                </div>

            </a>

            <a href="/events/events.php" class="quick-action">

                <span class="quick-action-icon">
                    ◫
                </span>

                <div>

                    <strong>
                        View Events
                    </strong>

                    <p>
                        Browse all events
                    </p>

                </div>

            </a>

        </div>

    </div>

</section>

<?php require __DIR__ . '/inc/footer.php'; ?>