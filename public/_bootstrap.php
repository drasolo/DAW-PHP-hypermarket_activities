<?php
// public/_bootstrap.php
// Include this at the top of every public page.

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../app/Database.php';

// 1) Compute clean path (without query string)
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$pathOnly = parse_url($uri, PHP_URL_PATH) ?: '/';

// 2) Skip logging for assets (optional)
$ext = strtolower(pathinfo($pathOnly, PATHINFO_EXTENSION));
$skipExt = ['css','js','png','jpg','jpeg','gif','svg','ico','webp','map','woff','woff2','ttf'];
if ($ext && in_array($ext, $skipExt, true)) {
    return;
}

// 3) User id if logged in
$userId = null;
if (!empty($_SESSION['user']['id'])) {
    $userId = (int)$_SESSION['user']['id'];
}

// 4) Privacy-friendly visitor hash
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ipHash = $ip ? hash('sha256', $ip) : null;

// 5) User agent truncated to fit VARCHAR(255)
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ua = $ua ? substr($ua, 0, 255) : null;

try {
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("
        INSERT INTO VisitLog (Path, ID_User, IPHash, UserAgent)
        VALUES (:p, :u, :ip, :ua)
    ");
    $stmt->execute([
        ':p'  => $pathOnly,
        ':u'  => $userId,
        ':ip' => $ipHash,
        ':ua' => $ua,
    ]);
} catch (Throwable $e) {
    // In production you might log this. For now, ignore to not break pages.
}
