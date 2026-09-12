<?php
    declare(strict_types=1);

    require __DIR__ . '/../inc/auth.php';
    require_login();

    require __DIR__ . '/../inc/db.php';

    $userId = (int) $_SESSION['user_id'];

    // Get all events belonging to the currently logged-in user.
    $stmt = $pdo->prepare(
        'SELECT id, name, date, time, location, description
         FROM events
         WHERE user_id = ?
         ORDER BY date ASC, time ASC'
    );

    $stmt->execute([$userId]);
    $events = $stmt->fetchAll();

    $title = 'My Events';
    $activePage = 'events';

    require __DIR__ . '/../inc/header.php';
?>

//PAGE HEADER
<section class="welcome">

    <div>

        <p class="eyebrow">
            MY EVENTS
        </p>

        <h1>
            My Events
        </h1>

        <p class="subtitle">
            Create and manage your personal events.
        </p>

    </div>

    <a href="#create-event" class="primary-button">
        + Create Event
    </a>

</section>

// EVENTS
<section class="panel">

    <div class="panel-header">

        <div>

            <h2>
                Your Events
            </h2>

            <p>
                <?= count($events) ?>
                event<?= count($events) === 1 ? '' : 's' ?>
            </p>

        </div>

    </div>

    <?php if (empty($events)): ?>

        <div class="empty-state">

            <div class="empty-icon">
                📅
            </div>

            <h3>
                No events yet
            </h3>

            <p>
                Create your first event using the form below.
            </p>

        </div>

    <?php else: ?>

        <div class="event-list">

            <?php foreach ($events as $event): ?>

                <?php
                    $eventDate = $event['date'];
                    $eventTime = $event['time'];

                    $timestamp = strtotime(
                        $eventDate . ' ' . $eventTime
                    );

                    if ($timestamp === false) {
                        continue;
                    }

                    $eventId = (int) $event['id'];

                    $isUpcoming =
                        $eventDate >= date('Y-m-d');
                ?>

                <a href="/events/event-detail.php?id=<?= $eventId ?>" class="event-row">

                    <div class="date-box">

                        <strong>
                            <?= e(date('d', $timestamp)) ?>
                        </strong>

                        <span>
                            <?= e(date('M', $timestamp)) ?>
                        </span>

                    </div>

                    <div class="event-info">

                        <h3>
                            <?= e($event['name']) ?>
                        </h3>

                        <p>

                            <?= e(
                                date(
                                    'l, d M Y',
                                    $timestamp
                                )
                            ) ?>

                            ·

                            <?= e(
                                date(
                                    'h:i A',
                                    $timestamp
                                )
                            ) ?>

                            ·

                            <?= e($event['location']) ?>

                        </p>

                    </div>

                    <span
                        class="status <?= $isUpcoming
                            ? 'upcoming'
                            : 'past' ?>"
                    >
                        <?= $isUpcoming ? 'Upcoming' : 'Past' ?>
                    </span>


                    <div class="event-arrow">
                        →
                    </div>

                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

// CREATE EVENT
<section
    class="panel form-section"
    id="create-event"
>
    <div class="form-heading">

        <p class="eyebrow">
            NEW EVENT
        </p>

        <h2>
            Create Event
        </h2>

        <p class="subtitle">
            Add a new event to your personal event list.
        </p>

    </div>

    <form
        action="/events/save_event.php"
        method="POST"
        class="event-form"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(get_csrf_token()) ?>"
        >

        <div class="form-row">

            <div class="form-group">

                <label for="name">
                    Event Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="e.g. Birthday Dinner"
                    maxlength="150"
                    required
                >

            </div>

            <div class="form-group">

                <label for="location">
                    Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    placeholder="e.g. Marina Bay"
                    maxlength="200"
                    required
                >

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label for="date">
                    Date
                </label>

                <input
                    type="date"
                    id="date"
                    name="date"
                    required
                >

            </div>

            <div class="form-group">

                <label for="time">
                    Time
                </label>

                <input
                    type="time"
                    id="time"
                    name="time"
                    required
                >

            </div>

        </div>

        <div class="form-group">

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
                maxlength="5000"
                placeholder="Add some details about your event..."
            ></textarea>

        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="primary-button"
            >
                Create Event
            </button>

        </div>

    </form>

</section>

// IMPORT EVENTS
<section
    class="panel form-section"
    id="import-events"
>
    <div class="form-heading">

        <p class="eyebrow">
            IMPORT
        </p>

        <h2>
            Import Events
        </h2>

        <p class="subtitle">
            Import event information from a JSON file.
        </p>

    </div>

    <form
        action="/events/import-events.php"
        method="POST"
        enctype="multipart/form-data"
        class="event-form"
    >
        <input
        type="hidden"
        name="csrf_token"
        value="<?= e(get_csrf_token()) ?>"
        >

        <div class="form-group">

            <label for="event_file">
                Event File
            </label>

            <input
                type="file"
                id="event_file"
                name="event_file"
                accept=".json,application/json"
                required
            >

            <small class="form-help">
                Upload a JSON file containing one or more events.
            </small>

        </div>


        <div class="form-actions">

            <button
                type="submit"
                class="primary-button"
            >
                Import Events
            </button>

        </div>

    </form>

</section>

<?php require __DIR__ . '/../inc/footer.php'; ?>