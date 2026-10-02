<?php
require __DIR__ . '/config.php';
requirePost();
requireCsrf();

$token = trim($_POST['token'] ?? '');
$password = (string)($_POST['password'] ?? '');

if (!preg_match('/^[a-f0-9]{64}$/', $token)) jsonResponse(false, 'Invalid or expired reset link.', [], 422);
if (strlen($password) < 8) jsonResponse(false, 'Password must contain at least 8 characters.', [], 422);

$hash = hash('sha256', $token);
$stmt = db()->prepare('SELECT id,user_id FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
$stmt->execute([$hash]);
$reset = $stmt->fetch();

if (!$reset) jsonResponse(false, 'This reset link is invalid or has expired.', [], 410);

$newHash = password_hash($password, PASSWORD_DEFAULT);
db()->beginTransaction();
try {
    db()->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?')->execute([$newHash,$reset['user_id']]);
    db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
    db()->commit();
} catch (Throwable $e) {
    db()->rollBack();
    jsonResponse(false, 'Unable to reset password right now.', [], 500);
}

jsonResponse(true, 'Password reset successfully.');
