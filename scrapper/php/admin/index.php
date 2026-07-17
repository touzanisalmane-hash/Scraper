<?php
/**
 * admin/index.php — Dashboard overview.
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';
requireAdmin();

$adminTitle  = 'Dashboard';
$adminActive = 'dashboard';

$totalProducts  = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories= (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalMessages  = (int) db()->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
$featuredCount  = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_featured = 1')->fetchColumn();

// Latest 5 products
$latestProducts = db()->query(
    'SELECT p.*, c.name AS category_name FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC LIMIT 5'
)->fetchAll();

// Latest 5 messages
$latestMessages = db()->query(
    'SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Stat cards -->
<div class="stat-cards">
  <div class="stat-box">
    <div class="label">Total Products</div>
    <div class="value"><?= $totalProducts ?></div>
    <div class="trend">↑ Active catalogue</div>
  </div>
  <div class="stat-box">
    <div class="label">Categories</div>
    <div class="value"><?= $totalCategories ?></div>
    <div class="trend">Product groups</div>
  </div>
  <div class="stat-box">
    <div class="label">Featured</div>
    <div class="value"><?= $featuredCount ?></div>
    <div class="trend">Shown on homepage</div>
  </div>
  <div class="stat-box">
    <div class="label">Messages</div>
    <div class="value"><?= $totalMessages ?></div>
    <div class="trend">Contact submissions</div>
  </div>
</div>

<!-- Two-column layout -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <!-- Latest products -->
  <div class="admin-card">
    <div class="card-head">
      <h3>Latest Products</h3>
      <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-sm btn-dark">Manage</a>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Name</th><th>Category</th><th>Price</th><th>Badge</th></tr>
      </thead>
      <tbody>
        <?php if (empty($latestProducts)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--text-light);padding:24px;">No products yet.</td></tr>
        <?php else: ?>
          <?php foreach ($latestProducts as $p): ?>
            <tr>
              <td style="font-weight:600;"><?= h($p['name']) ?></td>
              <td><?= h($p['category_name']) ?></td>
              <td>$<?= number_format($p['price'],2) ?></td>
              <td><span class="badge badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Latest messages -->
  <div class="admin-card">
    <div class="card-head">
      <h3>Recent Messages</h3>
      <a href="<?= SITE_URL ?>/admin/contacts.php" class="btn btn-sm btn-dark">View All</a>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Name</th><th>Subject</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php if (empty($latestMessages)): ?>
          <tr><td colspan="3" style="text-align:center;color:var(--text-light);padding:24px;">No messages yet.</td></tr>
        <?php else: ?>
          <?php foreach ($latestMessages as $m): ?>
            <tr>
              <td style="font-weight:600;"><?= h($m['name']) ?></td>
              <td><?= h(mb_substr($m['subject'], 0, 30)) ?>...</td>
              <td style="white-space:nowrap;color:var(--text-light);font-size:0.78rem;"><?= date('d M', strtotime($m['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Quick Actions -->
<div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
  <a href="<?= SITE_URL ?>/admin/product_form.php" class="btn btn-primary">+ Add Product</a>
  <a href="<?= SITE_URL ?>/admin/categories.php"   class="btn btn-dark">Manage Categories</a>
  <a href="<?= SITE_URL ?>/index.php" target="_blank" class="btn btn-success">View Store</a>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
