<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$user = loggedUser();
$csrf = csrfToken();
$addresses = [];
$products = [];

if ($user) {
    $stmt = db()->prepare('SELECT id,address_line1,address_line2,landmark,city,state,pincode,address_type,is_default FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC LIMIT 5');
    $stmt->execute([(int)$user['id']]);
    $addresses = $stmt->fetchAll();
}

$products = db()->query("SELECT id,name,product_pid,COALESCE(NULLIF(selling_price,0),price) AS selling_price,mrp,stock,stock_status,image_url FROM products WHERE status='active'")->fetchAll();

function e2(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="theme-color" content="#070a12">
<title>Checkout — Swarnim Groups</title>
<link rel="icon" href="data/Swarnim Logo.png">
<link rel="stylesheet" href="css/style.css">
<style>
.checkout-page{min-height:100vh;padding:110px 0 80px;position:relative;overflow:hidden}
.checkout-page:before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(245,196,81,.12),transparent 65%);top:40px;right:-220px;pointer-events:none}
.checkout-wrap{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(340px,.7fr);gap:24px;align-items:start}
.checkout-panel{background:linear-gradient(145deg,var(--surface2),var(--surface));border:1px solid var(--line);border-radius:28px;box-shadow:var(--shadow);overflow:hidden}
.checkout-head{padding:30px;border-bottom:1px solid var(--line)}
.checkout-head h1{font-size:clamp(2.2rem,5vw,4.2rem);letter-spacing:-.06em;margin:6px 0}
.checkout-head p{color:var(--muted);max-width:680px}
.checkout-body{padding:28px}
.step{display:flex;gap:14px;align-items:center;margin-bottom:26px}
.step-num{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;background:var(--gold);color:#111;font-weight:900}
.step h2{font-size:1.2rem;margin:0}.step small{display:block;color:var(--muted);margin-top:3px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field-full{grid-column:1/-1}
.field label{display:block;font-size:.8rem;font-weight:700;margin:0 0 7px;color:var(--muted)}
.field input,.field textarea,.field select{width:100%;box-sizing:border-box;border:1px solid var(--line);background:var(--bg);color:var(--text);border-radius:14px;padding:13px 14px;outline:none;transition:.25s}
.field textarea{min-height:90px;resize:vertical}.field input:focus,.field textarea:focus,.field select:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(245,196,81,.09)}
.pincode-wrap{display:flex;gap:8px}.pincode-wrap input{flex:1}.pin-btn{border:1px solid var(--line);background:var(--surface2);color:var(--text);border-radius:14px;padding:0 15px;font-weight:800;cursor:pointer}
.pin-status{margin-top:8px;font-size:.82rem;min-height:20px}.pin-status.ok{color:var(--green)}.pin-status.bad{color:#ff7d8d}
.saved-address{margin-bottom:18px}.saved-address select{width:100%;border:1px solid var(--line);background:var(--bg);color:var(--text);padding:13px;border-radius:14px}
.check-row{display:flex;align-items:center;gap:9px;color:var(--muted);font-size:.86rem;margin-top:14px}.check-row input{accent-color:var(--gold)}
.payment-options{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px}
.coupon-box{margin-top:18px;padding:18px;border:1px solid var(--line);border-radius:20px;background:rgba(255,255,255,.025)}
.coupon-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.coupon-head h3{margin:0;font-size:1rem}.coupon-head span{font-size:.75rem;color:var(--muted)}
.coupon-input{display:flex;gap:8px}.coupon-input input{flex:1}.coupon-apply{border:0;border-radius:13px;padding:0 18px;background:var(--gold);color:#111;font-weight:900;cursor:pointer;transition:.25s}.coupon-apply:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(245,196,81,.2)}
.coupon-status{min-height:20px;margin-top:8px;font-size:.8rem}.coupon-status.ok{color:var(--green)}.coupon-status.bad{color:#ff7d8d}
.coupon-track{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;padding:4px 2px 8px;margin-top:13px;scrollbar-width:thin}.coupon-track::-webkit-scrollbar{height:5px}.coupon-track::-webkit-scrollbar-thumb{background:var(--line);border-radius:99px}
.coupon-card{min-width:235px;scroll-snap-align:start;border:1px solid var(--line);border-radius:17px;padding:14px;background:linear-gradient(145deg,var(--surface2),var(--bg));position:relative;overflow:hidden;cursor:pointer;transition:.25s}.coupon-card:hover{transform:translateY(-2px);border-color:rgba(245,196,81,.5)}.coupon-card.applied{border-color:var(--green);box-shadow:0 0 0 2px rgba(49,210,139,.08)}.coupon-card.disabled{opacity:.55;cursor:not-allowed}.coupon-code{font-weight:950;letter-spacing:.08em;color:var(--gold)}.coupon-title{font-weight:800;margin-top:5px}.coupon-desc{font-size:.75rem;color:var(--muted);margin-top:4px;min-height:32px}.coupon-meta{display:flex;justify-content:space-between;gap:8px;margin-top:10px;font-size:.72rem}.coupon-meta b{color:var(--text)}
.tax-note{font-size:.72rem;color:var(--muted);margin-top:7px}.discount-row{color:var(--green)!important}.coupon-chip{display:inline-flex;align-items:center;gap:7px;padding:5px 9px;border-radius:999px;background:rgba(49,210,139,.1);border:1px solid rgba(49,210,139,.2);font-size:.72rem;color:var(--green);margin-top:8px}.coupon-chip button{border:0;background:none;color:inherit;cursor:pointer;font-weight:900;padding:0}
.payment-option{position:relative}.payment-option input{position:absolute;opacity:0}
.payment-card{display:block;padding:18px;border:1px solid var(--line);border-radius:18px;cursor:pointer;transition:.25s;background:var(--bg)}
.payment-card strong{display:block;margin-bottom:5px}.payment-card span{font-size:.78rem;color:var(--muted)}
.payment-option input:checked+.payment-card{border-color:var(--gold);box-shadow:0 0 0 3px rgba(245,196,81,.1);transform:translateY(-2px)}
.notice{padding:13px 15px;border-radius:15px;background:rgba(245,196,81,.08);border:1px solid rgba(245,196,81,.18);color:var(--muted);font-size:.82rem;margin-top:12px}
.order-side{position:sticky;top:95px}.summary{padding:25px}.summary h2{font-size:1.35rem;margin:0}.summary-items{display:grid;gap:12px;margin:20px 0}.summary-item{display:grid;grid-template-columns:58px 1fr auto;gap:10px;align-items:center}.summary-item img{width:58px;height:58px;object-fit:cover;border-radius:14px;background:var(--surface2)}.summary-item small{color:var(--muted);display:block;margin-top:3px}.summary-item strong{white-space:nowrap}.summary-row{display:flex;justify-content:space-between;padding:11px 0;color:var(--muted);border-top:1px solid var(--line)}.summary-row.total{font-size:1.2rem;color:var(--text);font-weight:900}.place-btn{width:100%;border:0;border-radius:17px;padding:17px;background:var(--text);color:var(--bg);font-size:1rem;font-weight:900;cursor:pointer;transition:.25s;position:relative;overflow:hidden}.place-btn:hover{transform:translateY(-2px);box-shadow:0 15px 35px rgba(0,0,0,.2)}.place-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}.place-btn.loading{color:transparent}.place-btn.loading:after{content:"";position:absolute;width:21px;height:21px;border:3px solid currentColor;border-top-color:transparent;border-radius:50%;left:50%;top:50%;transform:translate(-50%,-50%);animation:spin .7s linear infinite;color:var(--bg)}@keyframes spin{to{transform:translate(-50%,-50%) rotate(360deg)}}
.empty-checkout{padding:70px 20px;text-align:center}.empty-checkout h2{font-size:2rem}.empty-checkout p{color:var(--muted)}
.success-layer{position:fixed;inset:0;background:rgba(3,5,10,.78);backdrop-filter:blur(18px);z-index:1000;display:grid;place-items:center;opacity:0;visibility:hidden;transition:.4s}.success-layer.open{opacity:1;visibility:visible}.success-card{width:min(520px,calc(100% - 28px));background:var(--surface2);border:1px solid var(--line);border-radius:30px;padding:40px;text-align:center;transform:translateY(30px) scale(.95);transition:.5s;box-shadow:0 40px 100px rgba(0,0,0,.35)}.success-layer.open .success-card{transform:none}.success-icon{width:92px;height:92px;border-radius:50%;display:grid;place-items:center;background:rgba(49,210,139,.13);border:1px solid rgba(49,210,139,.35);color:var(--green);font-size:3rem;margin:0 auto 20px;animation:pop .6s cubic-bezier(.2,1.6,.4,1)}@keyframes pop{0%{transform:scale(0)}70%{transform:scale(1.12)}100%{transform:scale(1)}}.success-card h2{font-size:2.3rem;margin:0 0 8px}.success-card p{color:var(--muted)}.order-code{display:inline-block;margin:15px 0;padding:10px 15px;border:1px dashed var(--gold);border-radius:12px;font-weight:900;letter-spacing:.08em}.success-actions{display:flex;gap:10px;justify-content:center;margin-top:20px;flex-wrap:wrap}.success-actions a,.success-actions button{border:1px solid var(--line);border-radius:13px;padding:12px 16px;background:var(--bg);color:var(--text);text-decoration:none;cursor:pointer}
.confetti{position:fixed;top:-20px;width:8px;height:14px;z-index:1001;pointer-events:none;animation:fall 1.7s linear forwards}@keyframes fall{to{transform:translate3d(var(--x),110vh,0) rotate(720deg);opacity:0}}
@media(max-width:900px){.checkout-wrap{grid-template-columns:1fr}.order-side{position:static}}@media(max-width:600px){.checkout-page{padding-top:85px}.checkout-body,.summary,.checkout-head{padding:21px}.form-grid,.payment-options{grid-template-columns:1fr}.field-full{grid-column:auto}.pincode-wrap{display:grid;grid-template-columns:1fr auto}.pincode-wrap .pin-btn{padding:0 12px}.success-card{padding:28px}}

/* Premium Coupon UI */
.coupon-box{
    position:relative;
    margin-top:22px;
    padding:22px;
    border:1px solid color-mix(in srgb,var(--gold) 18%,var(--line));
    border-radius:26px;
    background:
        radial-gradient(circle at 92% 8%,rgba(245,196,81,.12),transparent 28%),
        linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,255,255,.018));
    box-shadow:0 18px 45px rgba(0,0,0,.12);
    overflow:hidden;
}
.coupon-box:before{
    content:"";
    position:absolute;
    width:170px;height:170px;
    right:-75px;bottom:-105px;
    border-radius:50%;
    background:rgba(245,196,81,.08);
    pointer-events:none;
}
.coupon-head{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    margin-bottom:16px;
}
.coupon-head h3{
    margin:0;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:1.12rem;
    font-weight:900;
    letter-spacing:-.02em;
}
.coupon-head h3:before{
    content:"";
    width:34px;height:34px;
    display:grid;
    place-items:center;
    border-radius:11px;
    background:rgba(245,196,81,.13);
    border:1px solid rgba(245,196,81,.2);
    box-shadow:inset 0 0 18px rgba(245,196,81,.06);
}
.coupon-head span{
    color:var(--muted);
    font-size:.75rem;
    white-space:nowrap;
}
.coupon-input{
    position:relative;
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    gap:10px;
}
.coupon-input input{
    min-width:0;
    height:52px;
    box-sizing:border-box;
    border:1px solid var(--line);
    border-radius:15px;
    background:rgba(0,0,0,.12);
    color:var(--text);
    padding:0 16px;
    outline:none;
    font-size:.98rem;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
    transition:.25s ease;
}
[data-theme="light"] .coupon-input input{background:rgba(255,255,255,.8)}
.coupon-input input::placeholder{color:var(--muted);text-transform:none;letter-spacing:0;font-weight:500}
.coupon-input input:focus{
    border-color:var(--gold);
    box-shadow:0 0 0 4px rgba(245,196,81,.10),0 8px 24px rgba(0,0,0,.08);
}
.coupon-apply{
    min-width:108px;
    height:52px;
    border:0;
    border-radius:15px;
    padding:0 22px;
    background:linear-gradient(135deg,#ffd45c,#efb83e);
    color:#111;
    font-size:.92rem;
    font-weight:950;
    letter-spacing:.01em;
    cursor:pointer;
    transition:transform .25s ease,box-shadow .25s ease,filter .25s ease;
}
.coupon-apply:hover{transform:translateY(-2px);filter:saturate(1.08);box-shadow:0 12px 28px rgba(245,196,81,.24)}
.coupon-apply:active{transform:translateY(0) scale(.98)}
.coupon-status{
    min-height:20px;
    margin:10px 3px 0;
    font-size:.78rem;
    font-weight:700;
}
.coupon-status.ok{color:var(--green)}
.coupon-status.bad{color:#ff6f83}
.coupon-track-wrap{position:relative;margin-top:17px}
.coupon-track{
    display:flex;
    gap:14px;
    overflow-x:auto;
    overflow-y:hidden;
    scroll-snap-type:x mandatory;
    scroll-behavior:smooth;
    padding:5px 3px 14px;
    scrollbar-width:none;
    overscroll-behavior-inline:contain;
}
.coupon-track::-webkit-scrollbar{display:none}
.coupon-card{
    position:relative;
    flex:0 0 285px;
    min-width:285px;
    min-height:164px;
    box-sizing:border-box;
    scroll-snap-align:start;
    border:1px solid var(--line);
    border-radius:21px;
    padding:18px 18px 16px;
    text-align:left;
    color:var(--text);
    background:
        radial-gradient(circle at 100% 0,rgba(245,196,81,.12),transparent 32%),
        linear-gradient(145deg,var(--surface2),var(--bg));
    box-shadow:0 10px 25px rgba(0,0,0,.09);
    overflow:hidden;
    cursor:pointer;
    transition:transform .28s ease,border-color .28s ease,box-shadow .28s ease;
}
.coupon-card:after{
    content:"";
    position:absolute;
    left:18px;right:18px;bottom:50px;
    border-top:1px dashed rgba(255,255,255,.13);
}
[data-theme="light"] .coupon-card:after{border-color:rgba(0,0,0,.12)}
.coupon-card:hover{
    transform:translateY(-5px);
    border-color:rgba(245,196,81,.55);
    box-shadow:0 18px 36px rgba(0,0,0,.16),0 0 0 1px rgba(245,196,81,.05);
}
.coupon-card.applied{
    border-color:var(--green);
    box-shadow:0 0 0 2px rgba(49,210,139,.10),0 18px 35px rgba(0,0,0,.14);
}
.coupon-card.disabled{opacity:.55;cursor:not-allowed;transform:none;filter:saturate(.55)}
.coupon-code{
    display:inline-flex;
    align-items:center;
    min-height:29px;
    padding:0 10px;
    border-radius:9px;
    background:rgba(245,196,81,.10);
    border:1px dashed rgba(245,196,81,.42);
    color:var(--gold);
    font-size:.78rem;
    font-weight:950;
    letter-spacing:.12em;
}
.coupon-title{
    margin-top:11px;
    color:var(--text);
    font-size:1rem;
    line-height:1.25;
    font-weight:900;
    letter-spacing:-.015em;
}
.coupon-desc{
    color:var(--muted);
    font-size:.75rem;
    line-height:1.45;
    margin-top:5px;
    min-height:34px;
    max-width:245px;
}
.coupon-meta{
    position:absolute;
    left:18px;right:18px;bottom:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    color:var(--muted);
    font-size:.7rem;
}
.coupon-meta b{color:var(--text);font-size:.76rem}
.coupon-card.applied .coupon-meta b{color:var(--green)}
.coupon-scroll-hint{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    margin-top:1px;
    color:var(--muted);
    font-size:.68rem;
}
.coupon-scroll-hint:before,.coupon-scroll-hint:after{
    content:"";
    width:26px;
    height:1px;
    background:linear-gradient(90deg,transparent,var(--line));
}
.coupon-scroll-hint:after{transform:scaleX(-1)}
.coupon-chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:7px 11px;
    border-radius:999px;
    background:rgba(49,210,139,.10);
    border:1px solid rgba(49,210,139,.24);
    font-size:.72rem;
    font-weight:750;
    color:var(--green);
    margin-top:9px;
}
.coupon-chip button{border:0;background:none;color:inherit;cursor:pointer;font-weight:950;padding:0 2px;font-size:1rem}
@media(max-width:600px){
    .coupon-box{padding:17px;border-radius:21px}
    .coupon-head{align-items:flex-start;flex-direction:column;gap:5px}
    .coupon-head span{white-space:normal}
    .coupon-input{grid-template-columns:1fr}
    .coupon-apply{width:100%}
    .coupon-card{flex-basis:250px;min-width:250px;min-height:158px}
}

</style>
</head>
<body>
<header id="header"><div class="container"><nav class="nav"><a class="logo" href="index.html"><img src="data/Swarnim Logo.png" alt="Swarnim Groups"><div><span>Swarnim Groups</span><small>Power • Comfort • Trust</small></div></a><div class="navlinks"><a href="index.html">Home</a><a href="product.php">Products</a><a href="index.html#about">About</a><a href="index.html#contact">Contact</a></div><div class="nav-actions"><button class="icon-btn" id="themeBtn" type="button">☼</button><a class="tool-btn" href="product.php">← Cart</a></div></nav></div></header>

<main class="checkout-page">
<div class="container">
<?php if (!$user): ?>
<section class="checkout-panel empty-checkout">
<div class="eyebrow">Secure Checkout</div>
<h2>Login to continue</h2>
<p>Your cart is ready. Login first so we can securely save your order and delivery address to your account.</p>
<a class="cta" href="login.php">Login & Continue →</a>
</section>
<?php else: ?>
<div class="checkout-wrap">
<section class="checkout-panel">
<div class="checkout-head">
<div class="eyebrow">Swarnim Checkout</div>
<h1>Almost there.</h1>
<p>Confirm your delivery address, check your pincode and choose how you want to pay. No payment gateway is required for online orders in this flow.</p>
</div>
<div class="checkout-body">
<div class="step"><span class="step-num">1</span><div><h2>Delivery address</h2><small>We'll deliver only to serviceable pincodes.</small></div></div>

<?php if ($addresses): ?>
<div class="saved-address">
<label class="field"><span style="display:block;font-size:.8rem;font-weight:700;color:var(--muted);margin-bottom:7px">Use a saved address</span>
<select id="savedAddress">
<option value="">+ Enter a new address</option>
<?php foreach($addresses as $a): ?>
<option value="<?=e2(json_encode($a,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>"><?=e2(ucfirst($a['address_type']).' · '.$a['address_line1'].' · '.$a['pincode'])?></option>
<?php endforeach; ?>
</select></label>
</div>
<?php endif; ?>

<form id="checkoutForm">
<div class="form-grid">
<div class="field field-full"><label>Address Line 1 *</label><input id="address1" name="address_line1" required maxlength="255" placeholder="House / Flat / Shop / Street"></div>
<div class="field field-full"><label>Address Line 2</label><input id="address2" name="address_line2" maxlength="255" placeholder="Area / Locality / Colony"></div>
<div class="field"><label>Landmark</label><input id="landmark" name="landmark" maxlength="150" placeholder="Near landmark"></div>
<div class="field"><label>Pincode *</label><div class="pincode-wrap"><input id="pincode" name="pincode" inputmode="numeric" maxlength="6" required placeholder="6-digit pincode"><button class="pin-btn" type="button" id="checkPin">Check</button></div><div id="pinStatus" class="pin-status"></div></div>
<div class="field"><label>City *</label><input id="city" name="city" required maxlength="100" placeholder="City"></div>
<div class="field"><label>State *</label><input id="state" name="state" required maxlength="100" placeholder="State"></div>
</div>
<label class="check-row"><input type="checkbox" id="saveAddress" name="save_address" checked> Save this address for my next order</label>

<div style="height:34px"></div>
<div class="step"><span class="step-num">2</span><div><h2>Payment method</h2><small>Choose online order acceptance or cash on delivery.</small></div></div>
<div class="payment-options">
<label class="payment-option"><input type="radio" name="payment_method" value="online" checked><span class="payment-card"><strong>⚡ Online Payment</strong><span>No gateway. Order is accepted and payment status is marked <b>ONLINE</b>.</span></span></label>
<label class="payment-option"><input type="radio" name="payment_method" value="cod"><span class="payment-card"><strong>Cash on Delivery</strong><span>Pay cash when your order is delivered. Payment status: <b>COD</b>.</span></span></label>
</div>
<div class="notice">Online payment here is an order-acceptance mode only. No card/UPI gateway is charged. Your order will be saved with payment status <strong>ONLINE</strong>.</div>

<div class="coupon-box">
<div class="coupon-head"><h3>Apply a coupon</h3><span>Slide to explore available offers</span></div>
<div class="coupon-input"><input id="couponCode" maxlength="50" placeholder="Enter coupon code"><button class="coupon-apply" type="button" id="applyCoupon">Apply</button></div>
<div id="couponStatus" class="coupon-status"></div>
<div class="coupon-track-wrap"><div id="couponTrack" class="coupon-track"><div style="color:var(--muted);font-size:.8rem;padding:12px 4px">Loading coupons…</div></div><div class="coupon-scroll-hint">Swipe to explore offers</div></div>
</div>

<div style="height:34px"></div>
<div class="step"><span class="step-num">3</span><div><h2>Final note</h2><small>Optional instructions for our delivery team.</small></div></div>
<div class="field"><label>Order Note</label><textarea id="customerNote" name="customer_note" maxlength="1000" placeholder="Example: Call before delivery, leave at reception, etc."></textarea></div>
<div style="height:18px"></div>
<button class="place-btn" id="placeOrder" type="submit">Place Order →</button>
</form>
</div>
</section>

<aside class="checkout-panel order-side">
<div class="summary">
<div class="eyebrow">Your order</div><h2>Order Summary</h2>
<div id="summaryItems" class="summary-items"></div>
<div class="summary-row"><span>Subtotal</span><strong id="summarySubtotal">₹0</strong></div>
<div class="summary-row discount-row"><span>Coupon Discount</span><strong id="summaryDiscount">−₹0</strong></div>
<div class="summary-row"><span>GST / Tax <small style="font-size:.68rem">18%</small></span><strong id="summaryTax">₹0</strong></div>
<div class="summary-row"><span>Delivery</span><strong id="summaryDelivery">FREE</strong></div>
<div id="appliedCouponChip"></div>
<div class="tax-note">GST is calculated at 18% on the amount after coupon discount.</div>
<div class="summary-row total"><span>Total</span><strong id="summaryTotal">₹0</strong></div>
</div>
</aside>
</div>
<?php endif; ?>
</div>
</main>

<div class="success-layer" id="successLayer">
<div class="success-card">
<div class="success-icon">✓</div>
<div class="eyebrow">Order accepted</div>
<h2>You're all set!</h2>
<p id="successText">Your order has been successfully placed.</p>
<div class="order-code" id="orderCode">SW000000</div>
<p id="paymentText"></p>
<div class="success-actions"><a href="product.php">Continue Shopping</a><a href="index.html">Go Home</a></div>
</div>
</div>

<script>
window.CHECKOUT_CONFIG = {
    csrf: <?=json_encode($csrf)?>,
    user: <?=json_encode($user, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>,
    products: <?=json_encode($products, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>
};
</script>
<script>
const cfg=window.CHECKOUT_CONFIG;
const GST_RATE=18;
const money=n=>'₹'+Number(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
const esc=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
const productMap=new Map((cfg.products||[]).map(p=>[Number(p.id),p]));
let cart=JSON.parse(localStorage.getItem('swarnim-cart')||'{}');
let appliedCoupon=null;
let couponList=[];
const summaryItems=document.querySelector('#summaryItems');
const couponCode=document.querySelector('#couponCode');
const couponStatus=document.querySelector('#couponStatus');
const couponTrack=document.querySelector('#couponTrack');

function getSubtotal(){
    return Object.entries(cart).reduce((sum,[id,q])=>{
        const p=productMap.get(Number(id));
        return p&&Number(q)>0?sum+Number(p.selling_price)*Number(q):sum;
    },0);
}
function calculate(subtotal,discount){
    const d=Math.min(Math.max(Number(discount)||0,0),subtotal);
    const taxable=Math.max(0,subtotal-d);
    const tax=Math.round(taxable*GST_RATE)/100;
    return {subtotal,discount:d,taxable,tax,total:Math.round((taxable+tax)*100)/100};
}
function updateSummary(){
    const subtotal=getSubtotal();
    const calc=calculate(subtotal,appliedCoupon?.discount||0);
    document.querySelector('#summarySubtotal').textContent=money(calc.subtotal);
    document.querySelector('#summaryDiscount').textContent='−'+money(calc.discount);
    document.querySelector('#summaryTax').textContent=money(calc.tax);
    document.querySelector('#summaryTotal').textContent=money(calc.total);
    document.querySelector('#appliedCouponChip').innerHTML=appliedCoupon?`<span class="coupon-chip">✓ ${esc(appliedCoupon.code)} saved ${money(appliedCoupon.discount)} <button type="button" id="removeCoupon" aria-label="Remove coupon">×</button></span>`:'';
    document.querySelector('#removeCoupon')?.addEventListener('click',()=>{appliedCoupon=null;couponCode.value='';couponStatus.className='coupon-status';couponStatus.textContent='Coupon removed.';updateSummary();renderCoupons();});
}
function renderSummary(){
    const entries=Object.entries(cart).filter(([id,q])=>Number(q)>0&&productMap.has(Number(id)));
    if(!entries.length){
        summaryItems.innerHTML='<div style="padding:20px 0;color:var(--muted)">Your cart is empty. <a href="product.php" style="color:var(--gold)">Go back to products</a></div>';
        document.querySelector('#placeOrder').disabled=true;
        return;
    }
    document.querySelector('#placeOrder').disabled=false;
    summaryItems.innerHTML=entries.map(([id,q])=>{
        const p=productMap.get(Number(id)),qty=Number(q),total=Number(p.selling_price)*qty;
        return `<div class="summary-item"><img src="${esc(p.image_url)}" alt=""><div><strong>${esc(p.name)}</strong><small>${esc(p.product_pid||'')} · Qty ${qty}</small></div><strong>${money(total)}</strong></div>`;
    }).join('');
    updateSummary();
}
function couponLabel(c){
    return c.discount_type==='percent'?`${Number(c.discount_value).toFixed(0)}% OFF`:`₹${Number(c.discount_value).toLocaleString('en-IN')} OFF`;
}
function renderCoupons(){
    if(!couponList.length){couponTrack.innerHTML='<div style="color:var(--muted);font-size:.8rem;padding:12px 4px">No coupons available right now.</div>';return;}
    const subtotal=getSubtotal();
    couponTrack.innerHTML=couponList.map(c=>{
        const applied=appliedCoupon?.code===c.code;
        const can=c.available_now && subtotal>=Number(c.min_order_amount||0);
        const reason=!c.available_now?'Not available right now':subtotal<Number(c.min_order_amount||0)?`Min. order ${money(c.min_order_amount)}`:'Tap to apply';
        return `<button type="button" class="coupon-card ${applied?'applied':''} ${can||applied?'':'disabled'}" data-coupon="${esc(c.code)}" ${can||applied?'':'disabled'}>
            <div class="coupon-code">${esc(c.code)}</div><div class="coupon-title">${esc(c.title||couponLabel(c))}</div>
            <div class="coupon-desc">${esc(c.description||couponLabel(c))}</div>
            <div class="coupon-meta"><b>${esc(couponLabel(c))}</b><span>${esc(reason)}</span></div>
        </button>`;
    }).join('');
    couponTrack.querySelectorAll('[data-coupon]').forEach(b=>b.addEventListener('click',()=>applyCoupon(b.dataset.coupon)));
}
async function loadCoupons(){
    try{
        const r=await fetch('order-api.php?action=list-coupons',{credentials:'same-origin',cache:'no-store'});
        const j=await r.json();
        if(!j.ok)throw new Error(j.message||'Could not load coupons.');
        couponList=j.data.coupons||[];
        renderCoupons();
    }catch(e){couponTrack.innerHTML='<div style="color:var(--muted);font-size:.8rem;padding:12px 4px">Coupons could not be loaded.</div>';}
}
async function applyCoupon(code){
    code=String(code||'').trim().toUpperCase();
    if(!code)return;
    couponCode.value=code;
    couponStatus.className='coupon-status';couponStatus.textContent='Checking coupon…';
    const fd=new FormData();fd.append('action','validate-coupon');fd.append('code',code);fd.append('subtotal',getSubtotal().toFixed(2));
    try{
        const r=await fetch('order-api.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-CSRF-Token':cfg.csrf}});
        const j=await r.json();
        if(!j.ok)throw new Error(j.message||'Coupon cannot be applied.');
        appliedCoupon={code:j.data.code,discount:Number(j.data.discount),title:j.data.title||''};
        couponStatus.className='coupon-status ok';couponStatus.textContent=`✓ ${j.message} You save ${money(j.data.discount)}.`;
        renderCoupons();updateSummary();
    }catch(e){
        appliedCoupon=null;couponStatus.className='coupon-status bad';couponStatus.textContent=e.message;renderCoupons();updateSummary();
    }
}
document.querySelector('#applyCoupon')?.addEventListener('click',()=>applyCoupon(couponCode.value));
couponCode?.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();applyCoupon(couponCode.value);}});
renderSummary();
loadCoupons();

