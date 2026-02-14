<?php
session_start();
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/auth.php';

require_login();

$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body>
<h2>Dashboard</h2>
<p>Logged in as: <b><?php echo htmlspecialchars($user['email']); ?></b> (role: <b><?php echo htmlspecialchars($user['role']); ?></b>)</p>

<ul>
  <li><a href="logout.php">Logout</a></li>
  <li><a href="admin_only.php">Admin only page</a></li>
  <li><a href="external.php" target="_blank">Parsare externă</a></li>
</ul>
</body>
</html>
