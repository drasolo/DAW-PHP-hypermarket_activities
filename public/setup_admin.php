<?php
require_once __DIR__ . '/../app/Database.php';

$email = 'admin@demo.ro';
$pass  = 'Admin123!';

$pdo = Database::pdo();

$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO Users (Email, PasswordHash, Role) VALUES (:e, :p, 'admin')");
$stmt->execute([':e' => $email, ':p' => $hash]);

echo "Admin created: {$email} / {$pass}";
