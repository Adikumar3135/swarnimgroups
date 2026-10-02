<!DOCTYPE html>
<html lang="en">

<head><link rel="icon" type="image/png" sizes="256x256" href="data/favicon.png"><link rel="apple-touch-icon" href="data/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Forgot Password — Swarnim Groups</title>
  <link rel="stylesheet" href="css/auth.css">
</head>

<body class="page">
  <div class="auth-shell">
    <section class="visual">
      <div class="visual-inner">
        <a href="index.html" class="brand" data-page><img src="data/Swarnim Logo.png" alt="Swarnim Groups">
          <div>Swarnim Groups<small>Power • Comfort • Trust</small></div>
        </a>
        <div class="visual-copy"><span class="eyebrow">Account Recovery</span>
          <h1>Reset.<br>Recover.<br><span>Reconnect.</span></h1>
          <p>Enter your registered email. A secure password-reset link will be sent directly to your inbox.</p>
        </div>
        <div style="color:#8f99aa;font-size:.7rem">Secure account recovery</div>
      </div>
      <div class="energy"></div>
    </section>
    <section class="auth-area">
      <div class="auth-card">
        <a class="back" href="login.php" data-page>← Back to login</a>
        <div class="auth-head"><span class="eyebrow">Password Recovery</span>
          <h2>Forgot password?</h2>
          <p>We'll send a secure reset link to your registered email address.</p>
        </div>
        <div id="message" class="message"></div>
        <form class="form" id="forgotForm">
          <div class="field"><label>Account Email</label><input id="forgotEmail" name="email" type="email"
              placeholder="you@example.com" required></div>
          <button class="btn" type="submit">Send Reset Link →</button>
        </form>
        <div class="switch">Remember your password? <a href="login.php" data-page>Back to login</a></div>
      </div>
    </section>
  </div>
  <script src="js/auth.js"></script>
</body>

</html>