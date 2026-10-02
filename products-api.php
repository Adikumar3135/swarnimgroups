<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user = loggedUser();

if ($action === 'wishlist-toggle') {
    requirePost();
    requireCsrf();
    if (!$user) jsonResponse(false, 'Please login to use your wishlist.', [], 401);
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId < 1) jsonResponse(false, 'Invalid product.', [], 422);
    $check = db()->prepare('SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?');
    $check->execute([(int)$user['id'], $productId]);
    $existing = $check->fetchColumn();
    if ($existing) {
        db()->prepare('DELETE FROM wishlist_items WHERE id = ?')->execute([(int)$existing]);
        jsonResponse(true, 'Removed from wishlist.', ['wishlisted' => false]);
    }
    $product = db()->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
    $product->execute([$productId]);
    if (!$product->fetchColumn()) jsonResponse(false, 'Product not found.', [], 404);
    db()->prepare('INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)')->execute([(int)$user['id'], $productId]);
    jsonResponse(true, 'Added to wishlist.', ['wishlisted' => true]);
}

if ($action === 'state') {
    $wish = [];
    $cart = [];
    if ($user) {
        $q = db()->prepare('SELECT product_id FROM wishlist_items WHERE user_id = ?');
        $q->execute([(int)$user['id']]);
        $wish = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
        $q = db()->prepare('SELECT product_id, quantity FROM cart_items WHERE user_id = ?');
        $q->execute([(int)$user['id']]);
        foreach ($q->fetchAll() as $row) $cart[(string)$row['product_id']] = (int)$row['quantity'];
    }
    jsonResponse(true, '', ['wishlist' => $wish, 'cart' => $cart, 'user' => $user]);
}

if ($action === 'cart-sync') {
    requirePost();
    requireCsrf();
    if (!$user) jsonResponse(true, 'Guest cart kept locally.', ['saved' => false]);
    $items = json_decode($_POST['items'] ?? '{}', true);
    if (!is_array($items)) jsonResponse(false, 'Invalid cart data.', [], 422);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($items as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = max(0, min(99, (int)$quantity));
            if ($productId < 1) continue;
            $exists = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
            $exists->execute([$productId]);
            if (!$exists->fetchColumn()) continue;
            if ($quantity === 0) {
                $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?')->execute([(int)$user['id'], $productId]);
            } else {
                $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)')->execute([(int)$user['id'], $productId, $quantity]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(false, 'Could not save cart.', [], 500);
    }
    jsonResponse(true, 'Cart saved.', ['saved' => true]);
}

jsonResponse(false, 'Unknown action.', [], 400);