const fields={address1:document.querySelector('#address1'),address2:document.querySelector('#address2'),landmark:document.querySelector('#landmark'),city:document.querySelector('#city'),state:document.querySelector('#state'),pincode:document.querySelector('#pincode')};
const pinStatus=document.querySelector('#pinStatus');let pinAvailable=false;
async function checkPincode(){
    const pin=fields.pincode.value.replace(/\D/g,'').slice(0,6);fields.pincode.value=pin;
    if(pin.length!==6){pinAvailable=false;pinStatus.className='pin-status bad';pinStatus.textContent='Enter a valid 6-digit pincode.';return false;}
    pinStatus.className='pin-status';pinStatus.textContent='Checking delivery…';
    try{const r=await fetch('order-api.php?action=check-pincode&pincode='+encodeURIComponent(pin),{credentials:'same-origin',cache:'no-store'});const j=await r.json();
        if(j.ok&&j.data.available){pinAvailable=true;if(j.data.city)fields.city.value=j.data.city;if(j.data.state)fields.state.value=j.data.state;pinStatus.className='pin-status ok';pinStatus.textContent='✓ Delivery available to '+(j.data.city||'your area')+'.';return true;}
        pinAvailable=false;pinStatus.className='pin-status bad';pinStatus.innerHTML='✕ Delivery is not available here. <a href="tel:'+esc(j.data.support_phone||'+919955056900')+'" style="color:inherit;font-weight:800">Call '+esc(j.data.support_phone||'+91 9955056900')+'</a> to book delivery.';return false;
    }catch(e){pinAvailable=false;pinStatus.className='pin-status bad';pinStatus.textContent='Could not check pincode. Please try again.';return false;}
}
document.querySelector('#checkPin')?.addEventListener('click',checkPincode);fields.pincode?.addEventListener('blur',()=>{if(fields.pincode.value.length===6)checkPincode();});
document.querySelector('#savedAddress')?.addEventListener('change',e=>{if(!e.target.value)return;try{const a=JSON.parse(e.target.value);fields.address1.value=a.address_line1||'';fields.address2.value=a.address_line2||'';fields.landmark.value=a.landmark||'';fields.city.value=a.city||'';fields.state.value=a.state||'';fields.pincode.value=a.pincode||'';checkPincode();}catch(_){}});
function burst(){for(let i=0;i<85;i++){const el=document.createElement('i');el.className='confetti';el.style.left=(45+Math.random()*10)+'vw';el.style.setProperty('--x',((Math.random()-.5)*80)+'vw');el.style.animationDelay=(Math.random()*.35)+'s';el.style.transform='rotate('+Math.random()*360+'deg)';document.body.appendChild(el);setTimeout(()=>el.remove(),2200);}}
document.querySelector('#checkoutForm')?.addEventListener('submit',async e=>{
    e.preventDefault();if(!Object.keys(cart).length){alert('Your cart is empty.');return;}
    const pinOk=await checkPincode();if(!pinOk)return;
    const btn=document.querySelector('#placeOrder');btn.disabled=true;btn.classList.add('loading');
    const fd=new FormData(e.target);fd.append('action','place-order');fd.append('items',JSON.stringify(cart));if(appliedCoupon)fd.append('coupon_code',appliedCoupon.code);
    try{const r=await fetch('order-api.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-CSRF-Token':cfg.csrf}});const j=await r.json();
        if(!j.ok){if(j.data?.login_required)location.href='login.php';throw new Error(j.message||'Order could not be placed.');}
        localStorage.removeItem('swarnim-cart');document.querySelector('#orderCode').textContent=j.data.order_number;
        document.querySelector('#successText').textContent=`Order accepted. ${j.data.coupon_code?'Coupon '+j.data.coupon_code+' saved '+money(j.data.coupon_discount)+'. ':''}GST ${j.data.tax_rate}%: ${money(j.data.tax_amount)}. Total: ${money(j.data.total)}.`;
        document.querySelector('#paymentText').textContent=j.data.payment_method==='online'?'Payment status: ONLINE · Order status: ACCEPTED':'Payment status: CASH ON DELIVERY · Order status: ACCEPTED';
        document.querySelector('#successLayer').classList.add('open');burst();
    }catch(err){alert(err.message||'Something went wrong while placing your order.');btn.disabled=false;btn.classList.remove('loading');}
});
const themeBtn=document.querySelector('#themeBtn'),savedTheme=localStorage.getItem('swarnim-theme');if(savedTheme)document.documentElement.dataset.theme=savedTheme;themeBtn.textContent=document.documentElement.dataset.theme==='light'?'☾':'☼';themeBtn.onclick=()=>{const light=document.documentElement.dataset.theme!=='light';document.documentElement.dataset.theme=light?'light':'dark';localStorage.setItem('swarnim-theme',light?'light':'dark');themeBtn.textContent=light?'☾':'☼';};
</script>
</body>
</html>
