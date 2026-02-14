<?php
require_once __DIR__ . '/../app/Database.php';

try {
    $pdo = Database::pdo();
    $dbName = $pdo->query('SELECT DATABASE() AS db')->fetch()['db'] ?? 'n/a';
    $tables = $pdo->query('SHOW TABLES')->fetchAll();
    echo "DB OK: {$dbName}<br>Tables: " . count($tables);
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB FAIL: " . htmlspecialchars($e->getMessage());
}
