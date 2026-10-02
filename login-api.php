<?php
require __DIR__ . '/config.php';
requirePost();
requireCsrf();

$email = strtolower(trim($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$expectedRole = trim((string)($_POST['expected_role'] ?? ''));
if ($expectedRole !== '' && !in_array($expectedRole, ['admin','employee','user'], true)) $expectedRole = '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    jsonResponse(false, 'Enter a valid email and password.', [], 422);
}

$rate = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND success = 0 AND created_at >= (NOW() - INTERVAL 15 MINUTE)');
$rate->execute([$email, clientIp()]);
if ((int)$rate->fetchColumn() >= 8) {
    jsonResponse(false, 'Too many failed login attempts. Please try again later.', [], 429);
}

$stmt = db()->prepare('SELECT id,name,email,password,role,status FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

$valid = $user && $user['status'] === 'active' && password_verify($password, $user['password']);
if ($valid && $expectedRole !== '' && $user['role'] !== $expectedRole) $valid = false;

$log = db()->prepare('INSERT INTO login_attempts (user_id,email,ip_address,user_agent,success) VALUES (?,?,?,?,?)');
$log->execute([$user['id'] ?? null, $email, clientIp(), substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,1000), $valid ? 1 : 0]);

if (!$valid) jsonResponse(false, 'Email or password is incorrect.', [], 401);

loginUser($user);
$redirect = match ($_SESSION['user']['role']) { 'admin' => 'admin/index.php', 'employee' => 'employee/index.php', default => 'index.php' };
jsonResponse(true, 'Login successful.', ['user' => $_SESSION['user'], 'redirect' => $redirect]);
