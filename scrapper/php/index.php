<?php
/**
 * index.php — Home page
 * Loads featured products and categories dynamically from the database.
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle  = 'Home';
$activePage = 'home';

// ── Featured products (is_featured = 1) ──────────────────────
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_featured = 1
     ORDER BY p.created_at DESC
     LIMIT 6'
);
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// ── All categories ────────────────────────────────────────────
$categories = db()->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

// Category card images (keyed by slug)
$catImages = [
    'fashion'     => 'https://images.pexels.com/photos/1536619/pexels-photo-1536619.jpeg?auto=compress&cs=tinysrgb&w=400',
    'electronics' => 'https://images.pexels.com/photos/356056/pexels-photo-356056.jpeg?auto=compress&cs=tinysrgb&w=400',
    'footwear'    => 'https://images.pexels.com/photos/1126993/pexels-photo-1126993.jpeg?auto=compress&cs=tinysrgb&w=400',
    'accessories' => 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400',
    'bags'        => 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400&h=300&fit=crop&crop=right',
    'eyewear'     => 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=400',
    'clothing'    => 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg?auto=compress&cs=tinysrgb&w=400',
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">New Collection <?= date('Y') ?></div>
      <h1 class="hero-title">Discover Your<br /><span>Perfect Style</span></h1>
      <p class="hero-subtitle">Shop the latest trends in fashion, electronics, and lifestyle essentials — curated for the modern shopper.</p>
      <div class="hero-actions">
        <a href="#products" class="btn btn-primary btn-lg">Shop Now</a>
        <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline btn-lg">Our Story</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><h3>50K+</h3><p>Products</p></div>
        <div class="hero-stat"><h3>120K+</h3><p>Happy Customers</p></div>
        <div class="hero-stat"><h3>4.9★</h3><p>Average Rating</p></div>
      </div>
    </div>
  </div>
</section>

<!-- Trust Bar -->
<section class="trust-bar">
  <div class="container">
    <div class="trust-grid">
      <div class="trust-item"><div class="trust-icon">🚚</div><h4>Free Shipping</h4><p>On all orders over $50</p></div>
      <div class="trust-item"><div class="trust-icon">↩️</div><h4>Easy Returns</h4><p>30-day return policy</p></div>
      <div class="trust-item"><div class="trust-icon">🔒</div><h4>Secure Payment</h4><p>100% protected checkout</p></div>
      <div class="trust-item"><div class="trust-icon">🎧</div><h4>24/7 Support</h4><p>Here to help anytime</p></div>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="products-section" id="products">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Featured Products</h2>
      <p class="section-subtitle">Hand-picked top sellers just for you</p>
    </div>

    <?php if (empty($featuredProducts)): ?>
      <p style="text-align:center;color:var(--text-light);padding:40px 0;">
        No featured products yet. <a href="<?= SITE_URL ?>/admin/" style="color:var(--primary);">Add products in the admin panel.</a>
      </p>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
          <?php include __DIR__ . '/includes/product_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:48px;">
      <a href="<?= SITE_URL ?>/search.php" class="btn btn-dark btn-lg">View All Products</a>
    </div>
  </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="categories-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Shop by Category</h2>
      <p class="section-subtitle">Find exactly what you're looking for</p>
    </div>
    <div class="categories-grid">
      <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
        <?php $img = $catImages[$cat['slug']] ?? 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400'; ?>
        <a href="<?= SITE_URL ?>/search.php?category=<?= (int)$cat['id'] ?>" class="category-card">
          <img src="<?= $img ?>" alt="<?= h($cat['name']) ?>" loading="lazy" />
          <div class="category-overlay">
            <div class="category-name"><?= h($cat['name']) ?></div>
            <div class="category-count"><?= (int)$cat['product_count'] ?> items</div>
          </div>
          <div class="category-arrow">→</div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Promo Section -->
<section class="promo-section">
  <div class="container">
    <div class="promo-inner">
      <div class="promo-card gold">
        <p class="promo-eyebrow">Limited Time Offer</p>
        <h2 class="promo-title">Summer Sale<br />Up to 40% Off</h2>
        <p class="promo-desc">Refresh your wardrobe this season with our biggest sale of the year on selected styles.</p>
        <a href="<?= SITE_URL ?>/search.php?badge=sale" class="btn btn-dark">Shop the Sale</a>
      </div>
      <div class="promo-card dark">
        <p class="promo-eyebrow">New Arrivals</p>
        <h2 class="promo-title">Tech Picks<br />Just Landed</h2>
        <p class="promo-desc">The latest gadgets and accessories, curated for those who demand more from their tech.</p>
        <a href="<?= SITE_URL ?>/search.php?badge=new" class="btn btn-primary">Explore Now</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
