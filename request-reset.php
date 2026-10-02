<?php
require __DIR__ . '/config.php';
requirePost();
requireCsrf();

$email = strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, 'Enter a valid email address.', [], 422);

$stmt = db()->prepare('SELECT id,name,email FROM users WHERE email = ? AND status = "active" LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

$message = 'If an account exists for this email, a password reset link has been sent.';

if ($user) {
    db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')->execute([$user['id']]);

    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + 1800);

    db()->prepare('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,?)')
        ->execute([$user['id'],$hash,$expires]);

    $link = appUrl('reset-password.php?token=' . urlencode($token));
    $safeName = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');

    $html = '<div style="font-family:Arial,sans-serif;max-width:620px;margin:auto;padding:35px;color:#172033">' .
        '<h2 style="color:#b77a00">Swarnim Groups</h2>' .
        '<p>Hello ' . $safeName . ',</p>' .
        '<p>We received a request to reset your Swarnim Groups password.</p>' .
        '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:13px 20px;background:#f6c64d;color:#111;text-decoration:none;border-radius:8px;font-weight:bold">Reset Password</a></p>' .
        '<p>This link expires in 30 minutes and can be used only once.</p>' .
        '<p>If you did not request this, you can safely ignore this email.</p>' .
        '<p>— Swarnim Groups</p></div>';

    if (!sendSmtp($user['email'], 'Reset your Swarnim Groups password', $html)) {
        jsonResponse(false, 'The reset email could not be sent. Check the SMTP settings in .env.', [], 500);
    }
}

jsonResponse(true, $message);
