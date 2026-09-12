<?php
    declare(strict_types=1);

    require __DIR__ . '/../inc/auth.php';
    require_login();

    require __DIR__ . '/../inc/db.php';

    // Get the requested event ID.
    $eventId = $_GET['id'] ?? '';

    if (!is_string($eventId) || !ctype_digit($eventId)) {
        $eventId = '';
    }

    // Find the event belonging to the currently logged-in user.
    $event = null;

    if ($eventId !== '') {

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

    // Page title.
    $title = $event !== false && $event !== null
        ? $event['name']
        : 'Event Not Found';

    $activePage = 'events';

    require __DIR__ . '/../inc/header.php';
?>

<?php if (!$event): ?>

    <!-- EVENT NOT FOUND -->
    <div class="page-header">

        <div>

            <h1>
                Event Not Found
            </h1>

            <p class="event-not-found-message">
                The event does not exist or you do not have access to it.
            </p>

        </div>

    </div>

    <div class="empty-state">

        <h2>
            Event not found
        </h2>

        <p class="event-not-found-message">
            This event may have been deleted, or it may belong to another account.
        </p>

        <div class="event-not-found-actions">

            <a href="/events/events.php" class="btn btn-primary">
                ← Back to My Events
            </a>

        </div>

    </div>

<?php else: ?>

    <?php
    // Safely prepare event values.
    $eventId = (int) $event['id'];
    $eventName = (string) ($event['name'] ?? 'Untitled Event');
    $eventDate = (string) ($event['date'] ?? '');
    $eventTime = (string) ($event['time'] ?? '');
    $eventLocation = (string) ($event['location'] ?? '');
    $eventDescription = (string) ($event['description'] ?? '');

    // Determine whether the event is upcoming or past.
    $timestamp = strtotime(
        $eventDate . ' ' . $eventTime
    );

    $isUpcoming =
        $timestamp !== false &&
        $timestamp >= time();
    ?>

    <!-- PAGE HEADER -->
    <div class="page-header">

        <div>

            <a href="/events/events.php" class="back-link">
                ← Back to My Events
            </a>

            <h1>
                <?= e($eventName) ?>
            </h1>

            <p>
                Event details
            </p>

        </div>

    </div>

    <!-- EVENT DETAILS -->
    <div class="event-detail-card">

        <div class="event-detail-status">

            <span
                class="event-status <?= $isUpcoming
                    ? 'upcoming'
                    : 'past' ?>"
            >
                <?= $isUpcoming ? 'Upcoming' : 'Past' ?>
            </span>

        </div>

        <div class="event-detail-info">

            <div class="detail-item">

                <span class="detail-label">
                    Date
                </span>

                <span class="detail-value">

                    <?= e(
                        date(
                            'l, d M Y',
                            $timestamp !== false
                                ? $timestamp
                                : strtotime($eventDate)
                        )
                    ) ?>

                </span>

            </div>

            <div class="detail-item">

                <span class="detail-label">
                    Time
                </span>

                <span class="detail-value">

                    <?= e(
                        $timestamp !== false
                            ? date('h:i A', $timestamp)
                            : $eventTime
                    ) ?>

                </span>

            </div>

            <div class="detail-item">

                <span class="detail-label">
                    Location
                </span>

                <span class="detail-value">
                    <?= e($eventLocation) ?>
                </span>

            </div>

        </div>

        <!-- DESCRIPTION -->
        <div class="event-description">

            <h3>
                Description
            </h3>

            <?php if ($eventDescription !== ''): ?>

                <p>
                    <?= nl2br(e($eventDescription)) ?>
                </p>

            <?php else: ?>

                <p>
                    No description provided.
                </p>

            <?php endif; ?>

        </div>

        <!-- ACTIONS -->
        <div class="event-detail-actions">

            <a href="/events/edit-event.php?id=<?= $eventId ?>" class="btn btn-secondary">
                Edit
            </a>

            <button
                type="button"
                class="btn btn-danger"
                onclick="openDeleteModal()"
            >
                Delete
            </button>

        </div>

    </div>

    <!-- DELETE FORM -->
    <form
        method="POST"
        action="/events/delete-event.php"
        id="deleteEventForm"
        onsubmit="return false;"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(get_csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="id"
            value="<?= $eventId ?>"
        >

        <!-- DELETE MODAL -->
        <div
            class="modal-overlay"
            id="deleteModal"
            aria-hidden="true"
        >

            <div
                class="modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="deleteModalTitle"
            >

                <button
                    type="button"
                    class="modal-close"
                    onclick="closeDeleteModal()"
                    aria-label="Close"
                >
                    ×
                </button>

                <h2 id="deleteModalTitle">
                    Delete Event?
                </h2>

                <p>
                    Are you sure you want to delete this event?
                    This action cannot be undone.
                </p>

                <div class="modal-actions">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="closeDeleteModal()"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="confirmDelete()"
                    >
                        Delete Event
                    </button>

                </div>

            </div>

        </div>

    </form>

<?php endif; ?>

<script>
function openDeleteModal() {

    const modal = document.getElementById('deleteModal');

    if (!modal) {
        return;
    }

    modal.classList.add('show');
    modal.setAttribute('aria-hidden','false');
}

function closeDeleteModal() {

    const modal = document.getElementById('deleteModal');

    if (!modal) {
        return;
    }

    modal.classList.remove('show');
    modal.setAttribute('aria-hidden','true');
}

function confirmDelete() {

    const form = document.getElementById('deleteEventForm');

    if (!form) {
        return;
    }

    // Allow the form to submit normally.
    form.onsubmit = null;
    form.submit();
}

// Close the modal when clicking outside the modal itself.
const deleteModal = document.getElementById('deleteModal');

if (deleteModal) {

    deleteModal.addEventListener(
        'click',
        function (event) {

            if (event.target === deleteModal) {
                closeDeleteModal();
            }

        }
    );
}

// Close the modal with Escape.
document.addEventListener(
    'keydown',
    function (event) {

        if (event.key === 'Escape') {
            closeDeleteModal();
        }

    }
);
</script>

<?php require __DIR__ . '/../inc/footer.php'; ?>