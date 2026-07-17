<?php
/**
 * admin/categories.php — Add, edit, delete categories.
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';
requireAdmin();

$adminTitle  = 'Categories';
$adminActive = 'categories';

$errors = [];
$editCat = null;

// ── Handle DELETE ─────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $dId = (int)$_GET['delete'];
    // Check for linked products
    $count = (int) db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?')
                       ->execute([$dId]) ?: 0;
    $stmt2 = db()->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
    $stmt2->execute([$dId]);
    $count = (int)$stmt2->fetchColumn();

    if ($count > 0) {
        setFlash('error', "Cannot delete: $count product(s) still use this category.");
    } else {
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$dId]);
        setFlash('success', 'Category deleted.');
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// ── Load category for editing ─────────────────────────────────
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $editCat = $stmt->fetch();
}

// ── Handle POST (add / update) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId   = (int)($_POST['cat_id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $slug    = slugify(!empty($_POST['slug']) ? $_POST['slug'] : $name);

    if (empty($name)) $errors[] = 'Category name is required.';

    if (empty($errors)) {
        // Unique slug check
        $uStmt = db()->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
        $uStmt->execute([$slug, $catId]);
        if ($uStmt->fetch()) $slug .= '-' . time();

        if ($catId > 0) {
            db()->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?')
               ->execute([$name, $slug, $catId]);
            setFlash('success', 'Category updated.');
        } else {
            db()->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)')
               ->execute([$name, $slug]);
            setFlash('success', 'Category added.');
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

$categories = db()->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start;max-width:900px;">

  <!-- ── Category list ── -->
  <div class="admin-card">
    <div class="card-head"><h3>All Categories</h3></div>
    <table class="admin-table">
      <thead>
        <tr><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td style="font-weight:600;"><?= h($cat['name']) ?></td>
            <td style="color:var(--text-light);font-size:0.8rem;"><?= h($cat['slug']) ?></td>
            <td><?= (int)$cat['product_count'] ?></td>
            <td>
              <a href="categories.php?edit=<?= (int)$cat['id'] ?>" class="btn btn-sm btn-dark">Edit</a>
              <a href="categories.php?delete=<?= (int)$cat['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this category?')">Del</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── Add / Edit form ── -->
  <div class="admin-card">
    <div class="card-head">
      <h3><?= $editCat ? 'Edit Category' : 'Add Category' ?></h3>
      <?php if ($editCat): ?>
        <a href="categories.php" class="btn btn-sm btn-dark">+ New</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?><p style="margin:0;"><?= h($e) ?></p><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <form method="post" action="categories.php">
        <input type="hidden" name="cat_id" value="<?= $editCat ? (int)$editCat['id'] : 0 ?>" />
        <div class="form-group">
          <label>Category Name *</label>
          <input type="text" name="name" class="form-control"
                 value="<?= h($editCat['name'] ?? $_POST['name'] ?? '') ?>"
                 placeholder="e.g. Electronics" required />
        </div>
        <div class="form-group">
          <label>Slug <span style="font-weight:400;color:var(--text-light);">(auto-generated)</span></label>
          <input type="text" name="slug" class="form-control"
                 value="<?= h($editCat['slug'] ?? '') ?>"
                 placeholder="Leave blank to auto-generate" />
        </div>
        <button type="submit" class="btn btn-primary">
          <?= $editCat ? 'Save Changes' : 'Add Category' ?>
        </button>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
