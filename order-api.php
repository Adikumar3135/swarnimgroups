<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

const GST_RATE = 18.00;

function couponState(PDO $pdo, string $code, int $userId, float $subtotal, bool $lock = false): array {
    $sql = 'SELECT * FROM coupons WHERE UPPER(code) = UPPER(?)' . ($lock ? ' FOR UPDATE' : '');
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) return ['valid' => false, 'message' => 'Coupon code not found.'];
    if ((int)$coupon['active'] !== 1) return ['valid' => false, 'message' => 'This coupon is not active.'];

    $now = time();
    if ($coupon['starts_at'] && strtotime((string)$coupon['starts_at']) > $now) {
        return ['valid' => false, 'message' => 'This coupon is not active yet.'];
    }
    if ($coupon['expires_at'] && strtotime((string)$coupon['expires_at']) < $now) {
        return ['valid' => false, 'message' => 'This coupon has expired.'];
    }
    if ($coupon['usage_limit'] !== null && (int)$coupon['usage_count'] >= (int)$coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
    }

    if ($coupon['per_user_limit'] !== null) {
        $usedStmt = $pdo->prepare('SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = ? AND user_id = ?');
        $usedStmt->execute([(int)$coupon['id'], $userId]);
        if ((int)$usedStmt->fetchColumn() >= (int)$coupon['per_user_limit']) {
            return ['valid' => false, 'message' => 'You have already used this coupon the maximum allowed times.'];
        }
    }

    $minimum = (float)$coupon['min_order_amount'];
    if ($subtotal < $minimum) {
        return ['valid' => false, 'message' => 'Minimum order value for this coupon is ₹' . number_format($minimum, 2) . '.'];
    }

    $discount = 0.0;
    if ($coupon['discount_type'] === 'percent') {
        $discount = $subtotal * ((float)$coupon['discount_value'] / 100);
        if ($coupon['max_discount'] !== null) $discount = min($discount, (float)$coupon['max_discount']);
    } else {
        $discount = (float)$coupon['discount_value'];
        if ($coupon['max_discount'] !== null) $discount = min($discount, (float)$coupon['max_discount']);
    }

    $discount = round(min($discount, $subtotal), 2);
    if ($discount <= 0) return ['valid' => false, 'message' => 'This coupon does not apply to the current order.'];

    return [
        'valid' => true,
        'coupon' => $coupon,
        'discount' => $discount,
        'message' => 'Coupon applied successfully.'
    ];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'check-pincode') {
    $pin = preg_replace('/\D+/', '', (string)($_GET['pincode'] ?? $_POST['pincode'] ?? ''));
    if (strlen($pin) !== 6) {
        jsonResponse(false, 'Enter a valid 6-digit pincode.', ['available' => false], 422);
    }

    $stmt = db()->prepare('SELECT pincode, city, state, note FROM delivery_pincodes WHERE pincode = ? AND active = 1 LIMIT 1');
    $stmt->execute([$pin]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse(true, 'Delivery is not available on this pincode.', [
            'available' => false,
            'pincode' => $pin,
            'support_phone' => envv('SUPPORT_PHONE', '+91 9955056900')
        ]);
    }

    jsonResponse(true, 'Delivery available.', [
        'available' => true,
        'pincode' => $row['pincode'],
        'city' => $row['city'],
        'state' => $row['state'],
        'note' => $row['note']
    ]);
}

