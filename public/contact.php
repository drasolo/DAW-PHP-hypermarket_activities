<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/MailerService.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $token)) {
        http_response_code(400);
        exit('Bad CSRF token');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || mb_strlen($name) > 100) {
        $error = "Nume invalid.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalid.";
    } elseif ($message === '' || mb_strlen($message) > 3000) {
        $error = "Mesaj invalid (1–3000 caractere).";
    } else {
        try {
            MailerService::sendContactMail($email, $name, $message);
            $success = "Mesaj trimis cu succes!";
        } catch (Throwable $e) {
            $error = "Eroare la trimitere email: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Contact</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 700px; margin: 30px auto; }
    .msg { padding: 10px; margin: 10px 0; border-radius: 6px; }
    .ok { background: #e7f7ea; border: 1px solid #b9e3c0; }
    .err { background: #fde8e8; border: 1px solid #f3b1b1; }
    input, textarea { width: 100%; padding: 10px; margin: 6px 0 12px; }
    button { padding: 10px 14px; cursor: pointer; }
  </style>
</head>
<body>

<h2>Contact</h2>

<?php if ($success): ?>
  <div class="msg ok"><?php echo h($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="msg err"><?php echo h($error); ?></div>
<?php endif; ?>

<form method="post">
  <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">

  <label>Nume</label>
  <input name="name" required maxlength="100">

  <label>Email</label>
  <input name="email" type="email" required>

  <label>Mesaj</label>
  <textarea name="message" rows="6" required maxlength="3000"></textarea>

  <button type="submit">Trimite</button>
</form>

<p><a href="dashboard.php">Dashboard</a></p>
</body>
</html>
