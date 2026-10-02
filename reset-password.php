<!DOCTYPE html>
<html lang="en">
<head><link rel="icon" type="image/png" sizes="256x256" href="data/favicon.png"><link rel="apple-touch-icon" href="data/favicon.png">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Set New Password — Swarnim Groups</title>
<link rel="stylesheet" href="css/auth.css">
</head>
<body class="page">
<div class="auth-shell">
<section class="visual"><div class="visual-inner">
<a href="index.html" class="brand" data-page><img src="data/Swarnim Logo.png" alt="Swarnim Groups"><div>Swarnim Groups<small>Power • Comfort • Trust</small></div></a>
<div class="visual-copy"><span class="eyebrow">Secure Recovery</span><h1>Create a<br><span>New Password.</span></h1><p>Choose a strong password for your Swarnim Groups account.</p></div>
<div style="color:#8f99aa;font-size:.7rem">Secure account recovery</div></div><div class="energy"></div></section>
<section class="auth-area"><div class="auth-card">
<a class="back" href="login.php" data-page>← Back to login</a>
<div class="auth-head"><span class="eyebrow">New Password</span><h2>Set password.</h2><p>Your reset link has been verified.</p></div>
<div id="message" class="message"></div>
<form class="form" id="resetForm">
<div class="field"><label>New Password</label><div class="input-wrap"><input id="newPassword" name="password" type="password" minlength="8" placeholder="Minimum 8 characters" required><button class="eye" type="button" data-password="newPassword">◉</button></div></div>
<div class="field"><label>Confirm New Password</label><div class="input-wrap"><input id="newConfirm" type="password" minlength="8" placeholder="Repeat your new password" required><button class="eye" type="button" data-password="newConfirm">◉</button></div></div>
<button class="btn" type="submit">Update Password →</button>
</form>
<div class="switch">Remember your password? <a href="login.php" data-page>Back to login</a></div>
</div></section></div>
<script src="js/auth.js"></script>
</body>
</html>