if ($action === 'list-coupons') {
    $user = loggedUser();
    if (!$user) jsonResponse(false, 'Please login before using coupons.', ['login_required' => true], 401);

    $stmt = db()->prepare('SELECT id,code,title,description,discount_type,discount_value,max_discount,min_order_amount,starts_at,expires_at,usage_limit,usage_count,per_user_limit,active FROM coupons WHERE active = 1 ORDER BY created_at DESC, id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $usedStmt = db()->prepare('SELECT coupon_id, COUNT(*) used_count FROM coupon_redemptions WHERE user_id = ? GROUP BY coupon_id');
    $usedStmt->execute([(int)$user['id']]);
    $used = [];
    foreach ($usedStmt->fetchAll() as $r) $used[(int)$r['coupon_id']] = (int)$r['used_count'];

    $now = time();
    $coupons = array_map(function(array $c) use ($used, $now): array {
        $userUsed = $used[(int)$c['id']] ?? 0;
        $future = $c['starts_at'] && strtotime((string)$c['starts_at']) > $now;
        $expired = $c['expires_at'] && strtotime((string)$c['expires_at']) < $now;
        $globalLimit = $c['usage_limit'] !== null && (int)$c['usage_count'] >= (int)$c['usage_limit'];
        $userLimit = $c['per_user_limit'] !== null && $userUsed >= (int)$c['per_user_limit'];
        $c['used_by_user'] = $userUsed;
        $c['available_now'] = !$future && !$expired && !$globalLimit && !$userLimit;
        return $c;
    }, $rows);

    jsonResponse(true, 'Coupons loaded.', ['coupons' => $coupons]);
}

if ($action === 'validate-coupon') {
    requirePost();
    requireCsrf();
    $user = loggedUser();
    if (!$user) jsonResponse(false, 'Please login before using coupons.', ['login_required' => true], 401);

    $code = strtoupper(trim((string)($_POST['code'] ?? '')));
    $subtotal = round(max(0, (float)($_POST['subtotal'] ?? 0)), 2);
    if ($code === '') jsonResponse(false, 'Enter a coupon code.', [], 422);

    $result = couponState(db(), $code, (int)$user['id'], $subtotal);
    if (!$result['valid']) jsonResponse(false, $result['message'], [], 422);

    $coupon = $result['coupon'];
    $discount = (float)$result['discount'];
    $taxable = round(max(0, $subtotal - $discount), 2);
    $tax = round($taxable * GST_RATE / 100, 2);
    $total = round($taxable + $tax, 2);

    jsonResponse(true, $result['message'], [
        'code' => $coupon['code'],
        'title' => $coupon['title'],
        'discount' => $discount,
        'tax_rate' => GST_RATE,
        'tax' => $tax,
        'total' => $total
    ]);
}


if ($action === 'cancel-order') {
    requirePost();
    requireCsrf();
    $user = loggedUser();
    if (!$user) jsonResponse(false, 'Please login first.', [], 401);
    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId < 1) jsonResponse(false, 'Invalid order.', [], 422);
    $stmt = db()->prepare("SELECT id, order_number, order_status FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$orderId, (int)$user['id']]);
    $order = $stmt->fetch();
    if (!$order) jsonResponse(false, 'Order not found.', [], 404);
    if (!in_array($order['order_status'], ['accepted', 'processing'], true)) {
        jsonResponse(false, 'This order can no longer be cancelled.', [], 422);
    }
    $pdo = db();
    try {
        $pdo->beginTransaction();
        $itemsStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL');
        $itemsStmt->execute([$orderId]);
        $restore = $pdo->prepare("UPDATE products SET stock = stock + ?, stock_status = CASE WHEN stock + ? <= 0 THEN 'out_of_stock' WHEN stock + ? <= 5 THEN 'low_stock' ELSE 'in_stock' END WHERE id = ?");
        foreach ($itemsStmt->fetchAll() as $item) {
            $q=(int)$item['quantity'];
            $restore->execute([$q,$q,$q,(int)$item['product_id']]);
        }
        $upd=$pdo->prepare("UPDATE orders SET order_status='cancelled' WHERE id=? AND user_id=? AND order_status IN ('accepted','processing')");
        $upd->execute([$orderId,(int)$user['id']]);
        if ($upd->rowCount() !== 1) throw new RuntimeException('The order status changed. Please refresh and try again.');
        $pdo->commit();
        jsonResponse(true, 'Order cancelled successfully.', ['order_number'=>$order['order_number'],'order_status'=>'cancelled']);
    } catch(Throwable $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(false, $e->getMessage() ?: 'Could not cancel order.', [], 422);
    }
}

if ($action === 'reorder') {
    requirePost();
    requireCsrf();
    $user = loggedUser();
    if (!$user) jsonResponse(false, 'Please login first.', [], 401);
    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId < 1) jsonResponse(false, 'Invalid order.', [], 422);
    $pdo=db();
    $check=$pdo->prepare('SELECT id FROM orders WHERE id=? AND user_id=? LIMIT 1');
    $check->execute([$orderId,(int)$user['id']]);
    if(!$check->fetch()) jsonResponse(false,'Order not found.',[],404);
    $items=$pdo->prepare('SELECT oi.product_id, oi.quantity, p.name, p.stock, p.status FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=? AND oi.product_id IS NOT NULL');
    $items->execute([$orderId]);
    $rows=$items->fetchAll();
    if(!$rows) jsonResponse(false,'No products are available to buy again.',[],422);
    $up=$pdo->prepare('INSERT INTO cart_items (user_id,product_id,quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity=LEAST(quantity+VALUES(quantity), 99)');
    $added=0; $skipped=[];
    foreach($rows as $r){
        $stock=(int)($r['stock']??0);
        if(($r['status']??'') !== 'active') { $skipped[]=$r['name']; continue; }
        if($stock<1){$skipped[]=$r['name'];continue;}
        $q=min((int)$r['quantity'],$stock,99);
        $up->execute([(int)$user['id'],(int)$r['product_id'],$q]);
        $added++;
    }
    if(!$added) jsonResponse(false,'Products from this order are currently unavailable.',[],422);
    jsonResponse(true,'Products added to your cart.', ['added'=>$added,'skipped'=>$skipped]);
}

