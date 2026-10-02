<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
$user=loggedUser();
if(!$user || ($user['role']??'')!=='admin'){http_response_code(403);exit('Admin access required.');}
function ae($v):string{return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');}
function adminCsrf():string{return csrfToken();}
