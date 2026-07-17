<?php
/**
 * cart.php — Shopping cart using PHP sessions.
 * Renders current cart items, order summary, and upsell products.
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle  = 'Shopping Cart';
$activePage = 'cart';

// ── Cart data ─────────────────────────────────────────────────
$cartItems = $_SESSION['cart'] ?? [];
$subtotal  = 0.0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

// Promo code (basic demo: SAVE20 = 20% off)
$promoCode    = strtoupper(trim($_SESSION['promo_code'] ?? ''));
$discount     = 0.0;
$promoApplied = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $code = strtoupper(trim($_POST['promo_code'] ?? ''));
    if ($code === 'SAVE20') {
        $_SESSION['promo_code'] = $code;
        $promoApplied = true;
    }
}
if ($promoCode === 'SAVE20' && $subtotal > 0) {
    $discount = round($subtotal * 0.20, 2);
}

$shipping = ($subtotal - $discount) >= 50 ? 0.0 : 9.99;
$tax      = round(($subtotal - $discount) * 0.08, 2);  // 8% tax
$total    = max(0, $subtotal - $discount + $shipping + $tax);

$itemCount = count($cartItems);

// ── Upsell: random products not in cart ───────────────────────
$excludeIds = array_keys($cartItems) ?: [0];
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.id NOT IN ($placeholders)
     ORDER BY RAND() LIMIT 4"
);
$stmt->execute($excludeIds);
$upsellProducts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <h1>Your Shopping Cart</h1>
  <p>Review your items before proceeding to checkout</p>
</div>

<!-- Cart Section -->
<section class="cart-section">
  <div class="container">
    <div class="cart-layout">

      <!-- ── CART ITEMS ── -->
      <div class="cart-card">
        <div class="cart-card-head">
          <h2>Cart Items</h2>
          <span><?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($cartItems)): ?>
          <div style="text-align:center;padding:72px 32px;">
            <div style="font-size:4rem;opacity:0.25;margin-bottom:20px;">🛒</div>
            <h3 style="color:var(--primary);margin-bottom:8px;">Your cart is empty</h3>
            <p style="color:var(--text-light);margin-bottom:24px;">Looks like you haven't added anything yet.</p>
            <a href="<?= SITE_URL ?>/index.php" class="btn btn-dark btn-lg">Start Shopping</a>
          </div>
        <?php else: ?>
          <div class="cart-table-wrap">
            <table class="cart-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cartItems as $pid => $item): ?>
                  <?php
                  $imgSrc = $item['image']
                    ? (str_starts_with($item['image'], 'http') ? $item['image'] : UPLOADS_URL . $item['image'])
                    : 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=200';
                  ?>
                  <tr>
                    <td>
                      <div class="cart-product">
                        <div class="cart-product-img">
                          <img src="<?= $imgSrc ?>" alt="<?= h($item['name']) ?>" loading="lazy" />
                        </div>
                        <div class="cart-product-info">
                          <h4><?= h($item['name']) ?></h4>
                          <p><?= price($item['price']) ?> each</p>
                        </div>
                      </div>
                    </td>
                    <td><span class="item-price"><?= price($item['price']) ?></span></td>
                    <td>
                      <!-- Qty update form -->
                      <form method="post" action="<?= SITE_URL ?>/cart_action.php" style="display:inline">
                        <input type="hidden" name="action"     value="update" />
                        <input type="hidden" name="product_id" value="<?= (int)$pid ?>" />
                        <div class="qty-control">
                          <button type="submit" name="qty" value="<?= max(1, $item['qty'] - 1) ?>"
                                  class="qty-btn">−</button>
                          <span class="qty-value"><?= (int)$item['qty'] ?></span>
                          <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>"
                                  class="qty-btn">+</button>
                        </div>
                      </form>
                    </td>
                    <td><span class="item-subtotal"><?= price($item['price'] * $item['qty']) ?></span></td>
                    <td>
                      <form method="post" action="<?= SITE_URL ?>/cart_action.php">
                        <input type="hidden" name="action"     value="remove" />
                        <input type="hidden" name="product_id" value="<?= (int)$pid ?>" />
                        <button type="submit" class="remove-btn" title="Remove">✕</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="cart-card-foot">
            <a href="<?= SITE_URL ?>/index.php" class="back-link">← Continue Shopping</a>
            <form method="post" action="<?= SITE_URL ?>/cart_action.php" style="display:inline">
              <input type="hidden" name="action" value="clear" />
              <button type="submit" class="btn btn-ghost btn-sm"
                      onclick="return confirm('Clear all items?')">Clear Cart</button>
            </form>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── ORDER SUMMARY ── -->
      <div class="summary-box">
        <div class="summary-head"><h3>Order Summary</h3></div>

        <div class="summary-body">
          <div class="summary-row">
            <span>Subtotal (<?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>)</span>
            <span><?= price($subtotal) ?></span>
          </div>
          <?php if ($discount > 0): ?>
            <div class="summary-row discount">
              <span>Discount (<?= h($promoCode) ?>)</span>
              <span>−<?= price($discount) ?></span>
            </div>
          <?php endif; ?>
          <div class="summary-row">
            <span>Shipping</span>
            <span <?= $shipping === 0.0 ? 'style="color:var(--success);font-weight:700;"' : '' ?>>
              <?= $shipping === 0.0 ? 'Free' : price($shipping) ?>
            </span>
          </div>
          <div class="summary-row">
            <span>Estimated Tax (8%)</span>
            <span><?= price($tax) ?></span>
          </div>
        </div>

        <div class="summary-total">
          <span>Total</span>
          <span><?= price($total) ?></span>
        </div>

        <div class="summary-foot">
          <!-- Promo code form -->
          <form method="post" action="cart.php">
            <div class="promo-row">
              <input type="text" name="promo_code"
                     placeholder="Promo code"
                     value="<?= h($promoCode) ?>" />
              <button type="submit" name="apply_promo">Apply</button>
            </div>
            <?php if ($promoApplied): ?>
              <p style="font-size:0.78rem;color:var(--success);margin-top:6px;">✓ Promo code applied!</p>
            <?php elseif (!empty($promoCode) && !$promoApplied && $discount === 0.0): ?>
              <p style="font-size:0.78rem;color:var(--error);margin-top:6px;">Invalid promo code.</p>
            <?php endif; ?>
          </form>

          <?php if (!empty($cartItems)): ?>
            <a href="#" class="btn btn-primary btn-full btn-lg">Proceed to Checkout</a>
          <?php endif; ?>

          <div class="secure-note">
            <span>🔒</span> Secure checkout — SSL encrypted
          </div>
        </div>
      </div>

    </div>

    <!-- ── UPSELL PRODUCTS ── -->
    <?php if (!empty($upsellProducts)): ?>
    <div style="margin-top:64px;">
      <div class="section-header" style="margin-bottom:32px;">
        <h2 class="section-title">You Might Also Like</h2>
        <p class="section-subtitle">Customers who shopped here also loved</p>
      </div>
      <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
        <?php foreach ($upsellProducts as $product): ?>
          <?php include __DIR__ . '/includes/product_card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
