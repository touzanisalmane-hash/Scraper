<?php
/**
 * account.php — Login and registration page.
 * Handles both forms via POST; stores logged-in user in session.
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle  = 'My Account';
$activePage = 'account';

$loginErrors = [];
$regErrors   = [];

// ── Handle LOGIN ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $loginErrors[] = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // In a real app you'd have a `users` table for customers.
        // Here we verify against the admins table as a placeholder.
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            redirect(SITE_URL . '/index.php');
        } else {
            $loginErrors[] = 'Invalid email or password.';
        }
    }
}

// ── Handle REGISTRATION ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['reg_email']  ?? '');
    $password  = $_POST['reg_password']    ?? '';
    $confirm   = $_POST['reg_confirm']     ?? '';
    $terms     = isset($_POST['terms']);

    if (empty($firstName)) $regErrors[] = 'First name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $regErrors[] = 'A valid email is required.';
    if (strlen($password) < 8) $regErrors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $regErrors[] = 'Passwords do not match.';
    if (!$terms) $regErrors[] = 'You must agree to the Terms of Service.';

    if (empty($regErrors)) {
        // Check duplicate email (admins table used as placeholder)
        $stmt = db()->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $regErrors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare(
                'INSERT INTO admins (username, email, password) VALUES (?, ?, ?)'
            );
            $stmt->execute([$firstName . ' ' . $lastName, $email, $hash]);
            setFlash('success', 'Account created! You can now sign in.');
            redirect(SITE_URL . '/account.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <h1>My Account</h1>
  <p>Sign in or create an account to start shopping</p>
</div>

<!-- Auth Section -->
<section class="auth-section">
  <div class="container">
    <div class="auth-grid">

      <!-- ── LOGIN CARD ── -->
      <div class="auth-card">
        <div class="auth-header">
          <h2>Welcome Back</h2>
          <p>Sign in to your <?= SITE_NAME ?> account</p>
        </div>

        <?php if (!empty($loginErrors)): ?>
          <div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:12px 16px;margin-bottom:20px;">
            <?php foreach ($loginErrors as $err): ?>
              <p style="color:#c0392b;font-size:0.85rem;margin:0;"><?= h($err) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="account.php">
          <div class="form-group">
            <label for="login-email">Email Address</label>
            <input type="email" id="login-email" name="email" class="form-control"
                   placeholder="you@example.com"
                   value="<?= h($_POST['email'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" class="form-control"
                   placeholder="Enter your password" required />
            <p class="form-hint"><a href="#" style="color:var(--primary);font-weight:600;">Forgot password?</a></p>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="remember" />
              Keep me signed in for 30 days
            </label>
          </div>
          <button type="submit" name="login_submit" class="btn btn-dark btn-full btn-lg">Sign In</button>
        </form>

        <div class="form-divider">or continue with</div>
        <div style="display:flex;gap:12px;">
          <button type="button" class="btn btn-ghost btn-full" style="font-size:0.85rem;">G &nbsp; Google</button>
          <button type="button" class="btn btn-ghost btn-full" style="font-size:0.85rem;">f &nbsp; Facebook</button>
        </div>
        <div class="form-footer">Don't have an account? <a href="#register">Register below</a></div>
      </div>

      <!-- ── REGISTER CARD ── -->
      <div class="auth-card" id="register">
        <div class="auth-header">
          <h2>Create Account</h2>
          <p>Join <?= SITE_NAME ?> and start enjoying exclusive benefits</p>
        </div>

        <?php if (!empty($regErrors)): ?>
          <div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:12px 16px;margin-bottom:20px;">
            <?php foreach ($regErrors as $err): ?>
              <p style="color:#c0392b;font-size:0.85rem;margin:0;"><?= h($err) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="account.php">
          <div class="form-row">
            <div class="form-group">
              <label for="reg-first">First Name</label>
              <input type="text" id="reg-first" name="first_name" class="form-control"
                     placeholder="John" value="<?= h($_POST['first_name'] ?? '') ?>" required />
            </div>
            <div class="form-group">
              <label for="reg-last">Last Name</label>
              <input type="text" id="reg-last" name="last_name" class="form-control"
                     placeholder="Doe" value="<?= h($_POST['last_name'] ?? '') ?>" />
            </div>
          </div>
          <div class="form-group">
            <label for="reg-email">Email Address</label>
            <input type="email" id="reg-email" name="reg_email" class="form-control"
                   placeholder="you@example.com" value="<?= h($_POST['reg_email'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label for="reg-password">Password</label>
            <input type="password" id="reg-password" name="reg_password" class="form-control"
                   placeholder="Minimum 8 characters" required />
            <p class="form-hint">Use letters, numbers, and symbols for a strong password.</p>
          </div>
          <div class="form-group">
            <label for="reg-confirm">Confirm Password</label>
            <input type="password" id="reg-confirm" name="reg_confirm" class="form-control"
                   placeholder="Repeat your password" required />
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="terms" required />
              I agree to the <a href="#" style="color:var(--primary);font-weight:600;">Terms of Service</a>
              and <a href="#" style="color:var(--primary);font-weight:600;">Privacy Policy</a>
            </label>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="newsletter" />
              Send me exclusive deals and new arrival updates
            </label>
          </div>
          <button type="submit" name="register_submit" class="btn btn-primary btn-full btn-lg">Create My Account</button>
        </form>

        <div class="form-footer">Already have an account? <a href="#top">Sign in above</a></div>

        <!-- Benefits -->
        <div class="auth-benefits">
          <h3>Why Join <?= SITE_NAME ?>?</h3>
          <div class="benefit-list">
            <div class="benefit-item">
              <div class="benefit-icon">🎁</div>
              <div><h4>Exclusive Member Deals</h4><p>Early access to sales and member-only discounts.</p></div>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">📦</div>
              <div><h4>Order Tracking</h4><p>Track your shipments in real-time from purchase to door.</p></div>
            </div>
            <div class="benefit-item">
              <div class="benefit-icon">⭐</div>
              <div><h4>Loyalty Points</h4><p>Earn points on every purchase and redeem for discounts.</p></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
