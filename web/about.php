<?php
    declare(strict_types=1);

    require __DIR__ . '/inc/auth.php';

    $title = 'About';
    $activePage = 'about';

    require __DIR__ . '/inc/header.php';
?>

<section class="welcome">

    <div>

        <p class="eyebrow">
            ABOUT EVENTHUB
        </p>

        <h1>
            Plan your events, your way.
        </h1>

        <p class="subtitle">
            EventHub is a personal event planning and organisation
            application designed to help you keep track of the
            events that matter to you.
        </p>

    </div>

</section>

<section class="profile-grid">

    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    What is EventHub?
                </h2>

                <p>
                    A simple space for managing your personal events.
                </p>

            </div>

        </div>

        <p>
            EventHub lets you create, organise, update and remove
            your events from one place. You can also import events
            from a JSON file when you already have event information
            prepared.
        </p>

    </div>

    <div class="panel">

        <div class="panel-header">

            <div>

                <h2>
                    Features
                </h2>

                <p>
                    Everything you need for basic event organisation.
                </p>

            </div>

        </div>

        <ul class="about-list">

            <li>
                Create and manage personal events
            </li>

            <li>
                View upcoming events
            </li>

            <li>
                Edit or delete your events
            </li>

            <li>
                Import events using JSON
            </li>

            <li>
                Manage your personal profile
            </li>

        </ul>

    </div>

</section>

<?php require __DIR__ . '/inc/footer.php'; ?>