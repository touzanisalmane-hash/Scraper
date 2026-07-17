<?php
/**
 * search.php — Search products and filter by category / badge.
 * Query params: q (search term), category (int), badge (sale|new|hot)
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$pageTitle  = 'All Products';
$activePage = 'home';

// ── Input params ──────────────────────────────────────────────
$q          = trim($_GET['q']        ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$badge      = in_array($_GET['badge'] ?? '', ['sale','new','hot']) ? $_GET['badge'] : '';

// ── Build query dynamically ───────────────────────────────────
$where  = ['1=1'];
$params = [];

if ($q !== '') {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($categoryId > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $categoryId;
}

if ($badge !== '') {
    $where[]  = 'p.badge = ?';
    $params[] = $badge;
}

$sql = 'SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY p.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ── All categories for filter sidebar ────────────────────────
$categories = db()->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name'
)->fetchAll();

$pageTitle = $q ? 'Search: ' . h($q) : ($badge ? ucfirst($badge) . ' Products' : 'All Products');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <h1><?= h($pageTitle) ?></h1>
  <p><?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found</p>
</div>

<section style="padding:64px 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:240px 1fr;gap:40px;align-items:start;">

      <!-- ── FILTERS SIDEBAR ── -->
      <aside>
        <!-- Search box -->
        <form method="get" action="search.php"
              style="background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px;margin-bottom:20px;box-shadow:var(--shadow-sm);">
          <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:12px;">Search</h4>
          <div style="display:flex;">
            <input type="text" name="q" value="<?= h($q) ?>"
                   placeholder="Search products..."
                   style="flex:1;padding:9px 12px;border:1px solid var(--border);border-right:none;border-radius:var(--radius-sm) 0 0 var(--radius-sm);font-size:0.85rem;outline:none;font-family:var(--font-main);" />
            <button type="submit"
                    style="padding:9px 14px;background:var(--primary);color:#fff;border:none;border-radius:0 var(--radius-sm) var(--radius-sm) 0;cursor:pointer;font-size:0.85rem;font-family:var(--font-main);">
              &#128269;
            </button>
          </div>
          <?php if ($categoryId): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
          <?php if ($badge):       ?><input type="hidden" name="badge"    value="<?= h($badge) ?>"><?php endif; ?>
        </form>

        <!-- Category filter -->
        <div style="background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px;margin-bottom:20px;box-shadow:var(--shadow-sm);">
          <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:14px;">Categories</h4>
          <ul style="list-style:none;padding:0;margin:0;">
            <li style="margin-bottom:8px;">
              <a href="search.php<?= $q ? '?q='.urlencode($q) : '' ?>"
                 style="display:flex;justify-content:space-between;font-size:0.88rem;font-weight:<?= $categoryId===0?'700':'500' ?>;color:<?= $categoryId===0?'var(--primary)':'var(--text-medium)' ?>;text-decoration:none;padding:6px 8px;border-radius:var(--radius-sm);<?= $categoryId===0?'background:rgba(26,58,92,0.06);':'' ?>">
                <span>All Categories</span>
              </a>
            </li>
            <?php foreach ($categories as $cat): ?>
              <li style="margin-bottom:4px;">
                <a href="search.php?category=<?= (int)$cat['id'] ?><?= $q ? '&q='.urlencode($q) : '' ?>"
                   style="display:flex;justify-content:space-between;font-size:0.88rem;font-weight:<?= $categoryId===$cat['id']?'700':'400' ?>;color:<?= $categoryId===$cat['id']?'var(--primary)':'var(--text-medium)' ?>;text-decoration:none;padding:6px 8px;border-radius:var(--radius-sm);<?= $categoryId===$cat['id']?'background:rgba(26,58,92,0.06);':'' ?>">
                  <span><?= h($cat['name']) ?></span>
                  <span style="color:var(--text-light);font-size:0.78rem;"><?= (int)$cat['product_count'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Badge filter -->
        <div style="background:var(--bg-white);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px;box-shadow:var(--shadow-sm);">
          <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-light);margin-bottom:14px;">Filter By</h4>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ([''=>'All Products','sale'=>'On Sale','new'=>'New Arrivals','hot'=>'Hot Picks'] as $val => $label): ?>
              <a href="search.php?badge=<?= urlencode($val) ?><?= $categoryId?'&category='.$categoryId:'' ?>"
                 style="display:block;font-size:0.88rem;font-weight:<?= $badge===$val?'700':'400' ?>;color:<?= $badge===$val?'var(--primary)':'var(--text-medium)' ?>;text-decoration:none;padding:6px 8px;border-radius:var(--radius-sm);<?= $badge===$val?'background:rgba(26,58,92,0.06);':'' ?>">
                <?= $label ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

      <!-- ── PRODUCTS GRID ── -->
      <div>
        <?php if (empty($products)): ?>
          <div style="text-align:center;padding:80px 32px;background:var(--bg-white);border-radius:var(--radius-lg);border:1px solid var(--border);">
            <div style="font-size:3.5rem;opacity:0.25;margin-bottom:20px;">🔍</div>
            <h3 style="color:var(--primary);margin-bottom:8px;">No products found</h3>
            <p style="color:var(--text-light);margin-bottom:24px;">Try a different search term or browse by category.</p>
            <a href="search.php" class="btn btn-dark">Browse All</a>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $product): ?>
              <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
