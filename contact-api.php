<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
requirePost();
requireCsrf();

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$interest = trim($_POST['interest'] ?? '');
$message = trim($_POST['message'] ?? '');
$consent = isset($_POST['consent']) && $_POST['consent'] === '1';

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    jsonResponse(false, 'Please enter a valid full name.', [], 422);
}
if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
    jsonResponse(false, 'Please enter a valid phone number.', [], 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.', [], 422);
}
$allowed = ['Battery','Cooler','AC','Chimney','Solar Plates','Inverter','Other / Multiple'];
if (!in_array($interest, $allowed, true)) {
    jsonResponse(false, 'Please choose a valid category.', [], 422);
}
if (mb_strlen($message) > 5000) {
    jsonResponse(false, 'Message is too long.', [], 422);
}
if (!$consent) {
    jsonResponse(false, 'Please allow us to contact you about your enquiry.', [], 422);
}

$user = loggedUser();
$stmt = db()->prepare('INSERT INTO contact_messages (user_id,name,phone,email,interest,message,consent,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,?,?)');
$stmt->execute([
    $user['id'] ?? null,
    $name,
    $phone,
    $email !== '' ? $email : null,
    $interest,
    $message !== '' ? $message : null,
    1,
    clientIp(),
    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000)
]);

jsonResponse(true, 'Your message has been sent successfully. Our team will contact you soon.', [
    'id' => (int)db()->lastInsertId()
]);
