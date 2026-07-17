<?php
/**
 * product.php — Product detail page.
 * Accessed via: product.php?slug=product-slug
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    redirect(SITE_URL . '/index.php');
}

// ── Fetch product ─────────────────────────────────────────────
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.slug = ?
     LIMIT 1'
);
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    redirect(SITE_URL . '/index.php');
}

// ── Related products (same category, excluding this one) ──────
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.category_id = ? AND p.id != ?
     ORDER BY RAND()
     LIMIT 4'
);
$stmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmt->fetchAll();

$pageTitle  = $product['name'];
$activePage = 'home';

$imgSrc = $product['image']
    ? (str_starts_with($product['image'], 'http') ? $product['image'] : UPLOADS_URL . h($product['image']))
    : 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=800';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero" style="padding:40px 0;">
  <p style="font-size:0.85rem;color:rgba(255,255,255,0.5);">
    <a href="<?= SITE_URL ?>/index.php" style="color:rgba(255,255,255,0.5);">Home</a>
    &rsaquo;
    <a href="<?= SITE_URL ?>/search.php?category=<?= (int)$product['category_id'] ?>" style="color:rgba(255,255,255,0.5);">
      <?= h($product['category_name']) ?>
    </a>
    &rsaquo; <?= h($product['name']) ?>
  </p>
</div>

<!-- Product Detail -->
<section style="padding:72px 0;background:var(--bg-white);">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start;">

      <!-- Image -->
      <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
        <img src="<?= $imgSrc ?>" alt="<?= h($product['name']) ?>"
             style="width:100%;height:480px;object-fit:cover;" />
      </div>

      <!-- Info -->
      <div>
        <p style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-light);margin-bottom:8px;">
          <?= h($product['category_name']) ?>
        </p>
        <h1 style="font-size:2rem;font-weight:800;color:var(--primary);letter-spacing:-0.5px;margin-bottom:12px;">
          <?= h($product['name']) ?>
        </h1>

        <!-- Rating placeholder -->
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;">
          <span style="color:var(--accent);font-size:0.9rem;">★★★★★</span>
          <span style="font-size:0.82rem;color:var(--text-light);">(0 reviews)</span>
        </div>

        <!-- Price -->
        <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:24px;">
          <span style="font-size:2rem;font-weight:800;color:var(--primary);"><?= price($product['price']) ?></span>
          <?php if (!empty($product['old_price'])): ?>
            <span style="font-size:1.1rem;color:var(--text-light);text-decoration:line-through;"><?= price($product['old_price']) ?></span>
            <?php $pct = round((1 - $product['price'] / $product['old_price']) * 100); ?>
            <span style="background:var(--error);color:#fff;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:100px;">
              −<?= $pct ?>%
            </span>
          <?php endif; ?>
        </div>

        <!-- Description -->
        <p style="color:var(--text-medium);font-size:0.95rem;line-height:1.85;margin-bottom:32px;">
          <?= nl2br(h($product['description'] ?? '')) ?>
        </p>

        <!-- Add to cart -->
        <form method="post" action="<?= SITE_URL ?>/cart_action.php" style="display:flex;gap:12px;flex-wrap:wrap;">
          <input type="hidden" name="action"     value="add" />
          <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>" />
          <div style="display:flex;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
            <button type="submit" name="qty" value="1" class="btn btn-primary btn-lg" style="border-radius:0;">
              Add to Cart
            </button>
          </div>
          <a href="<?= SITE_URL ?>/cart.php" class="btn btn-dark btn-lg">View Cart</a>
        </form>

        <!-- Stock info -->
        <p style="margin-top:16px;font-size:0.82rem;color:var(--success);">
          ✓ In stock — <?= (int)$product['stock'] ?> units available
        </p>

        <!-- Meta -->
        <hr style="border:none;border-top:1px solid var(--border);margin:28px 0;" />
        <p style="font-size:0.82rem;color:var(--text-light);">
          <strong style="color:var(--text-dark);">Category:</strong>
          <a href="<?= SITE_URL ?>/search.php?category=<?= (int)$product['category_id'] ?>"
             style="color:var(--primary);"><?= h($product['category_name']) ?></a>
        </p>
        <?php if ($product['badge'] !== 'none'): ?>
          <p style="font-size:0.82rem;color:var(--text-light);margin-top:6px;">
            <strong style="color:var(--text-dark);">Tag:</strong>
            <span style="text-transform:capitalize;"><?= h($product['badge']) ?></span>
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section style="padding:80px 0;background:var(--bg-light);">
  <div class="container">
    <div class="section-header" style="margin-bottom:40px;">
      <h2 class="section-title">Related Products</h2>
      <p class="section-subtitle">More from <?= h($product['category_name']) ?></p>
    </div>
    <div class="products-grid">
      <?php foreach ($relatedProducts as $product): ?>
        <?php include __DIR__ . '/includes/product_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
