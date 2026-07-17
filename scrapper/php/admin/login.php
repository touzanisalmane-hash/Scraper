<?php
/**
 * admin/login.php — Admin authentication page.
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';

// Already logged in
if (!empty($_SESSION['admin_id'])) {
    redirect(SITE_URL . '/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']       ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            setFlash('success', 'Welcome back, ' . $admin['username'] . '!');
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/style.css" />
  <style>
    body { background: var(--primary-dark); display:flex; align-items:center; justify-content:center; min-height:100vh; }
    .login-box { background:#fff; border-radius:var(--radius-lg); padding:48px; width:100%; max-width:420px; box-shadow:var(--shadow-lg); }
    .login-logo { font-size:1.8rem; font-weight:800; color:var(--primary); letter-spacing:-1px; margin-bottom:4px; }
    .login-logo span { color:var(--accent); }
    .admin-badge { font-size:0.72rem; font-weight:700; background:rgba(26,58,92,0.08); color:var(--primary); padding:4px 10px; border-radius:100px; display:inline-block; margin-bottom:28px; text-transform:uppercase; letter-spacing:0.8px; }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="login-logo">Shop<span>Zone</span></div>
    <div class="admin-badge">Admin Panel</div>
    <h2 style="font-size:1.4rem;font-weight:800;color:var(--primary);margin-bottom:6px;">Sign In</h2>
    <p style="color:var(--text-light);font-size:0.88rem;margin-bottom:28px;">Enter your admin credentials to continue.</p>

    <?php if ($error): ?>
      <div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:12px 16px;margin-bottom:20px;">
        <p style="color:#c0392b;font-size:0.85rem;margin:0;"><?= h($error) ?></p>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" class="form-control"
               placeholder="admin" value="<?= h($_POST['username'] ?? '') ?>" autofocus required />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="••••••••" required />
      </div>
      <button type="submit" class="btn btn-dark btn-full btn-lg" style="margin-top:8px;">Sign In</button>
    </form>

    <p style="margin-top:20px;font-size:0.8rem;color:var(--text-light);text-align:center;">
      <a href="<?= SITE_URL ?>/index.php" style="color:var(--primary);">← Back to store</a>
    </p>
  </div>
</body>
</html>
