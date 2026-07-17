<?php
/**
 * admin/products.php — List all products with edit/delete actions.
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';
requireAdmin();

// ── Handle DELETE ─────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Fetch image to delete file
    $stmt = db()->prepare('SELECT image FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['image'] && !str_starts_with($row['image'], 'http') && file_exists(UPLOADS_DIR . $row['image'])) {
        unlink(UPLOADS_DIR . $row['image']);
    }
    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Product deleted successfully.');
    redirect(SITE_URL . '/admin/products.php');
}

// ── Handle TOGGLE FEATURED ────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = db()->prepare('UPDATE products SET is_featured = 1 - is_featured WHERE id = ?');
    $stmt->execute([(int)$_GET['toggle']]);
    redirect(SITE_URL . '/admin/products.php');
}

$products = db()->query(
    'SELECT p.*, c.name AS category_name FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC'
)->fetchAll();

$adminTitle  = 'Products';
$adminActive = 'products';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <p style="color:var(--text-light);font-size:0.88rem;"><?= count($products) ?> products total</p>
  <a href="<?= SITE_URL ?>/admin/product_form.php" class="btn btn-primary">+ Add Product</a>
</div>

<div class="admin-card">
  <div class="card-head">
    <h3>All Products</h3>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:60px;">Image</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Badge</th>
          <th>Featured</th>
          <th>Stock</th>
          <th style="width:120px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">
              No products yet. <a href="<?= SITE_URL ?>/admin/product_form.php" style="color:var(--primary);">Add one now.</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <?php
            $thumb = $p['image']
              ? (str_starts_with($p['image'], 'http') ? $p['image'] : UPLOADS_URL . $p['image'])
              : 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=100';
            ?>
            <tr>
              <td>
                <img src="<?= $thumb ?>" alt="<?= h($p['name']) ?>"
                     style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #e0e6ef;" />
              </td>
              <td style="font-weight:600;max-width:200px;">
                <a href="<?= SITE_URL ?>/product.php?slug=<?= h($p['slug']) ?>"
                   target="_blank" style="color:var(--primary);text-decoration:none;">
                  <?= h($p['name']) ?>
                </a>
              </td>
              <td><?= h($p['category_name']) ?></td>
              <td>
                $<?= number_format($p['price'], 2) ?>
                <?php if ($p['old_price']): ?>
                  <br/><small style="text-decoration:line-through;color:var(--text-light);">$<?= number_format($p['old_price'],2) ?></small>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-<?= h($p['badge']) ?>"><?= h($p['badge']) ?></span></td>
              <td>
                <a href="products.php?toggle=<?= (int)$p['id'] ?>"
                   style="color:<?= $p['is_featured'] ? 'var(--success)' : 'var(--text-light)' ?>;font-size:1.2rem;text-decoration:none;">
                  <?= $p['is_featured'] ? '★' : '☆' ?>
                </a>
              </td>
              <td><?= (int)$p['stock'] ?></td>
              <td>
                <a href="<?= SITE_URL ?>/admin/product_form.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-dark">Edit</a>
                <a href="products.php?delete=<?= (int)$p['id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this product?')">Del</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
