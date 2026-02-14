<?php
require_once __DIR__ . '/_bootstrap.php';

$user = $_SESSION['user'] ?? null;
$isLoggedIn = !empty($user);
$isAdmin = $isLoggedIn && (($user['role'] ?? '') === 'admin');

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hypermarket Activities</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f6f7fb; color: #111; }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 24px; }
    .nav { background: #111; color: #fff; }
    .nav a { color: #fff; text-decoration: none; }
    .nav-inner { display:flex; gap: 16px; align-items:center; justify-content: space-between; padding: 14px 24px; }
    .brand { font-weight: 700; letter-spacing: .3px; }
    .menu { display:flex; gap: 14px; flex-wrap: wrap; align-items:center; }
    .pill { padding: 7px 10px; border-radius: 10px; background: rgba(255,255,255,.12); }
    .hero { background: #fff; border: 1px solid #e6e6e6; border-radius: 14px; padding: 22px; }
    .hero h1 { margin: 0 0 8px; font-size: 26px; }
    .muted { color: #555; }
    .grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 16px; }
    .card { background: #fff; border: 1px solid #e6e6e6; border-radius: 14px; padding: 16px; }
    .card h3 { margin: 0 0 6px; font-size: 16px; }
    .card p { margin: 0 0 12px; color: #555; font-size: 14px; }
    .btn { display:inline-block; padding: 9px 12px; border-radius: 10px; border: 1px solid #ddd; background: #f9f9f9; text-decoration:none; color:#111; }
    .btn:hover { background: #f1f1f1; }
    .row { display:flex; gap: 10px; flex-wrap: wrap; align-items:center; }
    .footer { margin-top: 18px; color:#666; font-size: 13px; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<div class="nav">
  <div class="nav-inner">
    <div class="brand">Hypermarket Activities</div>
    <div class="menu">
      <a class="pill" href="index.php">Home</a>
      <a class="pill" href="contact.php">Contact</a>
      <a class="pill" href="external.php">Parsare externă</a>

      <?php if ($isLoggedIn): ?>
        <a class="pill" href="dashboard.php">Dashboard</a>
        <a class="pill" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="pill" href="login.php">Login</a>
      <?php endif; ?>

      <?php if ($isAdmin): ?>
        <a class="pill" href="admin/categories.php">Admin: Categorii</a>
        <a class="pill" href="admin/products.php">Admin: Produse</a>
        <a class="pill" href="admin/analytics.php">Admin: Analytics</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="wrap">
  <div class="hero">
    <h1>Proiect DAW – Hypermarket Activities</h1>
    <p class="muted" style="margin:0 0 10px;">
      Aplicație web PHP + MySQL, construită pe baza bazei de date <b>hypermarket_activities</b>.
      Include autentificare cu roluri, operații CRUD, analytics (log accesări), trimitere email (formular contact),
      generare PDF și parsare informații externe.
    </p>

    <div class="row">
      <?php if ($isLoggedIn): ?>
        <span class="pill">Autentificat ca: <b><?php echo h($user['email']); ?></b> (<?php echo h($user['role']); ?>)</span>
        <a class="btn" href="dashboard.php">Mergi la Dashboard</a>
      <?php else: ?>
        <span class="pill">Nu ești autentificat</span>
        <a class="btn" href="login.php">Login</a>
      <?php endif; ?>

      <a class="btn" href="reports/products_pdf.php" target="_blank">Raport PDF Produse</a>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>CRUD (Admin)</h3>
      <p>Administrare categorii și produse (Create/Read/Update/Delete) pe tabelele din DB.</p>
      <div class="row">
        <a class="btn" href="admin/categories.php">Categorii</a>
        <a class="btn" href="admin/products.php">Produse</a>
      </div>
    </div>

    <div class="card">
      <h3>Analytics</h3>
      <p>Înregistrează accesările în <code>VisitLog</code> și afișează rapoarte: top pagini, ultimele 7 zile etc.</p>
      <a class="btn" href="admin/analytics.php">Vezi Analytics</a>
    </div>

    <div class="card">
      <h3>Contact (Email)</h3>
      <p>Formular de contact cu trimitere email prin SMTP/PHPMailer.</p>
      <a class="btn" href="contact.php">Deschide Contact</a>
    </div>

    <div class="card">
      <h3>Raport PDF</h3>
      <p>Generează PDF din DB (produse + categorie) folosind FPDF.</p>
      <a class="btn" href="reports/products_pdf.php" target="_blank">Generează PDF</a>
    </div>

    <div class="card">
      <h3>Parsare externă</h3>
      <p>Extrage date din surse externe (ex: IMDB list / DAB-it), cu cache local.</p>
      <a class="btn" href="external.php">Vezi rezultate</a>
    </div>

    <div class="card">
      <h3>Repo + Hosting</h3>
      <p>Proiectul va fi urcat pe GitHub și publicat pe hosting (linkuri trimise profesorului).</p>
      <span class="muted">Conturile demo se trimit evaluatorului pe email.</span>
    </div>
  </div>

  <div class="footer">
    <div>Tehnologii: PHP, PDO, MySQL/MariaDB, PHPMailer, FPDF.</div>
    <div>DB: <b>hypermarket_activities</b></div>
  </div>
</div>

</body>
</html>
