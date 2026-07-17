<?php
/**
 * about.php — About Us page (static content, no DB needed).
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

// ── Optionally pull stats from DB ─────────────────────────────
$productCount  = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categoryCount = (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn();

$pageTitle  = 'About Us';
$activePage = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <h1>Our Story</h1>
  <p>Built with passion. Driven by quality. Designed for you.</p>
</div>

<!-- Our Story -->
<section class="about-story">
  <div class="container">
    <div class="story-grid">
      <div class="story-img-wrap">
        <img src="https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=700"
             alt="Our Team at Work" />
      </div>
      <div class="story-content">
        <h2 class="section-title">Who We Are</h2>
        <p><?= SITE_NAME ?> was founded in 2018 with a simple yet powerful mission: to make premium-quality products accessible to everyone, without compromise. What started as a small online boutique in New York has grown into a globally recognised e-commerce destination.</p>
        <p>We believe that great design and quality craftsmanship should not be exclusive. By partnering directly with manufacturers and independent creators, we cut out the middleman and pass those savings directly to our customers.</p>
        <p>Today, <?= SITE_NAME ?> serves over 120,000 happy customers across 40+ countries, offering more than <?= number_format($productCount) ?> carefully curated products across <?= $categoryCount ?> categories.</p>
        <div class="value-tags">
          <div class="value-tag">Quality First</div>
          <div class="value-tag">Customer Focus</div>
          <div class="value-tag">Sustainability</div>
          <div class="value-tag">Innovation</div>
          <div class="value-tag">Transparency</div>
          <div class="value-tag">Community</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats -->
<section class="stats-section">
  <div class="container">
    <div class="section-header" style="margin-bottom:48px;">
      <h2 class="section-title"><?= SITE_NAME ?> by the Numbers</h2>
      <p class="section-subtitle">The milestones that keep us motivated every day</p>
    </div>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-number">120<span>K+</span></div><div class="stat-label">Happy Customers</div></div>
      <div class="stat-card"><div class="stat-number"><?= $productCount ?><span>+</span></div><div class="stat-label">Products Listed</div></div>
      <div class="stat-card"><div class="stat-number">40<span>+</span></div><div class="stat-label">Countries Served</div></div>
      <div class="stat-card"><div class="stat-number">4.9<span>★</span></div><div class="stat-label">Average Rating</div></div>
    </div>
  </div>
</section>

<!-- Mission / Vision -->
<section class="mission-section">
  <div class="container">
    <div class="section-header" style="margin-bottom:48px;">
      <h2 class="section-title" style="color:#fff;">What Drives Us</h2>
      <p class="section-subtitle" style="color:rgba(255,255,255,0.5);">Our guiding principles and long-term vision</p>
    </div>
    <div class="mv-grid">
      <div class="mv-card">
        <div class="mv-icon">🎯</div>
        <h3>Our Mission</h3>
        <p>To democratise access to quality products by leveraging technology and direct partnerships, delivering exceptional value to every customer — regardless of where they are in the world.</p>
      </div>
      <div class="mv-card">
        <div class="mv-icon">🔭</div>
        <h3>Our Vision</h3>
        <p>To become the world's most trusted e-commerce platform, celebrated not just for what we sell, but for how we operate — with honesty, sustainability, and genuine care for our customers.</p>
      </div>
      <div class="mv-card">
        <div class="mv-icon">💡</div>
        <h3>Our Values</h3>
        <p>Integrity in every decision. Curiosity to keep improving. Empathy for our customers and partners. We celebrate diversity of thought and background.</p>
      </div>
      <div class="mv-card">
        <div class="mv-icon">🌱</div>
        <h3>Sustainability</h3>
        <p>We are actively reducing our carbon footprint — from eco-friendly packaging to partnering with suppliers who share our commitment to ethical sourcing.</p>
      </div>
    </div>
  </div>
</section>

<!-- Team -->
<section class="team-section">
  <div class="container">
    <div class="section-header" style="margin-bottom:48px;">
      <h2 class="section-title">Meet the Team</h2>
      <p class="section-subtitle">The passionate people behind <?= SITE_NAME ?></p>
    </div>
    <div class="team-grid">
      <?php
      $team = [
          ['name'=>'Alex Morgan',    'role'=>'Chief Executive Officer', 'tag'=>'Co-Founder', 'img'=>'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=400'],
          ['name'=>'Sarah Chen',     'role'=>'Chief Product Officer',   'tag'=>'Co-Founder', 'img'=>'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=400'],
          ['name'=>'Marcus Williams','role'=>'Head of Technology',      'tag'=>'Engineering', 'img'=>'https://images.pexels.com/photos/1222271/pexels-photo-1222271.jpeg?auto=compress&cs=tinysrgb&w=400'],
          ['name'=>'Priya Sharma',   'role'=>'Head of Marketing',       'tag'=>'Growth',      'img'=>'https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=400'],
      ];
      foreach ($team as $member): ?>
        <div class="team-card">
          <div class="team-img-wrap">
            <img src="<?= h($member['img']) ?>" alt="<?= h($member['name']) ?>" loading="lazy" />
            <div class="team-overlay">
              <a href="#">in</a><a href="#">t</a>
            </div>
          </div>
          <div class="team-body">
            <h3><?= h($member['name']) ?></h3>
            <p class="role"><?= h($member['role']) ?></p>
            <span class="team-tag"><?= h($member['tag']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
