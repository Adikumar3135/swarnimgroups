<!DOCTYPE html>
<html lang="en">

<head><link rel="icon" type="image/png" sizes="256x256" href="data/favicon.png"><link rel="apple-touch-icon" href="data/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Login — Swarnim Groups</title>
  <link rel="stylesheet" href="css/auth.css">
</head>

<body class="page">
  <div class="auth-shell">
    <section class="visual">
      <div class="visual-inner">
        <a href="index.html" class="brand" data-page><img src="data/Swarnim Logo.png" alt="Swarnim Groups">
          <div>Swarnim Groups<small>Power • Comfort • Trust</small></div>
        </a>
        <div class="visual-copy">
          <span class="eyebrow">Member Access</span>
          <h1>Welcome<br>Back to <span>Power.</span></h1>
          <p>Sign in to continue with your Swarnim Groups account and access your personalized experience.</p>
          <div class="visual-stat">
            <div class="stat"><strong>15+</strong><span>Years Experience</span></div>
            <div class="stat"><strong>12k+</strong><span>Customers</span></div>
            <div class="stat"><strong>50+</strong><span>Brands</span></div>
          </div>
        </div>
        <div style="color:#8f99aa;font-size:.7rem">© 2026 Swarnim Groups</div>
      </div>
      <div class="energy"></div>
    </section>
    <section class="auth-area">
      <div class="auth-card">
        <a class="back" href="index.html" data-page>← Back to website</a>
        <div class="auth-head"><span class="eyebrow">Secure Login</span>
          <h2>Sign in.</h2>
          <p>Enter your account details to continue.</p>
        </div>
        <div id="message" class="message"></div>
        <form class="form" id="loginForm">
          <div class="field"><label>Email Address</label><input id="loginEmail" name="email" type="email"
              placeholder="you@example.com" required></div>
          <div class="field"><label>Password</label>
            <div class="input-wrap"><input id="loginPassword" name="password" type="password" placeholder="Enter your password"
                required><button class="eye" type="button" data-password="loginPassword">◉</button></div>
          </div>
          <div class="row"><label class="check"><input type="checkbox"> Remember me</label><a class="link"
              href="forgot-password.php" data-page>Forgot password?</a></div>
          <button class="btn" type="submit">Login to Swarnim →</button>
          <div class="divider">OR CONTINUE WITH</div>
          <div class="social"><button type="button">G Google</button><button type="button">f Facebook</button></div>
        </form>
        <div class="switch">Don't have an account? <a href="register.php" data-page>Create account</a></div>
      </div>
    </section>
  </div>
  <script src="js/auth.js"></script>
</body>

</html>