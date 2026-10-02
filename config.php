<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
    header('Cross-Origin-Opener-Policy: same-origin');
}

function loadEnv(string $file): void {
    if (!is_file($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if (($value[0] ?? '') === '"' && substr($value, -1) === '"') $value = substr($value, 1, -1);
        if ($key !== '') $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . '/.env');

function envv(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dsn = 'mysql:host=' . envv('DB_HOST', 'localhost') .
           ';dbname=' . envv('DB_NAME', 'swarnim_groups') .
           ';charset=utf8mb4';

    $pdo = new PDO($dsn, envv('DB_USER', 'root'), envv('DB_PASS', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    return $pdo;
}

function jsonResponse(bool $ok, string $message = '', array $data = [], int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Invalid request method.', [], 405);
}

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function requireCsrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) jsonResponse(false, 'Security token expired. Refresh and try again.', [], 419);
}

function clientIp(): string {
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function sendSmtp(string $to, string $subject, string $html): bool {
    $host = envv('SMTP_HOST');
    $port = (int) envv('SMTP_PORT', '587');
    $user = envv('SMTP_USER');
    $pass = envv('SMTP_PASS');
    $from = envv('MAIL_FROM', $user);
    $fromName = envv('MAIL_FROM_NAME', 'Swarnim Groups');

    if (!$host || !$user || !$pass || !$from) return false;

    $transport = $port === 465 ? 'ssl://' . $host . ':' . $port : 'tcp://' . $host . ':' . $port;
    $errno = 0; $errstr = '';
    $socket = @stream_socket_client($transport, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) return false;

    stream_set_timeout($socket, 15);

    $read = function() use ($socket): string {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $write = function(string $cmd) use ($socket): void {
        fwrite($socket, $cmd . "\r\n");
    };
    $ok = function(string $response, array $codes): bool {
        $code = (int) substr($response, 0, 3);
        return in_array($code, $codes, true);
    };

    $greeting = $read();
    if (!$ok($greeting, [220])) { fclose($socket); return false; }

    $write('EHLO swarnimgroups.local');
    if (!$ok($read(), [250])) { fclose($socket); return false; }

    if ($port !== 465) {
        $write('STARTTLS');
        if (!$ok($read(), [220])) { fclose($socket); return false; }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
        $write('EHLO swarnimgroups.local');
        if (!$ok($read(), [250])) { fclose($socket); return false; }
    }

    $write('AUTH LOGIN');
    if (!$ok($read(), [334])) { fclose($socket); return false; }
    $write(base64_encode($user));
    if (!$ok($read(), [334])) { fclose($socket); return false; }
    $write(base64_encode($pass));
    if (!$ok($read(), [235])) { fclose($socket); return false; }

    $write('MAIL FROM:<' . $from . '>');
    if (!$ok($read(), [250])) { fclose($socket); return false; }
    $write('RCPT TO:<' . $to . '>');
    if (!$ok($read(), [250, 251])) { fclose($socket); return false; }
    $write('DATA');
    if (!$ok($read(), [354])) { fclose($socket); return false; }

    $safeSubject = str_replace(["\r", "\n"], '', $subject);
    $headers = "From: " . $fromName . " <" . $from . ">\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $safeSubject . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Date: " . date(DATE_RFC2822) . "\r\n";
    $body = $headers . "\r\n" . $html;
    $body = preg_replace('/^\./m', '..', $body);
    fwrite($socket, $body . "\r\n.\r\n");
    $result = $read();
    $write('QUIT');
    fclose($socket);
    return $ok($result, [250]);
}

function appUrl(string $path = ''): string {
    return rtrim(envv('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function loggedUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
}


function maintenanceEnabled(): bool {
    try {
        $pdo = db();
        $row = $pdo->query("SELECT value FROM site_settings WHERE setting_key='maintenance_mode' LIMIT 1")->fetch();
        return $row && (string)$row['value'] === '1';
    } catch (Throwable $e) { return false; }
}
function enforceMaintenance(): void {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = basename($uri);
    $publicExempt = ['maintenance.php','login.php','register.php','forgot-password.php','request-reset.php','reset-password-api.php','login-api.php','register-api.php','auth-status.php','logout.php','admin-login.php','employee-login.php','employee','admin'];
    foreach ($publicExempt as $part) { if (stripos($uri, '/' . $part) !== false || stripos($uri, $part) === 0) return; }
    if (maintenanceEnabled() && (($_SESSION['user']['role'] ?? '') !== 'admin')) {
        header('Location: ' . appUrl('maintenance.php')); exit;
    }
}

csrfToken();

// Maintenance mode is surfaced only from the cart/checkout shopping flow.
// Public browsing remains available while maintenance mode is enabled.
