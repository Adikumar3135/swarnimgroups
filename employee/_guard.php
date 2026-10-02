<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
$user=loggedUser();
if(!$user || ($user['role']??'')!=='employee'){http_response_code(403);exit('Employee access required.');}
function ee($v):string{return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');}
