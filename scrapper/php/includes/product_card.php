<?php
/**
 * includes/product_card.php
 * Reusable product card partial.
 * Expects $product array with keys:
 *   id, name, slug, price, old_price, badge, image, category_name
 */
$badge    = $product['badge'] ?? 'none';
$imgSrc   = $product['image']
              ? (str_starts_with($product['image'], 'http') ? $product['image'] : UPLOADS_URL . h($product['image']))
              : 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=400';
?>
<div class="product-card">
  <?php if ($badge !== 'none'): ?>
    <span class="product-badge <?= h($badge) ?>">
      <?= $badge === 'sale' ? ($product['old_price'] ? '-' . round((1 - $product['price'] / $product['old_price']) * 100) . '%' : 'Sale') : ucfirst($badge) ?>
    </span>
  <?php endif; ?>

  <div class="product-img-wrap">
    <img src="<?= $imgSrc ?>" alt="<?= h($product['name']) ?>" loading="lazy" />
    <div class="product-overlay">
      <form method="post" action="<?= SITE_URL ?>/cart_action.php" style="display:contents">
        <input type="hidden" name="action"     value="add" />
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>" />
        <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
      </form>
      <a href="<?= SITE_URL ?>/product.php?slug=<?= h($product['slug']) ?>" class="btn btn-outline btn-sm">Details</a>
    </div>
  </div>

  <div class="product-body">
    <p class="product-category"><?= h($product['category_name'] ?? '') ?></p>
    <h3 class="product-title">
      <a href="<?= SITE_URL ?>/product.php?slug=<?= h($product['slug']) ?>"
         style="color:inherit;text-decoration:none;">
        <?= h($product['name']) ?>
      </a>
    </h3>
    <div class="rating-row">
      <span class="stars">★★★★★</span>
      <span class="rating-count">(0)</span>
    </div>
    <div class="product-footer">
      <div class="product-price">
        <span class="price-current"><?= price($product['price']) ?></span>
        <?php if (!empty($product['old_price'])): ?>
          <span class="price-old"><?= price($product['old_price']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
