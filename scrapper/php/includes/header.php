<?php
/**
 * includes/header.php
 * Shared <head> + announcement banner + sticky navbar.
 *
 * Required variables (set before including):
 *   $pageTitle   string  — <title> tag value
 *   $activePage  string  — 'home'|'account'|'contact'|'about'|'cart'
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';

$cartItems = cartCount();
$flash     = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($pageTitle ?? SITE_NAME) ?> — <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/style.css" />
</head>
<body>

<?php if ($flash): ?>
  <?php $fc = $flash['type'] === 'success' ? '#2ecc71' : '#e74c3c'; ?>
  <div style="background:<?= $fc ?>;color:#fff;text-align:center;padding:10px 24px;font-size:0.875rem;font-weight:600;">
    <?= h($flash['message']) ?>
  </div>
<?php endif; ?>

<!-- Announcement Banner -->
<div class="banner">
  Free shipping on orders over $50 &nbsp;|&nbsp; Use code <strong>SAVE20</strong> for 20% off your first order
</div>

<!-- Navigation -->
<input type="checkbox" id="nav-toggle" class="nav-toggle" style="display:none" />
<nav class="navbar">
  <div class="container">
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo">Shop<span>Zone</span></a>
    <div class="nav-menu">
      <ul class="nav-links">
        <li><a href="<?= SITE_URL ?>/index.php"<?= ($activePage??'')==='home'?' class="active"':'' ?>>Home</a></li>
        <li><a href="<?= SITE_URL ?>/account.php"<?= ($activePage??'')==='account'?' class="active"':'' ?>>Account</a></li>
        <li><a href="<?= SITE_URL ?>/contact.php"<?= ($activePage??'')==='contact'?' class="active"':'' ?>>Contact</a></li>
        <li><a href="<?= SITE_URL ?>/about.php"<?= ($activePage??'')==='about'?' class="active"':'' ?>>About Us</a></li>
        <li>
          <a href="<?= SITE_URL ?>/cart.php"
             class="cart-link<?= ($activePage??'')==='cart'?' active':'' ?>">
            &#128722; Cart
            <span class="cart-badge"><?= $cartItems ?></span>
          </a>
        </li>
      </ul>
    </div>
    <label for="nav-toggle" class="nav-hamburger">
      <span></span><span></span><span></span>
    </label>
  </div>
</nav>
