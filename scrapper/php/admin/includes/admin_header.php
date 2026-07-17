<?php
/**
 * admin/includes/admin_header.php
 * Shared header + sidebar for all admin pages.
 *
 * Required before include:
 *   $adminTitle   string  — page heading
 *   $adminActive  string  — 'dashboard'|'products'|'categories'|'contacts'
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
requireAdmin();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($adminTitle ?? 'Dashboard') ?> — <?= SITE_NAME ?> Admin</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/style.css" />
  <style>
    /* ── Admin Layout ─────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-main); background: #f0f2f5; display: flex; min-height: 100vh; }

    .admin-sidebar {
      width: 240px;
      background: var(--primary-dark);
      min-height: 100vh;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      z-index: 100;
    }

    .sidebar-brand {
      padding: 24px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-brand .logo { font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: -1px; }
    .sidebar-brand .logo span { color: var(--accent); }
    .sidebar-brand p { font-size: 0.72rem; color: rgba(255,255,255,0.4); margin-top: 3px; text-transform: uppercase; letter-spacing: 1px; }

    .sidebar-nav { padding: 16px 12px; flex: 1; }
    .sidebar-nav p.nav-section { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.3); padding: 8px 10px 6px; }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: var(--radius-sm);
      color: rgba(255,255,255,0.65);
      font-size: 0.88rem;
      font-weight: 500;
      text-decoration: none;
      margin-bottom: 3px;
      transition: all 0.2s;
    }

    .sidebar-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
    .sidebar-nav a.active { background: var(--accent); color: var(--primary-dark); font-weight: 700; }
    .sidebar-nav a.active:hover { background: var(--accent-dark); }

    .nav-icon { font-size: 1rem; width: 20px; text-align: center; }

    .sidebar-footer {
      padding: 16px 12px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-footer a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: var(--radius-sm);
      color: rgba(255,255,255,0.5);
      font-size: 0.85rem;
      text-decoration: none;
      transition: all 0.2s;
    }

    .sidebar-footer a:hover { background: rgba(255,0,0,0.12); color: #ff6b6b; }

    /* ── Main Content ─────────────────────────────────── */
    .admin-main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; min-width: 0; }

    .admin-topbar {
      background: #fff;
      border-bottom: 1px solid #e0e6ef;
      padding: 0 32px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: 0 1px 8px rgba(0,0,0,0.05);
    }

    .topbar-title { font-size: 1.1rem; font-weight: 700; color: var(--primary); }

    .topbar-user {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.88rem;
      color: var(--text-medium);
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--primary);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.85rem;
    }

    .admin-content { padding: 32px; flex: 1; }

    /* ── Cards ──────────────────────────────────────────── */
    .admin-card {
      background: #fff;
      border-radius: var(--radius-md);
      border: 1px solid #e0e6ef;
      box-shadow: 0 1px 6px rgba(26,58,92,0.06);
      overflow: hidden;
    }

    .card-head {
      padding: 18px 24px;
      border-bottom: 1px solid #e0e6ef;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .card-head h3 { font-size: 0.95rem; font-weight: 700; color: var(--primary); }
    .card-body { padding: 24px; }

    /* ── Stats ──────────────────────────────────────────── */
    .stat-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }

    .stat-box {
      background: #fff;
      border-radius: var(--radius-md);
      padding: 24px;
      border: 1px solid #e0e6ef;
      box-shadow: 0 1px 6px rgba(26,58,92,0.06);
    }

    .stat-box .label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); margin-bottom: 10px; }
    .stat-box .value { font-size: 2rem; font-weight: 800; color: var(--primary); letter-spacing: -1px; }
    .stat-box .trend { font-size: 0.78rem; color: var(--success); margin-top: 6px; }

    /* ── Tables ─────────────────────────────────────────── */
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th { padding: 12px 16px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-light); background: #f8f9fc; border-bottom: 1px solid #e0e6ef; }
    .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f0f2f5; font-size: 0.88rem; color: var(--text-dark); vertical-align: middle; }
    .admin-table tr:last-child td { border-bottom: none; }
    .admin-table tr:hover td { background: #fafbff; }

    /* ── Badges ─────────────────────────────────────────── */
    .badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-sale  { background: rgba(231,76,60,0.12);  color: #c0392b; }
    .badge-new   { background: rgba(46,204,113,0.12); color: #1a7a4a; }
    .badge-hot   { background: rgba(240,165,0,0.18);  color: #9a6800; }
    .badge-none  { background: #f0f2f5; color: var(--text-light); }

    /* ── Form inputs ────────────────────────────────────── */
    .form-control { width: 100%; padding: 10px 14px; border: 1.5px solid #e0e6ef; border-radius: var(--radius-sm); font-size: 0.9rem; font-family: var(--font-main); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,58,92,0.1); }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-dark); margin-bottom: 7px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    textarea.form-control { resize: vertical; min-height: 110px; }

    /* ── Action buttons ─────────────────────────────────── */
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; font-family: var(--font-main); }
    .btn-primary { background: var(--accent); color: var(--primary-dark); }
    .btn-primary:hover { background: var(--accent-dark); }
    .btn-dark { background: var(--primary); color: #fff; }
    .btn-dark:hover { background: var(--primary-dark); }
    .btn-danger { background: #fdecea; color: #c0392b; border: 1px solid #fcc; }
    .btn-danger:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
    .btn-sm { padding: 6px 12px; font-size: 0.78rem; }
    .btn-success { background: #eafaf1; color: #1a7a4a; border: 1px solid #c0e8d0; }
    .btn-success:hover { background: #2ecc71; color: #fff; border-color: #2ecc71; }

    /* ── Alerts ─────────────────────────────────────────── */
    .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.88rem; }
    .alert-success { background: #eafaf1; border: 1px solid #a9dfbf; color: #1a7a4a; }
    .alert-error   { background: #fdecea; border: 1px solid #fcc; color: #c0392b; }

    @media (max-width: 900px) {
      .admin-sidebar { width: 200px; }
      .admin-main { margin-left: 200px; }
      .stat-cards { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 640px) {
      .admin-sidebar { display: none; }
      .admin-main { margin-left: 0; }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div class="logo">Shop<span>Zone</span></div>
    <p>Admin Panel</p>
  </div>
  <nav class="sidebar-nav">
    <p class="nav-section">Main</p>
    <a href="<?= SITE_URL ?>/admin/index.php"    class="<?= ($adminActive??'')==='dashboard'?'active':'' ?>"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="<?= SITE_URL ?>/admin/products.php" class="<?= ($adminActive??'')==='products'?'active':'' ?>"><span class="nav-icon">📦</span> Products</a>
    <a href="<?= SITE_URL ?>/admin/categories.php" class="<?= ($adminActive??'')==='categories'?'active':'' ?>"><span class="nav-icon">🏷️</span> Categories</a>
    <a href="<?= SITE_URL ?>/admin/contacts.php" class="<?= ($adminActive??'')==='contacts'?'active':'' ?>"><span class="nav-icon">💬</span> Messages</a>
    <p class="nav-section" style="margin-top:12px;">Store</p>
    <a href="<?= SITE_URL ?>/index.php" target="_blank"><span class="nav-icon">🌐</span> View Store</a>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= SITE_URL ?>/admin/logout.php"><span class="nav-icon">🚪</span> Sign Out</a>
  </div>
</aside>

<!-- Main -->
<main class="admin-main">
  <header class="admin-topbar">
    <div class="topbar-title"><?= h($adminTitle ?? 'Dashboard') ?></div>
    <div class="topbar-user">
      <span><?= h($adminName) ?></span>
      <div class="user-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
    </div>
  </header>

  <div class="admin-content">
    <?php
    $flash = getFlash();
    if ($flash):
      $cls = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
    ?>
      <div class="alert <?= $cls ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
