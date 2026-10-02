<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$user = loggedUser();
if (!$user) jsonResponse(false, 'Please login to continue.', [], 401);
requirePost();
requireCsrf();

$pdo = db();
$userId = (int)$user['id'];
$action = trim((string)($_POST['action'] ?? ''));

function profileUser(PDO $pdo, int $id): array {
    $s = $pdo->prepare('SELECT id,name,phone,email,role,status,email_verified,avatar_url,created_at,updated_at FROM users WHERE id=? LIMIT 1');
    $s->execute([$id]);
    $u = $s->fetch();
    if (!$u) jsonResponse(false, 'User account not found.', [], 404);
    return $u;
}

if ($action === 'update-profile') {
    $name = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) jsonResponse(false, 'Enter a valid name.');
    if (!preg_match('/^[0-9+()\-\s]{7,20}$/', $phone)) jsonResponse(false, 'Enter a valid phone number.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, 'Enter a valid email address.');
    $check = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) jsonResponse(false, 'This email is already registered.');
    $s = $pdo->prepare('UPDATE users SET name=?, phone=?, email=? WHERE id=?');
    $s->execute([$name,$phone,$email,$userId]);
    $_SESSION['user']['name']=$name;
    $_SESSION['user']['email']=$email;
    jsonResponse(true, 'Profile updated successfully.', ['user'=>profileUser($pdo,$userId)]);
}

if ($action === 'change-password') {
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $s = $pdo->prepare('SELECT password FROM users WHERE id=?');
    $s->execute([$userId]);
    $row = $s->fetch();
    if (!$row || !password_verify($current, $row['password'])) jsonResponse(false, 'Current password is incorrect.');
    if (strlen($new) < 8) jsonResponse(false, 'New password must be at least 8 characters.');
    if ($new !== $confirm) jsonResponse(false, 'New passwords do not match.');
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash,$userId]);
    jsonResponse(true, 'Password changed successfully.');
}

if ($action === 'upload-avatar') {
    if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) jsonResponse(false, 'Please choose an image.');
    $file = $_FILES['avatar'];
    if ((int)$file['size'] > 2 * 1024 * 1024) jsonResponse(false, 'Image must be 2 MB or smaller.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime])) jsonResponse(false, 'Only JPG, PNG and WEBP images are allowed.');
    $dir = __DIR__ . '/uploads/profile';
    if (!is_dir($dir) && !mkdir($dir,0755,true)) jsonResponse(false, 'Could not create upload directory.');
    $name = 'user-' . $userId . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) jsonResponse(false, 'Could not save the image.');
    $relative = 'uploads/profile/' . $name;
    $old = profileUser($pdo,$userId)['avatar_url'] ?? null;
    $pdo->prepare('UPDATE users SET avatar_url=? WHERE id=?')->execute([$relative,$userId]);
    if ($old && str_starts_with($old,'uploads/profile/')) {
        $oldPath = __DIR__ . '/' . $old;
        if (is_file($oldPath)) @unlink($oldPath);
    }
    jsonResponse(true, 'Profile photo updated.', ['avatar_url'=>$relative]);
}

if ($action === 'delete-avatar') {
    $u = profileUser($pdo,$userId);
    if (!empty($u['avatar_url']) && str_starts_with($u['avatar_url'],'uploads/profile/')) {
        $path = __DIR__ . '/' . $u['avatar_url'];
        if (is_file($path)) @unlink($path);
    }
    $pdo->prepare('UPDATE users SET avatar_url=NULL WHERE id=?')->execute([$userId]);
    jsonResponse(true, 'Profile photo removed.');
}

if ($action === 'add-address') {
    $line1=trim((string)($_POST['address_line1']??''));
    $line2=trim((string)($_POST['address_line2']??''));
    $landmark=trim((string)($_POST['landmark']??''));
    $city=trim((string)($_POST['city']??''));
    $state=trim((string)($_POST['state']??''));
    $pincode=trim((string)($_POST['pincode']??''));
    $type=trim((string)($_POST['address_type']??'home'));
    $default=!empty($_POST['is_default']) ? 1 : 0;
    if ($line1==='' || $city==='' || $state==='' || !preg_match('/^[0-9]{6}$/',$pincode)) jsonResponse(false,'Complete the address and enter a valid 6-digit pincode.');
    if (!in_array($type,['home','work','other'],true)) $type='other';
    if ($default) $pdo->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=?')->execute([$userId]);
    $s=$pdo->prepare('INSERT INTO user_addresses(user_id,address_line1,address_line2,landmark,city,state,pincode,address_type,is_default) VALUES(?,?,?,?,?,?,?,?,?)');
    $s->execute([$userId,$line1,$line2?:null,$landmark?:null,$city,$state,$pincode,$type,$default]);
    if (!$default) {
        $count=$pdo->prepare('SELECT COUNT(*) FROM user_addresses WHERE user_id=?'); $count->execute([$userId]);
        if ((int)$count->fetchColumn()===1) $pdo->prepare('UPDATE user_addresses SET is_default=1 WHERE id=?')->execute([(int)$pdo->lastInsertId()]);
    }
    jsonResponse(true,'Address saved successfully.');
}

if ($action === 'delete-address') {
    $id=(int)($_POST['address_id']??0);
    $s=$pdo->prepare('DELETE FROM user_addresses WHERE id=? AND user_id=?'); $s->execute([$id,$userId]);
    if (!$s->rowCount()) jsonResponse(false,'Address not found.');
    $remaining=$pdo->prepare('SELECT id FROM user_addresses WHERE user_id=? ORDER BY is_default DESC,id ASC LIMIT 1'); $remaining->execute([$userId]);
    if ($r=$remaining->fetch()) $pdo->prepare('UPDATE user_addresses SET is_default=1 WHERE id=?')->execute([(int)$r['id']]);
    jsonResponse(true,'Address removed.');
}

if ($action === 'set-default-address') {
    $id=(int)($_POST['address_id']??0);
    $s=$pdo->prepare('SELECT id FROM user_addresses WHERE id=? AND user_id=?'); $s->execute([$id,$userId]);
    if (!$s->fetch()) jsonResponse(false,'Address not found.');
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=?')->execute([$userId]);
    $pdo->prepare('UPDATE user_addresses SET is_default=1 WHERE id=? AND user_id=?')->execute([$id,$userId]);
    $pdo->commit();
    jsonResponse(true,'Default address updated.');
}

jsonResponse(false,'Unknown profile action.',[],400);
