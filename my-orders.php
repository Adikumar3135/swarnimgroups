<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$user = loggedUser();
if (!$user) { header('Location: login.php?redirect=my-orders.php'); exit; }

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->execute([(int)$user['id']]);
$orders = $stmt->fetchAll();

$itemStmt = $pdo->prepare('SELECT oi.*, p.slug, p.stock, p.stock_status, p.status AS product_status FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id ASC');
foreach ($orders as &$order) {
    $itemStmt->execute([(int)$order['id']]);
    $order['items'] = $itemStmt->fetchAll();
}
unset($order);

function oe($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function money($v): string { return '₹' . number_format((float)$v, 2); }
function prettyStatus(string $status): string { return ucwords(str_replace('_', ' ', $status)); }
function statusIndex(string $status): int {
    return match($status) {
        'accepted' => 0, 'processing' => 1, 'packed' => 2, 'shipped' => 3, 'delivered' => 4,
        'cancelled' => -1, default => 0
    };
}
$csrf = csrfToken();
?>
<!doctype html>
<html lang="en">
<head><link rel="icon" type="image/png" sizes="256x256" href="data/favicon.png"><link rel="apple-touch-icon" href="data/favicon.png">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0a0d14">
<title>My Orders — Swarnim Groups</title>
<style>
:root{--bg:#070a10;--panel:#0e131d;--panel2:#121925;--text:#f4f7fb;--muted:#98a3b5;--line:rgba(255,255,255,.09);--gold:#f5c542;--gold2:#ffdc68;--green:#54e69b;--blue:#78b8ff;--red:#ff6677;--shadow:0 22px 70px rgba(0,0,0,.34)}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(circle at 10% 0%,rgba(245,197,66,.08),transparent 28%),radial-gradient(circle at 90% 15%,rgba(77,140,255,.08),transparent 30%),var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.page{width:min(1220px,calc(100% - 32px));margin:auto;padding:26px 0 70px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:24px}.brand{display:flex;align-items:center;gap:13px;text-decoration:none;color:var(--text)}.brand img{width:48px;height:48px;object-fit:contain;border-radius:14px;background:#101621;padding:7px;border:1px solid var(--line)}.brand strong{display:block;font-size:18px}.brand span{display:block;color:var(--muted);font-size:12px;margin-top:2px}.actions{display:flex;gap:10px;align-items:center}.btn{border:1px solid var(--line);background:rgba(255,255,255,.05);color:var(--text);padding:11px 15px;border-radius:13px;text-decoration:none;font-weight:800;cursor:pointer;transition:.22s}.btn:hover{transform:translateY(-2px);border-color:rgba(245,197,66,.45);background:rgba(255,255,255,.09)}.btn.primary{background:linear-gradient(135deg,var(--gold),var(--gold2));color:#111;border:0}.hero{padding:28px;border:1px solid var(--line);border-radius:28px;background:linear-gradient(145deg,rgba(255,255,255,.07),rgba(255,255,255,.025));box-shadow:var(--shadow);position:relative;overflow:hidden}.hero:after{content:"";position:absolute;width:230px;height:230px;border-radius:50%;right:-100px;top:-110px;background:rgba(245,197,66,.09);filter:blur(2px)}.eyebrow{color:var(--gold);font-weight:900;text-transform:uppercase;letter-spacing:.13em;font-size:11px}.hero h1{font-size:clamp(28px,4vw,46px);margin:8px 0 8px}.hero p{color:var(--muted);margin:0;max-width:720px}.stats{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}.stat{border:1px solid var(--line);background:rgba(0,0,0,.14);border-radius:15px;padding:10px 14px}.stat b{font-size:18px}.stat span{display:block;color:var(--muted);font-size:11px;margin-top:2px}.toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin:24px 0 14px;flex-wrap:wrap}.filters{display:flex;gap:8px;flex-wrap:wrap}.filter{border:1px solid var(--line);background:var(--panel);color:var(--muted);padding:9px 13px;border-radius:999px;cursor:pointer;font-weight:700}.filter.active,.filter:hover{color:var(--text);border-color:rgba(245,197,66,.4);background:rgba(245,197,66,.09)}.search{width:min(300px,100%);padding:12px 14px;border-radius:13px;border:1px solid var(--line);background:var(--panel);color:var(--text);outline:none}.orders{display:grid;gap:18px}.order-card{border:1px solid var(--line);background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));border-radius:24px;overflow:hidden;box-shadow:0 14px 45px rgba(0,0,0,.2);animation:rise .45s ease both}.order-head{display:flex;justify-content:space-between;gap:16px;padding:19px 21px;border-bottom:1px solid var(--line);background:rgba(255,255,255,.025)}.order-no{font-weight:900}.order-date{font-size:12px;color:var(--muted);margin-top:4px}.status-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;font-size:12px;font-weight:900;background:rgba(84,230,155,.1);color:var(--green);border:1px solid rgba(84,230,155,.2);white-space:nowrap}.status-pill.cancelled{background:rgba(255,102,119,.1);color:var(--red);border-color:rgba(255,102,119,.2)}.items{padding:6px 21px}.item{display:grid;grid-template-columns:76px 1fr auto;gap:15px;align-items:center;padding:15px 0;border-bottom:1px solid rgba(255,255,255,.055)}.item:last-child{border-bottom:0}.item img{width:76px;height:76px;object-fit:cover;border-radius:15px;background:#151b27;border:1px solid var(--line)}.item-name{font-weight:850;line-height:1.35}.item-meta{color:var(--muted);font-size:12px;margin-top:6px}.item-price{text-align:right;font-weight:900}.qty{color:var(--muted);font-size:12px;margin-top:5px}.timeline{display:flex;align-items:flex-start;padding:18px 21px 8px;gap:0;overflow:auto}.step{min-width:120px;flex:1;position:relative;text-align:center;color:var(--muted);font-size:11px;font-weight:800}.step:not(:last-child):after{content:"";position:absolute;top:11px;left:50%;width:100%;height:2px;background:#273142;z-index:0}.step.done:not(:last-child):after{background:linear-gradient(90deg,var(--green),#387c5b)}.dot{width:24px;height:24px;border-radius:50%;margin:0 auto 7px;border:2px solid #354053;background:#101621;position:relative;z-index:1;display:grid;place-items:center}.step.done{color:var(--text)}.step.done .dot{border-color:var(--green);background:rgba(84,230,155,.14);color:var(--green)}.step.current .dot{border-color:var(--gold);box-shadow:0 0 0 5px rgba(245,197,66,.09)}.cancelled-box{margin:16px 21px;padding:13px 15px;border-radius:15px;background:rgba(255,102,119,.07);border:1px solid rgba(255,102,119,.16);color:#ff9aa7;font-size:13px}.summary{display:grid;grid-template-columns:1fr auto;gap:20px;padding:17px 21px;background:rgba(0,0,0,.13);border-top:1px solid var(--line)}.summary small{color:var(--muted)}.amounts{min-width:240px}.row{display:flex;justify-content:space-between;gap:20px;padding:4px 0;font-size:13px;color:var(--muted)}.row.total{padding-top:9px;margin-top:5px;border-top:1px dashed var(--line);color:var(--text);font-size:17px;font-weight:900}.row.discount{color:var(--green)}.card-actions{display:flex;gap:9px;flex-wrap:wrap;padding:0 21px 20px}.mini{font-size:12px;padding:9px 12px}.modal{position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(10px);display:none;align-items:center;justify-content:center;padding:18px;z-index:100}.modal.open{display:flex}.modal-box{width:min(860px,100%);max-height:90vh;overflow:auto;border:1px solid var(--line);border-radius:24px;background:#0d121b;box-shadow:0 30px 100px rgba(0,0,0,.55);animation:pop .25s ease}.modal-head{display:flex;justify-content:space-between;align-items:center;padding:20px;border-bottom:1px solid var(--line);position:sticky;top:0;background:#0d121b;z-index:2}.modal-head h2{margin:0}.close{border:1px solid var(--line);background:rgba(255,255,255,.05);color:var(--text);width:38px;height:38px;border-radius:12px;cursor:pointer}.modal-body{padding:20px}.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.info-box{border:1px solid var(--line);border-radius:17px;padding:15px;background:rgba(255,255,255,.025)}.info-box h3{font-size:13px;margin:0 0 11px;color:var(--gold)}.info-box p{margin:5px 0;color:#d7ddea;font-size:13px;line-height:1.55}.notice{padding:13px 15px;border-radius:14px;background:rgba(120,184,255,.08);border:1px solid rgba(120,184,255,.16);color:#b9d8ff;font-size:13px;margin-bottom:16px}.empty{padding:70px 20px;text-align:center;border:1px dashed #2b3547;border-radius:22px;background:rgba(255,255,255,.025)}.empty .icon{font-size:46px;margin-bottom:10px}.empty h2{margin:0 0 6px}.empty p{color:var(--muted)}@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}@keyframes pop{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:none}}
@media(max-width:760px){.page{width:min(100% - 20px,1220px);padding-top:14px}.topbar{align-items:flex-start}.brand img{width:42px;height:42px}.actions .back{display:none}.hero{padding:22px}.order-head{align-items:flex-start;flex-direction:column}.item{grid-template-columns:58px 1fr}.item img{width:58px;height:58px}.item-price{grid-column:2;text-align:left}.summary{grid-template-columns:1fr}.amounts{min-width:0}.detail-grid{grid-template-columns:1fr}.timeline{padding-left:10px;padding-right:10px}.step{min-width:105px}.search{width:100%}}
@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<div class="page">
  <div class="topbar">
    <a class="brand" href="index.html"><img src="data/Swarnim Logo.png" alt="Swarnim Groups"><div><strong>Swarnim Groups</strong><span>Power • Comfort • Trust</span></div></a>
    <div class="actions"><a class="btn back" href="product.php">Continue Shopping</a><a class="btn primary" href="index.html">Home</a></div>
  </div>

  <section class="hero">
    <div class="eyebrow">Your account</div>
    <h1>My Orders</h1>
    <p>Track every Swarnim Groups purchase, view item details, payment information, delivery address and order history from one place.</p>
    <div class="stats">
      <div class="stat"><b><?=count($orders)?></b><span>Total Orders</span></div>
      <div class="stat"><b><?=count(array_filter($orders, fn($o)=>$o['order_status']==='delivered'))?></b><span>Delivered</span></div>
      <div class="stat"><b><?=count(array_filter($orders, fn($o)=>!in_array($o['order_status'],['delivered','cancelled'],true)))?></b><span>Active</span></div>
    </div>
  </section>

  <div class="toolbar">
    <div class="filters">
      <button class="filter active" data-filter="all">All</button>
      <button class="filter" data-filter="active">Active</button>
      <button class="filter" data-filter="delivered">Delivered</button>
      <button class="filter" data-filter="cancelled">Cancelled</button>
    </div>
    <input class="search" id="orderSearch" type="search" placeholder="Search order number or product…">
  </div>

  <main class="orders" id="ordersList">
  <?php if (!$orders): ?>
    <div class="empty"><div class="icon"></div><h2>No orders yet</h2><p>Once you place an order, its complete journey will appear here.</p><a class="btn primary" href="product.php">Start Shopping</a></div>
  <?php else: foreach ($orders as $order): $idx=statusIndex((string)$order['order_status']); $cancelled=$idx<0; ?>
    <article class="order-card" data-status="<?=oe($order['order_status'])?>" data-search="<?=oe(strtolower($order['order_number'].' '.implode(' ',array_column($order['items'],'product_name'))))?>">
      <div class="order-head">
        <div><div class="order-no">Order #<?=oe($order['order_number'])?></div><div class="order-date">Placed on <?=oe(date('d M Y, h:i A',strtotime((string)$order['created_at'])))?></div></div>
        <span class="status-pill <?=$cancelled?'cancelled':''?>">● <?=oe(prettyStatus((string)$order['order_status']))?></span>
      </div>
      <?php if (!$cancelled): ?>
      <div class="timeline">
        <?php foreach (['accepted'=>'Order Accepted','processing'=>'Processing','packed'=>'Packed','shipped'=>'Shipped','delivered'=>'Delivered'] as $key=>$label): $si=statusIndex($key); $done=$idx>=$si; $current=$idx===$si; ?>
          <div class="step <?=$done?'done':''?> <?=$current?'current':''?>"><div class="dot"><?=$done?'✓':''?></div><span><?=oe($label)?></span></div>
        <?php endforeach; ?>
      </div>
      <?php else: ?><div class="cancelled-box">This order was cancelled. If you need help, contact Swarnim Groups support with your order number.</div><?php endif; ?>
      <div class="items">
        <?php foreach ($order['items'] as $item): ?>
          <div class="item">
            <img src="<?=oe($item['image_url'] ?: 'data/Swarnim Logo.png')?>" alt="<?=oe($item['product_name'])?>">
            <div><div class="item-name"><?=oe($item['product_name'])?></div><div class="item-meta">P-ID: <?=oe($item['product_pid'] ?: '—')?> · Qty <?= (int)$item['quantity'] ?></div></div>
            <div class="item-price"><?=money($item['line_total'])?><div class="qty"><?=money($item['unit_price'])?> each</div></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="summary">
        <div>
          <small>Payment: <strong style="color:var(--text)"><?=oe(strtoupper((string)$order['payment_method']))?></strong> · Status: <strong style="color:var(--green)"><?=oe(prettyStatus((string)$order['payment_status']))?></strong></small>
          <br><small>Delivery: <?=oe($order['city'])?>, <?=oe($order['state'])?> — <?=oe($order['pincode'])?></small>
        </div>
        <div class="amounts">
          <div class="row"><span>Subtotal</span><b><?=money($order['subtotal'])?></b></div>
          <?php if ((float)$order['coupon_discount']>0): ?><div class="row discount"><span>Coupon <?=oe($order['coupon_code'])?></span><b>−<?=money($order['coupon_discount'])?></b></div><?php endif; ?>
          <div class="row"><span>GST <?=number_format((float)$order['tax_rate'],0)?>%</span><b><?=money($order['tax_amount'])?></b></div>
          <div class="row"><span>Delivery</span><b><?=((float)$order['delivery_charge']>0)?money($order['delivery_charge']):'FREE'?></b></div>
          <div class="row total"><span>Total</span><b><?=money($order['total_amount'])?></b></div>
        </div>
      </div>
      <div class="card-actions">
        <button class="btn mini" data-view="<?= (int)$order['id'] ?>">View Details</button>
        <a class="btn mini" target="_blank" href="invoice.php?id=<?= (int)$order['id'] ?>">View Invoice</a><a class="btn mini" target="_blank" href="invoice.php?id=<?= (int)$order['id'] ?>&download=1">Download PDF</a>
        <button class="btn mini" data-reorder="<?= (int)$order['id'] ?>">Buy Again</button>
        <?php if (in_array($order['order_status'],['accepted','processing'],true)): ?><button class="btn mini" data-cancel="<?= (int)$order['id'] ?>">Cancel Order</button><?php endif; ?>
      </div>
      <template id="detail-<?= (int)$order['id'] ?>">
        <div class="notice">Order <strong><?=oe($order['order_number'])?></strong> was placed on <?=oe(date('d M Y, h:i A',strtotime((string)$order['created_at'])))?>.</div>
        <div class="detail-grid">
          <div class="info-box"><h3>Delivery Address</h3><p><?=oe($order['customer_name'])?><br><?=oe($order['customer_phone'])?><br><?=oe($order['address_line1'])?><?= $order['address_line2'] ? '<br>'.oe($order['address_line2']) : '' ?><?= $order['landmark'] ? '<br>Landmark: '.oe($order['landmark']) : '' ?><br><?=oe($order['city'])?>, <?=oe($order['state'])?> — <?=oe($order['pincode'])?></p></div>
          <div class="info-box"><h3>Payment</h3><p>Method: <?=oe(strtoupper($order['payment_method']))?><br>Status: <?=oe(prettyStatus($order['payment_status']))?><br>Order Status: <?=oe(prettyStatus($order['order_status']))?></p></div>
          <div class="info-box"><h3>Price Details</h3><p>Subtotal: <?=money($order['subtotal'])?><br>Coupon: <?= $order['coupon_code'] ? oe($order['coupon_code']).' · −'.money($order['coupon_discount']) : 'None' ?><br>GST: <?=money($order['tax_amount'])?> (<?=number_format((float)$order['tax_rate'],0)?>%)<br>Delivery: <?=((float)$order['delivery_charge']>0)?money($order['delivery_charge']):'FREE'?><br><strong>Total: <?=money($order['total_amount'])?></strong></p></div>
          <div class="info-box"><h3>Order Items</h3><p><?php foreach($order['items'] as $item): ?><?=oe($item['product_name'])?> × <?= (int)$item['quantity'] ?> — <?=money($item['line_total'])?><br><?php endforeach; ?></p></div>
        </div>
      </template>
    </article>
  <?php endforeach; endif; ?>
  </main>
</div>

<div class="modal" id="detailsModal"><div class="modal-box"><div class="modal-head"><h2>Order Details</h2><button class="close" data-close>×</button></div><div class="modal-body" id="detailsBody"></div></div></div>

<script>
const csrf=<?=json_encode($csrf)?>;
const modal=document.getElementById('detailsModal');
const body=document.getElementById('detailsBody');
const esc=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
function openDetails(id){const t=document.getElementById('detail-'+id);if(!t)return;body.innerHTML=t.innerHTML;modal.classList.add('open')}
function closeModal(){modal.classList.remove('open')}
document.querySelectorAll('[data-view]').forEach(b=>b.onclick=()=>openDetails(b.dataset.view));
document.querySelector('[data-close]').onclick=closeModal;
modal.onclick=e=>{if(e.target===modal)closeModal()};
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal()});

</script>
</body></html>