if ($action === 'place-order') {
    requirePost();
    requireCsrf();

    $user = loggedUser();
    if (!$user) {
        jsonResponse(false, 'Please login before placing an order.', ['login_required' => true], 401);
    }

    $items = json_decode((string)($_POST['items'] ?? '[]'), true);
    if (!is_array($items) || !$items) {
        jsonResponse(false, 'Your cart is empty.', [], 422);
    }

    $address1 = trim((string)($_POST['address_line1'] ?? ''));
    $address2 = trim((string)($_POST['address_line2'] ?? ''));
    $landmark = trim((string)($_POST['landmark'] ?? ''));
    $city = trim((string)($_POST['city'] ?? ''));
    $state = trim((string)($_POST['state'] ?? ''));
    $pincode = preg_replace('/\D+/', '', (string)($_POST['pincode'] ?? ''));
    $paymentMethod = (string)($_POST['payment_method'] ?? '');
    $customerNote = trim((string)($_POST['customer_note'] ?? ''));
    $saveAddress = !empty($_POST['save_address']);
    $couponCode = strtoupper(trim((string)($_POST['coupon_code'] ?? '')));

    if ($address1 === '' || $city === '' || $state === '' || strlen($pincode) !== 6) {
        jsonResponse(false, 'Please complete your delivery address.', [], 422);
    }
    if (!in_array($paymentMethod, ['online', 'cod'], true)) {
        jsonResponse(false, 'Please select a payment method.', [], 422);
    }

    $pdo = db();

    $pinStmt = $pdo->prepare('SELECT pincode, city, state FROM delivery_pincodes WHERE pincode = ? AND active = 1 LIMIT 1');
    $pinStmt->execute([$pincode]);
    $pin = $pinStmt->fetch();
    if (!$pin) {
        jsonResponse(false, 'Delivery is not available on this pincode. Please call ' . envv('SUPPORT_PHONE', '+91 9955056900') . ' to book delivery.', [
            'available' => false,
            'support_phone' => envv('SUPPORT_PHONE', '+91 9955056900')
        ], 422);
    }

    $userStmt = $pdo->prepare('SELECT id, name, phone, email FROM users WHERE id = ? AND status = "active" LIMIT 1');
    $userStmt->execute([(int)$user['id']]);
    $customer = $userStmt->fetch();
    if (!$customer) jsonResponse(false, 'Your account is not available. Please login again.', [], 401);

    $cleanItems = [];
    $subtotal = 0.0;

    try {
        $pdo->beginTransaction();

        foreach ($items as $rawId => $rawQty) {
            $productId = (int)$rawId;
            $quantity = max(0, min(99, (int)$rawQty));
            if ($productId < 1 || $quantity < 1) continue;

            $productStmt = $pdo->prepare("SELECT id, name, product_pid, COALESCE(NULLIF(selling_price,0),price) AS selling_price, stock, stock_status, image_url FROM products WHERE id = ? AND status = 'active' FOR UPDATE");
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();
            if (!$product) throw new RuntimeException('One of the products in your cart is no longer available.');

            $stock = (int)$product['stock'];
            if ($stock < $quantity || $product['stock_status'] === 'out_of_stock') throw new RuntimeException($product['name'] . ' does not have enough stock.');

            $unitPrice = round((float)$product['selling_price'], 2);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal = round($subtotal + $lineTotal, 2);

            $cleanItems[] = [
                'id' => (int)$product['id'], 'name' => $product['name'], 'pid' => $product['product_pid'],
                'quantity' => $quantity, 'unit_price' => $unitPrice, 'line_total' => $lineTotal, 'image_url' => $product['image_url']
            ];
        }

        if (!$cleanItems) throw new RuntimeException('Your cart is empty.');

        $couponId = null;
        $couponDiscount = 0.0;
        $appliedCouponCode = null;
        if ($couponCode !== '') {
            $couponResult = couponState($pdo, $couponCode, (int)$customer['id'], $subtotal, true);
            if (!$couponResult['valid']) throw new RuntimeException($couponResult['message']);
            $couponId = (int)$couponResult['coupon']['id'];
            $appliedCouponCode = $couponResult['coupon']['code'];
            $couponDiscount = (float)$couponResult['discount'];
        }

        $taxableAmount = round(max(0, $subtotal - $couponDiscount), 2);
        $taxAmount = round($taxableAmount * GST_RATE / 100, 2);
        $deliveryCharge = 0.0;
        $total = round($taxableAmount + $taxAmount + $deliveryCharge, 2);
        $orderNumber = 'SW' . date('ymdHis') . strtoupper(bin2hex(random_bytes(3)));
        $paymentStatus = $paymentMethod === 'online' ? 'online' : 'cash_on_delivery';

        $orderStmt = $pdo->prepare(
            'INSERT INTO orders
            (order_number,user_id,customer_name,customer_phone,customer_email,address_line1,address_line2,landmark,city,state,pincode,subtotal,delivery_charge,total_amount,coupon_id,coupon_code,coupon_discount,tax_rate,tax_amount,payment_method,payment_status,order_status,customer_note)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $orderStmt->execute([
            $orderNumber,(int)$customer['id'],$customer['name'],$customer['phone'],$customer['email'],
            $address1,$address2 !== '' ? $address2 : null,$landmark !== '' ? $landmark : null,$city,$state,$pincode,
            $subtotal,$deliveryCharge,$total,$couponId,$appliedCouponCode,$couponDiscount,GST_RATE,$taxAmount,
            $paymentMethod,$paymentStatus,'accepted',$customerNote !== '' ? $customerNote : null
        ]);
        $orderId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,product_pid,quantity,unit_price,line_total,image_url) VALUES (?,?,?,?,?,?,?,?)');
        $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ?, stock_status = CASE WHEN stock - ? <= 0 THEN 'out_of_stock' WHEN stock - ? <= 5 THEN 'low_stock' ELSE 'in_stock' END WHERE id = ? AND stock >= ?");

        foreach ($cleanItems as $item) {
            $itemStmt->execute([$orderId,$item['id'],$item['name'],$item['pid'],$item['quantity'],$item['unit_price'],$item['line_total'],$item['image_url']]);
            $stockStmt->execute([$item['quantity'],$item['quantity'],$item['quantity'],$item['id'],$item['quantity']]);
            if ($stockStmt->rowCount() !== 1) throw new RuntimeException('Stock changed while placing your order. Please try again.');
        }

        if ($couponId !== null) {
            $pdo->prepare('UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?')->execute([$couponId]);
            $pdo->prepare('INSERT INTO coupon_redemptions (coupon_id,user_id,order_id,discount_amount) VALUES (?,?,?,?)')->execute([$couponId,(int)$customer['id'],$orderId,$couponDiscount]);
        }

        if ($saveAddress) {
            $pdo->prepare('INSERT INTO user_addresses (user_id,address_line1,address_line2,landmark,city,state,pincode,address_type,is_default) VALUES (?,?,?,?,?,?,?,?,?)')->execute([
                (int)$customer['id'],$address1,$address2 !== '' ? $address2 : null,$landmark !== '' ? $landmark : null,$city,$state,$pincode,'home',0
            ]);
        }

        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([(int)$customer['id']]);
        $pdo->commit();

        $adminEmail = envv('ORDER_NOTIFY_EMAIL', envv('MAIL_FROM', ''));
        if ($adminEmail !== '') {
            $subject = 'New Swarnim Groups Order #' . $orderNumber;
            $html = '<h2>New Order Received</h2>'
                . '<p><strong>Order:</strong> ' . htmlspecialchars($orderNumber) . '</p>'
                . '<p><strong>Customer:</strong> ' . htmlspecialchars($customer['name']) . '</p>'
                . '<p><strong>Phone:</strong> ' . htmlspecialchars($customer['phone']) . '</p>'
                . '<p><strong>Subtotal:</strong> ₹' . number_format($subtotal, 2) . '</p>'
                . '<p><strong>Coupon:</strong> ' . htmlspecialchars($appliedCouponCode ?? 'None') . ' · Discount ₹' . number_format($couponDiscount, 2) . '</p>'
                . '<p><strong>GST:</strong> ₹' . number_format($taxAmount, 2) . ' (' . GST_RATE . '%)</p>'
                . '<p><strong>Total:</strong> ₹' . number_format($total, 2) . '</p>'
                . '<p><strong>Payment:</strong> ' . htmlspecialchars(strtoupper($paymentMethod)) . '</p>'
                . '<p><strong>Address:</strong> ' . htmlspecialchars($address1 . ', ' . $city . ', ' . $state . ' - ' . $pincode) . '</p>';
            @sendSmtp($adminEmail, $subject, $html);
        }

        jsonResponse(true, 'Order placed successfully.', [
            'order_id'=>$orderId,'order_number'=>$orderNumber,'subtotal'=>$subtotal,'coupon_code'=>$appliedCouponCode,
            'coupon_discount'=>$couponDiscount,'tax_rate'=>GST_RATE,'tax_amount'=>$taxAmount,
            'delivery_charge'=>$deliveryCharge,'total'=>$total,'payment_method'=>$paymentMethod,
            'payment_status'=>$paymentStatus,'order_status'=>'accepted'
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(false, $e->getMessage() ?: 'Could not place your order.', [], 422);
    }
}

jsonResponse(false, 'Unknown checkout action.', [], 400);
