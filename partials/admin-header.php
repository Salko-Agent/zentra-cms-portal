<?php
// partials/admin-header.php  (outputs <head> + opens body + layout wrapper)
// Requires $page_title to be set before include.
if (!isset($page_title)) $page_title = 'Zentra CMS';
$csrf = csrf_token();
$pid  = $_SESSION['project_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <meta name="csrf" content="<?= htmlspecialchars($csrf) ?>">
  <meta name="project_id" content="<?= (int)$pid ?>">
  <title><?= htmlspecialchars($page_title) ?> – Zentra CMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<?php if (!empty($load_charts)): ?>
  <link rel="stylesheet" href="/assets/css/analytics.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>
  <script src="/assets/js/analytics-charts.js" defer></script>
<?php endif; ?>
</head>
<body>
<div class="admin-layout">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
<header class="admin-topbar">
  <div style="display:flex;align-items:center;gap:12px">
    <button id="sidebarToggle" class="btn btn-secondary btn-sm" style="padding:10px 14px;min-height:44px;line-height:1" aria-label="Menü öffnen"><i data-lucide="menu"></i></button>
    <span class="topbar-title"><?= htmlspecialchars($page_title) ?></span>
  </div>
  <div class="topbar-right">
    <?php $u = current_user(); if ($u): ?>
    <span class="topbar-user">👤 <?= htmlspecialchars($u['name'] ?? '') ?></span>
    <?php endif; ?>
    <a href="/logout.php" class="btn btn-secondary btn-sm">Abmelden</a>
  </div>
</header>
<main class="admin-content">
