<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$user = loggedUser();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Admin login required.');
}
$rows = db()->query('SELECT cm.*, u.email AS account_email FROM contact_messages cm LEFT JOIN users u ON u.id = cm.user_id ORDER BY cm.created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="data/Swarnim Logo.png">
<link rel="apple-touch-icon" href="data/Swarnim Logo.png">

<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact Messages — Swarnim Groups</title>
<style>
body{font-family:Arial,sans-serif;background:#070a12;color:#eef2f7;margin:0;padding:30px}.wrap{max-width:1250px;margin:auto}h1{margin:0 0 8px}.sub{color:#9da7b7;margin-bottom:25px}.top{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:20px}.btn{color:#111;background:#f5c542;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700}.table{overflow:auto;border:1px solid #273044;border-radius:16px}table{width:100%;border-collapse:collapse;min-width:1050px}th,td{padding:13px;border-bottom:1px solid #20283a;text-align:left;vertical-align:top}th{background:#111827;color:#f5c542}td{color:#d7ddea}.status{font-weight:700;text-transform:capitalize}.message{white-space:pre-wrap;max-width:330px}.empty{text-align:center;padding:40px;color:#9da7b7}
</style></head><body><div class="wrap"><div class="top"><div><h1>Contact Messages</h1><div class="sub">Swarnim Groups enquiries received from the website.</div></div><a class="btn" href="index.html">Back to Website</a></div><div class="table"><table><thead><tr><th>ID</th><th>Date</th><th>Name</th><th>Phone</th><th>Email</th><th>Interest</th><th>Message</th><th>Status</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td colspan="8" class="empty">No contact messages yet.</td></tr><?php else: foreach ($rows as $row): ?><tr><td><?= (int)$row['id'] ?></td><td><?= htmlspecialchars($row['created_at']) ?></td><td><?= htmlspecialchars($row['name']) ?></td><td><?= htmlspecialchars($row['phone']) ?></td><td><?= htmlspecialchars($row['email'] ?? '') ?></td><td><?= htmlspecialchars($row['interest']) ?></td><td class="message"><?= htmlspecialchars($row['message'] ?? '') ?></td><td class="status"><?= htmlspecialchars($row['status']) ?></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div></body></html>
