<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_check();

if (empty($_SESSION['is_super_admin'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

// Require POST + CSRF to prevent CSRF-based project switching
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard.php');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare('SELECT id FROM projects WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    if ($stmt->fetch()) {
        log_activity((int)$_SESSION['project_id'], 'project_switched', 'project', $id);
        $_SESSION['project_id'] = $id;
    }
}

header('Location: /admin/dashboard.php');
exit;
