<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$user=loggedUser();
if(!$user || ($user['role']??'')!=='admin'){http_response_code(403);exit('Access denied. Admin login required.');}
$orders=db()->query("SELECT o.*, COUNT(oi.id) item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id GROUP BY o.id ORDER BY o.created_at DESC")->fetchAll();
function oe(string $v): string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?>
<!doctype html>
<html lang="en"><head>
<link rel="icon" type="image/png" href="data/Swarnim Logo.png">
<link rel="apple-touch-icon" href="data/Swarnim Logo.png">
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Orders — Swarnim Groups</title>
<style>
*{box-sizing:border-box}body{font-family:Inter,Arial,sans-serif;background:#070a12;color:#eef2f7;margin:0;padding:28px}.wrap{max-width:1400px;margin:auto}.top{display:flex;justify-content:space-between;gap:20px;align-items:center;margin-bottom:24px}.sub{color:#98a2b3}.btn{display:inline-block;text-decoration:none;color:#111;background:#f5c542;padding:11px 16px;border-radius:12px;font-weight:800}.table{overflow:auto;border:1px solid #273044;border-radius:18px;background:#0d111b}table{width:100%;border-collapse:collapse;min-width:1250px}th,td{padding:14px;border-bottom:1px solid #20283a;text-align:left;vertical-align:top}th{color:#f5c542;background:#111827}td{color:#d7ddea}.pill{display:inline-flex;padding:6px 9px;border-radius:999px;font-size:12px;font-weight:800;background:#182233}.online{color:#5de4a2;background:#103323}.cod{color:#ffd36a;background:#342b10}.accepted{color:#7dd3fc}.amount{font-weight:900;font-size:16px}.address{max-width:280px;line-height:1.5}.empty{text-align:center;padding:50px;color:#98a2b3}
</style></head><body><div class="wrap"><div class="top"><div><h1>Orders</h1><div class="sub">Accepted orders, payment status and delivery details.</div></div><a class="btn" href="index.html">Back to Website</a></div>
<div class="table"><table><thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Address</th><th>Items</th><th>Amount</th><th>Coupon / GST</th><th>Payment</th><th>Order Status</th></tr></thead><tbody>
<?php if(!$orders): ?><tr><td colspan="9" class="empty">No orders yet.</td></tr>
<?php else: foreach($orders as $o): ?>
<tr>
<td><strong><?=oe($o['order_number'])?></strong><br><small>#<?= (int)$o['id']?></small></td>
<td><?=oe($o['created_at'])?></td>
<td><?=oe($o['customer_name'])?><br><?=oe($o['customer_phone'])?><br><?=oe($o['customer_email']??'')?></td>
<td class="address"><?=oe($o['address_line1'])?><br><?=oe($o['address_line2']??'')?><?=($o['address_line2']?'':'')?><br><?=oe($o['city'])?>, <?=oe($o['state'])?> - <strong><?=oe($o['pincode'])?></strong></td>
<td><?= (int)$o['item_count'] ?> item(s)</td>
<td class="amount">₹<?=number_format((float)$o['total_amount'],2)?><br><small style="color:#98a2b3">Subtotal ₹<?=number_format((float)$o['subtotal'],2)?><br>Tax ₹<?=number_format((float)($o['tax_amount']??0),2)?></small></td>
<td><?php if(!empty($o['coupon_code'])): ?><span class="pill online"><?=oe($o['coupon_code'])?> · −₹<?=number_format((float)$o['coupon_discount'],2)?></span><br><?php else: ?><small style="color:#98a2b3">No coupon</small><br><?php endif; ?><small style="color:#98a2b3">GST <?=number_format((float)($o['tax_rate']??18),0)?>%</small></td>
<td><span class="pill <?= $o['payment_method']==='online'?'online':'cod' ?>"><?=oe(strtoupper($o['payment_method']))?></span><br><small><?=oe(str_replace('_',' ',strtoupper($o['payment_status'])))?></small></td>
<td><span class="pill accepted"><?=oe(strtoupper($o['order_status']))?></span></td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></body></html>
