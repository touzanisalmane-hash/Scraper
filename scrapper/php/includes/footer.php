<?php
/**
 * includes/footer.php
 * Shared footer — consistent across all frontend pages.
 */
?>
<footer class="footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand column -->
      <div class="footer-brand">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">Shop<span>Zone</span></a>
        <p>Your one-stop destination for fashion, electronics, and lifestyle essentials. Quality products, unbeatable prices.</p>
        <div class="social-links">
          <a href="#" class="social-link" aria-label="Facebook">f</a>
          <a href="#" class="social-link" aria-label="Twitter">t</a>
          <a href="#" class="social-link" aria-label="Instagram">in</a>
          <a href="#" class="social-link" aria-label="Pinterest">p</a>
        </div>
      </div>

      <!-- Shop links -->
      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/index.php">New Arrivals</a></li>
          <li><a href="<?= SITE_URL ?>/index.php">Best Sellers</a></li>
          <li><a href="<?= SITE_URL ?>/search.php">All Products</a></li>
          <li><a href="<?= SITE_URL ?>/cart.php">Cart</a></li>
        </ul>
      </div>

      <!-- Company links -->
      <div class="footer-col">
        <h4>Company</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
          <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Blog</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="footer-col">
        <h4>Stay Updated</h4>
        <p style="font-size:0.84rem;margin-bottom:14px;color:rgba(255,255,255,0.45);">
          Get deals and new arrivals in your inbox.
        </p>
        <form method="post" action="#" class="footer-newsletter">
          <input type="email" name="newsletter_email" placeholder="Your email address" />
          <button type="submit">Subscribe</button>
        </form>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
