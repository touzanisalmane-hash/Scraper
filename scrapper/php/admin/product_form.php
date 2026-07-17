<?php
/**
 * admin/product_form.php — Add or edit a product.
 * GET ?id=X  → edit mode
 * POST       → save (insert or update)
 */

declare(strict_types=1);
require_once __DIR__ . '/../config.php';
requireAdmin();

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit  = $id > 0;
$errors  = [];
$product = [
    'name' => '', 'slug' => '', 'description' => '',
    'price' => '', 'old_price' => '', 'badge' => 'none',
    'image' => '', 'category_id' => 0, 'stock' => 100, 'is_featured' => 0,
];

// ── Fetch existing product (edit mode) ────────────────────────
if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) redirect(SITE_URL . '/admin/products.php');
    $product = $row;
}

// ── Fetch categories for dropdown ─────────────────────────────
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// ── Handle POST (save) ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $oldPrice    = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $badge       = in_array($_POST['badge'], ['none','sale','new','hot']) ? $_POST['badge'] : 'none';
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $stock       = max(0, (int)($_POST['stock'] ?? 0));
    $isFeatured  = isset($_POST['is_featured']) ? 1 : 0;
    $slug        = slugify(!empty($_POST['slug']) ? $_POST['slug'] : $name);

    // Validation
    if (empty($name))       $errors[] = 'Product name is required.';
    if ($price <= 0)        $errors[] = 'Price must be greater than zero.';
    if ($categoryId === 0)  $errors[] = 'Please select a category.';

    // Unique slug check (exclude self on edit)
    if (empty($errors)) {
        $slugStmt = db()->prepare('SELECT id FROM products WHERE slug = ? AND id != ?');
        $slugStmt->execute([$slug, $isEdit ? $id : 0]);
        if ($slugStmt->fetch()) {
            $slug .= '-' . time();
        }
    }

    // Image upload
    $imagePath = $isEdit ? ($product['image'] ?? '') : '';
    if (!empty($_FILES['image']['tmp_name'])) {
        $file     = $_FILES['image'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','webp','gif'];
        $maxSize  = 5 * 1024 * 1024; // 5MB

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG, WEBP or GIF.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image must be under 5MB.';
        } else {
            $newFileName = uniqid('product_', true) . '.' . $ext;
            if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);
            if (move_uploaded_file($file['tmp_name'], UPLOADS_DIR . $newFileName)) {
                // Delete old file if editing
                if ($isEdit && $imagePath && !str_starts_with($imagePath, 'http')
                    && file_exists(UPLOADS_DIR . $imagePath)) {
                    unlink(UPLOADS_DIR . $imagePath);
                }
                $imagePath = $newFileName;
            } else {
                $errors[] = 'Failed to upload image. Check uploads/ folder permissions.';
            }
        }
    }

    // Merge postback values
    $product = array_merge($product, compact('name','description','price','badge','categoryId','stock','isFeatured','slug'));
    $product['old_price']   = $oldPrice;
    $product['category_id'] = $categoryId;
    $product['is_featured'] = $isFeatured;

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = db()->prepare(
                'UPDATE products SET
                    name=?, slug=?, description=?, price=?, old_price=?, badge=?,
                    image=?, category_id=?, stock=?, is_featured=?
                 WHERE id=?'
            );
            $stmt->execute([$name, $slug, $description, $price, $oldPrice, $badge,
                             $imagePath, $categoryId, $stock, $isFeatured, $id]);
            setFlash('success', 'Product updated successfully.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO products
                    (name, slug, description, price, old_price, badge, image, category_id, stock, is_featured)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $slug, $description, $price, $oldPrice, $badge,
                             $imagePath, $categoryId, $stock, $isFeatured]);
        }
        redirect(SITE_URL . '/admin/products.php');
    }
}

$adminTitle  = $isEdit ? 'Edit Product' : 'Add Product';
$adminActive = 'products';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="max-width:860px;">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?>
        <p style="margin:0;"><?= h($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="card-head">
      <h3><?= $isEdit ? 'Edit: ' . h($product['name']) : 'New Product' ?></h3>
      <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-sm btn-dark">← Back</a>
    </div>
    <div class="card-body">
      <form method="post" action="" enctype="multipart/form-data">

        <div class="form-row">
          <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= h($product['name']) ?>" placeholder="e.g. Urban Runner Sneakers" required />
          </div>
          <div class="form-group">
            <label>URL Slug</label>
            <input type="text" name="slug" class="form-control"
                   value="<?= h($product['slug']) ?>" placeholder="auto-generated from name" />
            <small style="color:var(--text-light);font-size:0.75rem;">Leave blank to auto-generate.</small>
          </div>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control"
                    placeholder="Describe the product..."><?= h($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Price * ($)</label>
            <input type="number" name="price" class="form-control" step="0.01" min="0"
                   value="<?= h($product['price']) ?>" placeholder="0.00" required />
          </div>
          <div class="form-group">
            <label>Old Price ($) <span style="font-weight:400;color:var(--text-light);">(optional — shows strikethrough)</span></label>
            <input type="number" name="old_price" class="form-control" step="0.01" min="0"
                   value="<?= h($product['old_price'] ?? '') ?>" placeholder="Leave blank for none" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Category *</label>
            <select name="category_id" class="form-control" required>
              <option value="">— Select Category —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= (int)$product['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                  <?= h($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Badge</label>
            <select name="badge" class="form-control">
              <?php foreach (['none','sale','new','hot'] as $b): ?>
                <option value="<?= $b ?>" <?= $product['badge'] === $b ? 'selected' : '' ?>>
                  <?= ucfirst($b) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Stock Units</label>
            <input type="number" name="stock" class="form-control" min="0"
                   value="<?= (int)$product['stock'] ?>" />
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:2px;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.88rem;font-weight:600;">
              <input type="checkbox" name="is_featured" value="1"
                     <?= $product['is_featured'] ? 'checked' : '' ?>
                     style="width:18px;height:18px;accent-color:var(--primary);" />
              Show on Homepage (Featured)
            </label>
          </div>
        </div>

        <!-- Image upload -->
        <div class="form-group">
          <label>Product Image</label>
          <?php if ($isEdit && !empty($product['image'])): ?>
            <?php $thumb = str_starts_with($product['image'], 'http') ? $product['image'] : UPLOADS_URL . $product['image']; ?>
            <div style="margin-bottom:10px;">
              <img src="<?= $thumb ?>" alt="Current image"
                   style="width:100px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #e0e6ef;" />
              <p style="font-size:0.76rem;color:var(--text-light);margin-top:4px;">Current image — upload a new one to replace it.</p>
            </div>
          <?php endif; ?>
          <input type="file" name="image" class="form-control" accept="image/*"
                 style="padding:8px;" />
          <small style="color:var(--text-light);font-size:0.75rem;">JPG, PNG, WEBP or GIF. Max 5MB.</small>
        </div>

        <div style="display:flex;gap:12px;margin-top:8px;">
          <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:0.95rem;">
            <?= $isEdit ? 'Save Changes' : 'Add Product' ?>
          </button>
          <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-dark">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
