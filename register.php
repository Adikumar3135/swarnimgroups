<!DOCTYPE html>
<html lang="en">
<head><link rel="icon" type="image/png" sizes="256x256" href="data/favicon.png"><link rel="apple-touch-icon" href="data/favicon.png">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Account — Swarnim Groups</title>
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="page">
<div class="auth-shell">
  <section class="visual">
    <div class="visual-inner">
      <a href="index.html" class="brand" data-page><img src="data/Swarnim Logo.png" alt="Swarnim Groups"><div>Swarnim Groups<small>Power • Comfort • Trust</small></div></a>
      <div class="visual-copy">
        <span class="eyebrow">Join Swarnim</span>
        <h1>Build Your<br><span>Power</span> Profile.</h1>
        <p>Create your account and stay connected with products, enquiries and services from Swarnim Groups.</p>
        <div class="visual-stat"><div class="stat"><strong>100%</strong><span>Genuine Products</span></div><div class="stat"><strong>24/7</strong><span>Support</span></div></div>
      </div>
      <div style="color:#8f99aa;font-size:.7rem">Power • Comfort • Energy</div>
    </div>
    <div class="energy"></div>
  </section>
  <section class="auth-area">
    <div class="auth-card">
      <a class="back" href="login.php" data-page>← Back to login</a>
      <div class="auth-head"><span class="eyebrow">New Account</span><h2>Create account.</h2><p>It only takes a minute to get started.</p></div>
      <div id="message" class="message"></div>
      <form class="form" id="registerForm">
        <div class="field"><label>Full Name</label><input id="regName" name="name" type="text" placeholder="Your full name" required></div>
        <div class="form-row-2"><div class="field"><label>Phone Number</label><input id="regPhone" name="phone" type="tel" placeholder="+91" required></div><div class="field"><label>Email Address</label><input id="regEmail" name="email" type="email" placeholder="you@example.com" required></div></div>
        <div class="field"><label>Password</label><div class="input-wrap"><input id="regPassword" name="password" type="password" placeholder="Minimum 8 characters" required><button class="eye" type="button" data-password="regPassword">◉</button></div><div class="password-meter"><i id="meter"></i></div></div>
        <div class="field"><label>Confirm Password</label><div class="input-wrap"><input id="regConfirm" type="password" placeholder="Repeat your password" required><button class="eye" type="button" data-password="regConfirm">◉</button></div></div>
        <label class="check"><input type="checkbox" required> I agree to the account terms and privacy policy.</label>
        <button class="btn" type="submit">Create My Account →</button>
      </form>
      <div class="switch">Already have an account? <a href="login.php" data-page>Sign in</a></div>
    </div>
  </section>
</div>
<script src="js/auth.js"></script>
</body>
</html>