<?php
session_start();
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../app/Database.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT ID_User, Email, PasswordHash, Role FROM Users WHERE Email = :e LIMIT 1");
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['PasswordHash'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['ID_User'],
            'email' => $user['Email'],
            'role' => $user['Role'],
        ];
        header('Location: dashboard.php');
        exit;
    }

    $error = "Email sau parolă greșită.";
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
<h2>Login</h2>

<?php if ($error): ?>
  <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">
  <label>Email:</label><br>
  <input name="email" type="email" required><br><br>

  <label>Password:</label><br>
  <input name="password" type="password" required><br><br>

  <button type="submit">Login</button>
</form>
</body>
</html>
