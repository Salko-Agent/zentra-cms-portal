<?php
// admin/password.php — change password
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $current  = $_POST['current']  ?? '';
    $new_pw   = $_POST['new']      ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if ($new_pw === '') {
        $error = 'Neues Passwort darf nicht leer sein.';
    } elseif (strlen($new_pw) < 8) {
        $error = 'Passwort muss mindestens 8 Zeichen haben.';
    } elseif ($new_pw !== $confirm) {
        $error = 'Passwörter stimmen nicht überein.';
    } else {
        $db   = db();
        $stmt = $db->prepare('SELECT pw_hash FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['pw_hash'])) {
            $error = 'Aktuelles Passwort ist falsch.';
        } else {
            $hash = password_hash($new_pw, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare('UPDATE users SET pw_hash = ? WHERE id = ?')
               ->execute([$hash, $_SESSION['user_id']]);
            $success = 'Passwort erfolgreich geändert.';
            log_activity(current_project_id(), 'password_changed', 'user', (int)$_SESSION['user_id'], $_SESSION['user_name'] ?? '');
        }
    }
}

$page_title = 'Passwort ändern';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Passwort ändern</h1>
    <p>Ändere dein Anmeldepasswort</p>
  </div>
</div>

<?php if ($success): ?>
<div class="alert alert-ok"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px">
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="form-group">
      <label for="current">Aktuelles Passwort</label>
      <input type="password" id="current" name="current" required autocomplete="current-password">
    </div>

    <div class="form-group">
      <label for="new">Neues Passwort</label>
      <input type="password" id="new" name="new" required autocomplete="new-password" minlength="8">
      <div class="form-hint">Mindestens 8 Zeichen</div>
    </div>

    <div class="form-group">
      <label for="confirm">Neues Passwort bestätigen</label>
      <input type="password" id="confirm" name="confirm" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary">Passwort ändern</button>
  </form>
</div>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>
