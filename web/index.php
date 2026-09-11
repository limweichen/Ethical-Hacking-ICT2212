<?php
require __DIR__ . '/inc/auth.php';

header('Location: ' . (is_logged_in() ? '/dashboard.php' : '/login.php'));
exit;
