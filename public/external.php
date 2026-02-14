<?php
require_once __DIR__ . '/_bootstrap.php';

require_once __DIR__ . '/../app/parsers/ExternalFetch.php';
require_once __DIR__ . '/../app/parsers/ImdbListParser.php';
require_once __DIR__ . '/../app/parsers/DabItParser.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$source = $_GET['src'] ?? 'imdb';

// Linkurile sunt în cod ca să fie clar în demo.
// Dacă IMDB blochează pe hosting, cache-ul te ajută după primul fetch reușit.
$imdbUrl = "https://www.imdb.com/list/ls003569487/";
$dabUrl  = "https://www.dab-it.ro/retelistica"; // poți schimba cu linkul exact de la curs/lab

$data = [];
$error = null;
$note = null;

if ($source === 'imdb') {
    $res = ExternalFetch::get($imdbUrl, 'imdb_list', 3600);
    if (!$res['ok']) {
        $error = $res['error'];
    } else {
        $note = $res['error'] ?: ($res['from_cache'] ? 'Loaded from cache.' : null);
        $data = ImdbListParser::parseList($res['html'], 10);
        if (count($data) === 0) $error = "Nu am putut extrage item-uri (posibil layout diferit / blocaj).";
    }
} else {
    $res = ExternalFetch::get($dabUrl, 'dab_it', 3600);
    if (!$res['ok']) {
        $error = $res['error'];
    } else {
        $note = $res['error'] ?: ($res['from_cache'] ? 'Loaded from cache.' : null);
        $data = DabItParser::parseProducts($res['html'], 10);
        if (count($data) === 0) $error = "Nu am putut extrage produse (posibil layout diferit).";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>External Parsing</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; }
    .row { display:flex; gap: 10px; align-items:center; flex-wrap:wrap; }
    .msg { padding: 10px; margin: 10px 0; border-radius: 6px; }
    .err { background: #fde8e8; border: 1px solid #f3b1b1; }
    .note { background: #eef5ff; border: 1px solid #b8d7ff; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 10px; }
    th { background: #f3f3f3; text-align: left; }
    a { text-decoration: none; }
  </style>
</head>
<body>

<h2>Parsare informații externe</h2>

<div class="row">
  <a href="external.php?src=imdb">IMDB list</a> |
  <a href="external.php?src=dab">DAB-it (best-effort)</a> |
  <a href="dashboard.php">Dashboard</a>
</div>

<?php if ($note): ?>
  <div class="msg note"><?php echo h($note); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="msg err"><?php echo h($error); ?></div>
<?php endif; ?>

<?php if ($source === 'imdb' && count($data) > 0): ?>
  <h3>IMDB – rezultate</h3>
  <table>
    <thead>
      <tr><th>#</th><th>Titlu</th><th>An</th><th>Rating</th></tr>
    </thead>
    <tbody>
      <?php foreach ($data as $i => $r): ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><?php echo h($r['title']); ?></td>
          <td><?php echo h($r['year']); ?></td>
          <td><?php echo h($r['rating']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php if ($source !== 'imdb' && count($data) > 0): ?>
  <h3>DAB-it – rezultate</h3>
  <table>
    <thead>
      <tr><th>#</th><th>Produs</th><th>Preț</th><th>Link</th></tr>
    </thead>
    <tbody>
      <?php foreach ($data as $i => $r): ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><?php echo h($r['name']); ?></td>
          <td><?php echo h($r['price']); ?></td>
          <td><?php echo h($r['href']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

</body>
</html>
