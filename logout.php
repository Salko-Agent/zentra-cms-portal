<?php
// logout.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

auth_start();

// Log before destroying session
if (!empty($_SESSION['user_id'])) {
    log_activity((int)$_SESSION['project_id'], 'user_logout', 'user', (int)$_SESSION['user_id']);
}

auth_logout();
header('Location: /login.php');
exit;
