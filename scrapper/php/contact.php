<?php
/**
 * contact.php — Contact page with form saved to database.
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle  = 'Contact Us';
$activePage = 'contact';
$errors     = [];
$success    = false;

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']      ?? '');
    $email    = trim($_POST['email']     ?? '');
    $subject  = trim($_POST['subject']   ?? '');
    $orderRef = trim($_POST['order_ref'] ?? '');
    $message  = trim($_POST['message']   ?? '');

    if (empty($name))                                    $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = 'A valid email is required.';
    if (empty($subject))                                 $errors[] = 'Subject is required.';
    if (empty($message))                                 $errors[] = 'Message cannot be empty.';

    if (empty($errors)) {
        $stmt = db()->prepare(
            'INSERT INTO contacts (name, email, subject, order_ref, message)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $subject, $orderRef ?: null, $message]);
        $success = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <h1>Get in Touch</h1>
  <p>We'd love to hear from you. Our friendly team is always here to help.</p>
</div>

<!-- Contact Section -->
<section class="contact-section">
  <div class="container">
    <div class="contact-grid">

      <!-- ── CONTACT FORM ── -->
      <div class="form-card">
        <h2>Send Us a Message</h2>
        <p>Fill in the form below and we'll get back to you within 24 hours.</p>

        <?php if ($success): ?>
          <div style="background:#eafaf1;border:1px solid #2ecc71;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
            <p style="color:#1a7a4a;font-weight:600;margin:0;">
              ✓ Thank you! Your message has been sent. We'll be in touch soon.
            </p>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div style="background:#fdecea;border:1px solid #e74c3c;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
            <?php foreach ($errors as $err): ?>
              <p style="color:#c0392b;font-size:0.85rem;margin:0;"><?= h($err) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="contact.php">
          <div class="form-row">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" class="form-control"
                     placeholder="John Doe" value="<?= h($_POST['name'] ?? '') ?>" required />
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" class="form-control"
                     placeholder="you@example.com" value="<?= h($_POST['email'] ?? '') ?>" required />
            </div>
          </div>
          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control"
                   placeholder="How can we help?" value="<?= h($_POST['subject'] ?? '') ?>" required />
          </div>
          <div class="form-group">
            <label for="order_ref">Order Number <span style="font-weight:400;color:var(--text-light);">(optional)</span></label>
            <input type="text" id="order_ref" name="order_ref" class="form-control"
                   placeholder="e.g. #SZ-00123" value="<?= h($_POST['order_ref'] ?? '') ?>" />
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" class="form-control"
                      placeholder="Tell us more about your inquiry..." required><?= h($_POST['message'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" required />
              I agree to the <a href="#" style="color:var(--primary);font-weight:600;">Privacy Policy</a> and consent to being contacted.
            </label>
          </div>
          <button type="submit" class="btn btn-dark btn-lg" style="min-width:200px;">Send Message</button>
        </form>
      </div>

      <!-- ── CONTACT INFO SIDEBAR ── -->
      <div class="contact-sidebar">
        <div class="contact-info-card">
          <div class="contact-icon">📍</div>
          <div>
            <h4>Our Address</h4>
            <p>123 Commerce Street, Suite 400<br />New York, NY 10001, USA</p>
          </div>
        </div>
        <div class="contact-info-card">
          <div class="contact-icon">📞</div>
          <div>
            <h4>Phone</h4>
            <a href="tel:+18005551234">+1 (800) 555-1234</a>
          </div>
        </div>
        <div class="contact-info-card">
          <div class="contact-icon">✉️</div>
          <div>
            <h4>Email</h4>
            <a href="mailto:support@shopzone.com">support@shopzone.com</a>
          </div>
        </div>
        <div class="contact-info-card">
          <div class="contact-icon">💬</div>
          <div>
            <h4>Live Chat</h4>
            <p>Available Mon – Fri<br />9:00 AM – 6:00 PM EST</p>
          </div>
        </div>
        <div class="hours-card">
          <h4>Business Hours</h4>
          <div class="hours-row"><span>Monday – Friday</span><span>9:00 AM – 6:00 PM</span></div>
          <div class="hours-row"><span>Saturday</span><span>10:00 AM – 4:00 PM</span></div>
          <div class="hours-row"><span>Sunday</span><span>Closed</span></div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
