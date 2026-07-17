<?php
/**
 * cart_action.php — Handles all cart mutations (add / update / remove).
 * Called via POST form submissions; always redirects back.
 */

declare(strict_types=1);
require_once __DIR__ . '/config.php';

$action    = $_POST['action']     ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = max(1, (int)($_POST['qty'] ?? 1));

switch ($action) {

    case 'add':
        if ($productId > 0) {
            $stmt = db()->prepare('SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if ($product) {
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['qty'] += $qty;
                } else {
                    $_SESSION['cart'][$productId] = [
                        'id'    => $product['id'],
                        'name'  => $product['name'],
                        'price' => (float)$product['price'],
                        'image' => $product['image'],
                        'qty'   => $qty,
                    ];
                }
                setFlash('success', h($product['name']) . ' added to your cart.');
            }
        }
        break;

    case 'update':
        // Update quantity or remove if qty <= 0
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            if ($qty > 0) {
                $_SESSION['cart'][$productId]['qty'] = $qty;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$productId]);
        setFlash('success', 'Item removed from cart.');
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        setFlash('success', 'Cart cleared.');
        break;
}

// Redirect back to referring page (or cart)
$back = $_SERVER['HTTP_REFERER'] ?? SITE_URL . '/cart.php';
redirect($back);
