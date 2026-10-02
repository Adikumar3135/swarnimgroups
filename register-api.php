<?php
require __DIR__ . '/config.php';
requirePost();
requireCsrf();

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if (mb_strlen($name) < 2) jsonResponse(false, 'Enter your full name.', [], 422);
if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) jsonResponse(false, 'Enter a valid phone number.', [], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, 'Enter a valid email address.', [], 422);
if (strlen($password) < 8) jsonResponse(false, 'Password must contain at least 8 characters.', [], 422);

$stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) jsonResponse(false, 'An account with this email already exists.', [], 409);

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare('INSERT INTO users (name,phone,email,password,role,status,email_verified) VALUES (?,?,?,?,?,?,?)');
$stmt->execute([$name,$phone,$email,$hash,'user','active',1]);

$id = (int)db()->lastInsertId();
loginUser(['id'=>$id,'name'=>$name,'email'=>$email,'role'=>'user']);

jsonResponse(true, 'Account created successfully.', ['user'=>$_SESSION['user']]);
