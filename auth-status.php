<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
jsonResponse(true, '', ['authenticated' => isset($_SESSION['user']), 'user' => $_SESSION['user'] ?? null, 'csrf' => csrfToken()]);
