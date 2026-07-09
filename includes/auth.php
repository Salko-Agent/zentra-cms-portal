<?php
// ============================================================
// Zentra – Authentication helpers
// ============================================================

function auth_start(): void {
    if (!defined('APP_SECRET')) {
        require_once dirname(__DIR__) . '/config.php';
    }
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        if (APP_ENV === 'production') {
            ini_set('session.cookie_secure', '1');
        }
        session_name(SESSION_NAME);
        session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'path' => '/']);
        session_start();
    }
}

function auth_check(): void {
    auth_start();
    if (empty($_SESSION['user_id'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/admin/dashboard.php');
        header('Location: /login.php?redirect=' . $redirect);
        exit;
    }
    // Regenerate session ID periodically to prevent fixation
    if (empty($_SESSION['_last_regen']) || time() - $_SESSION['_last_regen'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_last_regen'] = time();
    }
}

function auth_login(string $email, string $password): bool {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT id, project_id, name, pw_hash, role, is_super_admin FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['pw_hash'])) {
        return false;
    }
    auth_start();
    session_regenerate_id(true);
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['project_id']     = (int)$user['project_id'];
    $_SESSION['user_name']      = $user['name'];
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['is_super_admin'] = !empty($user['is_super_admin']);
    $_SESSION['_last_regen']    = time();

    // Update last_login timestamp
    try {
        db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')
            ->execute([$user['id']]);
    } catch (Throwable $e) {
        error_log('last_login update failed: ' . $e->getMessage());
    }

    // Log the login
    require_once __DIR__ . '/functions.php';
    log_activity((int)$user['project_id'], 'user_login', 'user', (int)$user['id'], $user['name'], [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
    ]);

    return true;
}

function auth_logout(): void {
    auth_start();
    $_SESSION = [];
    // Expire the session cookie immediately so the browser discards it
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 86400, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_project_id(): int {
    return (int)($_SESSION['project_id'] ?? 0);
}

function current_user(): array {
    return [
        'id'   => $_SESSION['user_id']   ?? 0,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
    ];
}

// CSRF token helpers
function csrf_token(): string {
    auth_start();
    // Rotate token every 30 minutes
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_generated'])
        || time() - $_SESSION['csrf_generated'] > 1800) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_generated'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('CSRF token mismatch');
    }
}
