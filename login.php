<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

auth_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // IP-based rate limiting via database
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/functions.php';

    // Count failed attempts from this IP in the last 15 minutes
    try {
        $stmt = db()->prepare(
            "SELECT COUNT(*) FROM activity_log
             WHERE action = 'login_failed' AND meta_json LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stmt->execute(['%"ip":"' . addcslashes($ip, '%_\\') . '"%']);
        $recentAttempts = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $recentAttempts = 0;
    }

    if ($recentAttempts >= 10) {
        $error = 'Zu viele Versuche. Bitte warte 15 Minuten.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        if ($email === '' || $password === '') {
            $error = 'E-Mail und Passwort erforderlich.';
        } else {
            if (auth_login($email, $password) === true) {
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                // Log failed attempt with IP
                try {
                    log_activity(0, 'login_failed', 'auth', null, $email, ['ip' => $ip]);
                } catch (Throwable $e) {}
                $error = 'Ungültige Anmeldedaten.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Anmelden – Zentra CMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body, html {
      width: 100%;
      height: 100%;
      background: #000;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e6f1ff;
    }

    /* ── Canvas Lava Background ── */
    .canvas-wrapper {
      position: fixed;
      inset: 0;
      z-index: 1;
      filter: blur(25px) contrast(30);
      background: #000;
    }
    .canvas-wrapper canvas { display: block; }

    /* ── Login Card ── */
    .login-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }
    .login-card {
      background: rgba(2, 12, 27, 0.7);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(10, 25, 47, 0.5);
      border-radius: 28px;
      padding: 45px 35px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
      text-align: center;
      animation: fadeIn 1s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .brand-logo {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #0a192f, #007cf0);
      border-radius: 16px;
      margin: 0 auto 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 20px rgba(0, 162, 255, 0.6);
    }
    h1 {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }
    p.subtitle {
      font-size: 14px;
      color: #8892b0;
      margin-bottom: 35px;
    }

    /* ── Alert ── */
    .alert-err {
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.3);
      color: #fca5a5;
      padding: 11px 14px;
      border-radius: 10px;
      font-size: .83rem;
      margin-bottom: 20px;
      text-align: left;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* ── Form ── */
    .input-group {
      position: relative;
      margin-bottom: 20px;
      text-align: left;
    }
    .input-group label {
      display: block;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #8892b0;
      margin-bottom: 8px;
      margin-left: 4px;
    }
    .input-group input {
      width: 100%;
      padding: 14px 18px;
      background: rgba(10, 25, 47, 0.6);
      border: 2px solid #112240;
      border-radius: 12px;
      color: #fff;
      font-size: 16px;
      font-family: inherit;
      outline: none;
      transition: all .3s ease;
    }
    .input-group input:focus {
      border-color: #007cf0;
      background: rgba(10, 25, 47, 0.9);
      box-shadow: 0 0 15px rgba(0, 162, 255, 0.2);
    }
    .input-group input::placeholder { color: #2A4060; }

    .btn-login {
      width: 100%;
      padding: 16px;
      background: linear-gradient(90deg, #007cf0, #00dfd8);
      background-size: 200% auto;
      border: none;
      border-radius: 12px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: .5s;
      margin-top: 15px;
      box-shadow: 0 10px 20px rgba(0, 124, 240, 0.3);
      letter-spacing: .01em;
    }
    .btn-login:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 15px 25px rgba(0, 124, 240, 0.4);
    }
    .btn-login:active { transform: translateY(0); }

    .login-footer {
      text-align: center;
      margin-top: 24px;
      font-size: .72rem;
      color: #1a2a40;
    }

    @media (max-width: 480px) {
      .login-card { padding: 35px 25px; border-radius: 20px; }
    }
  </style>
</head>
<body>

<div class="canvas-wrapper">
  <canvas id="lavaCanvas"></canvas>
</div>

<div class="login-container">
  <form class="login-card" method="POST" action="/login.php" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

    <div class="brand-logo">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    <h1>Willkommen zurück</h1>
    <p class="subtitle">Melde dich bei Zentra CMS an.</p>

    <?php if ($error): ?>
    <div class="alert-err">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="input-group">
      <label for="email">E-Mail Adresse</label>
      <input type="email" id="email" name="email" required autofocus
             autocomplete="email" placeholder="name@domain.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="input-group">
      <label for="password">Passwort</label>
      <input type="password" id="password" name="password" required
             autocomplete="current-password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
    </div>

    <button type="submit" class="btn-login">Anmelden</button>
    <div class="login-footer">Zentra CMS &middot; Nur f&uuml;r autorisierte Nutzer</div>
  </form>
</div>

<script>
(function() {
  const canvas = document.getElementById('lavaCanvas');
  const ctx = canvas.getContext('2d');
  let width, height;
  let blobs = [];
  const BLOB_COUNT = 12;
  const mouse = { x: -1000, y: -1000 };

  class Blob {
    constructor() {
      this.reset();
    }
    reset() {
      this.x = Math.random() * width;
      this.y = Math.random() * height;
      this.radius = 60 + Math.random() * 120;
      this.vx = (Math.random() - 0.5) * 0.6;
      this.vy = (Math.random() - 0.5) * 0.6;
      this.phase = Math.random() * Math.PI * 2;
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      this.phase += 0.005;
      const pulse = Math.sin(this.phase) * 10;
      const r = this.radius + pulse;

      if (this.x < -r) this.x = width + r;
      if (this.x > width + r) this.x = -r;
      if (this.y < -r) this.y = height + r;
      if (this.y > height + r) this.y = -r;

      const dx = this.x - mouse.x;
      const dy = this.y - mouse.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < 300) {
        const angle = Math.atan2(dy, dx);
        const force = (300 - dist) / 300;
        this.vx += Math.cos(angle) * force * 0.08;
        this.vy += Math.sin(angle) * force * 0.08;
      }

      const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
      if (speed > 1.2) {
        this.vx = (this.vx / speed) * 1.2;
        this.vy = (this.vy / speed) * 1.2;
      }
    }
    draw() {
      const pulse = Math.sin(this.phase) * 10;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.radius + pulse, 0, Math.PI * 2);
      ctx.fillStyle = '#007cf0';
      ctx.fill();
    }
  }

  function resize() {
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width;
    canvas.height = height;
    if (blobs.length === 0) {
      for (let i = 0; i < BLOB_COUNT; i++) blobs.push(new Blob());
    }
  }

  function animate() {
    ctx.fillStyle = '#000';
    ctx.fillRect(0, 0, width, height);
    blobs.forEach(b => { b.update(); b.draw(); });
    requestAnimationFrame(animate);
  }

  window.addEventListener('resize', resize);
  window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
  window.addEventListener('touchmove', e => {
    if (e.touches.length > 0) { mouse.x = e.touches[0].clientX; mouse.y = e.touches[0].clientY; }
  });

  resize();
  animate();
})();
</script>

</body>
</html>
