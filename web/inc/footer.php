</main>

<?php if ($flashMessage !== null): ?>

    <div
        class="toast-notification <?= ($flashType ?? 'success') === 'error'
            ? 'toast-error'
            : '' ?>"
        id="toastNotification"
    >
        <span class="toast-icon">✓</span>

        <span class="toast-message">
            <?= e($flashMessage) ?>
        </span>

        <button
            type="button"
            class="toast-close"
            onclick="closeToast()"
            aria-label="Close notification"
        >
            ×
        </button>
    </div>

<?php endif; ?>

<footer>
    <p>
        © ICT2212 Group 17 · Personal Event Planning & Organisation
    </p>
</footer>

<script>
const accountButton = document.getElementById('accountButton');
const accountDropdown = document.getElementById('accountDropdown');

if (accountButton && accountDropdown) {

    accountButton.addEventListener('click', function (event) {
        event.stopPropagation();

        const isOpen =
            accountDropdown.classList.contains('show');

        accountDropdown.classList.toggle('show');
        accountButton.setAttribute('aria-expanded', String(!isOpen));
    });

    document.addEventListener('click', function () {
        accountDropdown.classList.remove('show');

        accountButton.setAttribute(
            'aria-expanded',
            'false'
        );
    });

    accountDropdown.addEventListener('click', function (event) {
        event.stopPropagation();
    });
}

function closeToast() {

    const toast =
        document.getElementById('toastNotification');

    if (toast) {

        toast.classList.add('hide');

        setTimeout(function () {
            toast.remove();
        }, 300);
    }
}

const toast =
    document.getElementById('toastNotification');

if (toast) {

    setTimeout(function () {
        closeToast();
    }, 3500);
}
</script>

</body>
</html>