<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../app/Database.php';
require_once __DIR__ . '/../../app/auth.php';

require_role('admin');

$pdo = Database::pdo();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Totals
$total = (int)$pdo->query("SELECT COUNT(*) AS c FROM VisitLog")->fetch()['c'];

// Last 7 days visits
$last7 = $pdo->query("
    SELECT DATE(VisitedAt) AS day, COUNT(*) AS visits, COUNT(DISTINCT IPHash) AS unique_visitors
    FROM VisitLog
    WHERE VisitedAt >= (NOW() - INTERVAL 7 DAY)
    GROUP BY DATE(VisitedAt)
    ORDER BY day DESC
")->fetchAll();

// Top pages (all time)
$topPages = $pdo->query("
    SELECT Path, COUNT(*) AS visits
    FROM VisitLog
    GROUP BY Path
    ORDER BY visits DESC
    LIMIT 10
")->fetchAll();

// Logged-in vs anonymous
$authSplit = $pdo->query("
    SELECT
      SUM(CASE WHEN ID_User IS NULL THEN 1 ELSE 0 END) AS anonymous,
      SUM(CASE WHEN ID_User IS NOT NULL THEN 1 ELSE 0 END) AS logged_in
    FROM VisitLog
")->fetch();
$anonymous = (int)($authSplit['anonymous'] ?? 0);
$loggedIn  = (int)($authSplit['logged_in'] ?? 0);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Analytics</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 10px; }
    th { background: #f3f3f3; text-align: left; }
    .top { display:flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .cards { display:flex; gap: 12px; flex-wrap: wrap; margin: 10px 0 20px; }
    .card { border: 1px solid #ddd; border-radius: 8px; padding: 12px; min-width: 220px; }
    a { text-decoration: none; }
  </style>
</head>
<body>

<div class="top">
  <div><b>Admin</b> → Analytics</div>
  <div>
    <a href="../dashboard.php">Dashboard</a> |
    <a href="categories.php">Categorii</a> |
    <a href="products.php">Produse</a> |
    <a href="../logout.php">Logout</a>
  </div>
</div>

<div class="cards">
  <div class="card">
    <div>Total visits</div>
    <h2><?php echo $total; ?></h2>
  </div>
  <div class="card">
    <div>Logged-in vs Anonymous</div>
    <p>Logged-in: <b><?php echo $loggedIn; ?></b></p>
    <p>Anonymous: <b><?php echo $anonymous; ?></b></p>
  </div>
</div>

<h3>Last 7 days</h3>
<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Visits</th>
      <th>Unique visitors (IPHash)</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($last7 as $row): ?>
      <tr>
        <td><?php echo h((string)$row['day']); ?></td>
        <td><?php echo (int)$row['visits']; ?></td>
        <td><?php echo (int)$row['unique_visitors']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Top pages</h3>
<table>
  <thead>
    <tr>
      <th>Path</th>
      <th>Visits</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($topPages as $row): ?>
      <tr>
        <td><?php echo h((string)$row['Path']); ?></td>
        <td><?php echo (int)$row['visits']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
