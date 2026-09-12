<?php
    declare(strict_types=1);

    require __DIR__ . '/../inc/auth.php';
    require_login();

    require __DIR__ . '/../inc/db.php';

    $eventId = $_GET['id'] ?? '';
    $event = null;

    if (is_string($eventId) && ctype_digit($eventId)) {

        $userId = (int) $_SESSION['user_id'];

        $stmt = $pdo->prepare(
            'SELECT id, name, date, time, location, description
             FROM events
             WHERE id = ?
             AND user_id = ?'
        );

        $stmt->execute([
            (int) $eventId,
            $userId
        ]);

        $event = $stmt->fetch();
    }

    $title = 'Edit Event';
    $activePage = 'events';

    require __DIR__ . '/../inc/header.php';
?>

<?php if (!$event): ?>

    <section class="panel">

        <div class="empty-state">

            <div class="empty-icon">
                ⚠️
            </div>

            <h3>
                Event Not Found
            </h3>

            <p>
                The event you are trying to edit does not exist.
            </p>

            <br>

            <a href="/events/events.php" class="primary-button">
                ← Back to Events
            </a>

        </div>

    </section>

<?php else: ?>

    <div class="breadcrumb">

        <a href="/events/event-detail.php?id=<?= (int) $event['id'] ?>">
            ← Back to Event
        </a>

    </div>

    <section class="panel form-section">

        <div class="form-heading">

            <p class="eyebrow">
                EDIT EVENT
            </p>

            <h1>
                Edit Event
            </h1>

            <p class="subtitle">
                Update the details of your event.
            </p>

        </div>

        <form
            action="/events/save-edit-event.php"
            method="POST"
            class="event-form"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(get_csrf_token()) ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int) $event['id'] ?>"
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
                        value="<?= e($event['name']) ?>"
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
                        value="<?= e($event['location']) ?>"
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
                        value="<?= e($event['date']) ?>"
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
                        value="<?= e(substr($event['time'], 0, 5)) ?>"
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
                ><?= e($event['description'] ?? '') ?></textarea>

            </div>

            <div class="form-actions">

                <a
                    href="/events/event-detail.php?id=<?= (int) $event['id'] ?>"
                    class="secondary-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </section>

<?php endif; ?>

<?php require __DIR__ . '/../inc/footer.php'; ?>