<?php
session_start();
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../../app/Database.php';
require_once __DIR__ . '/../../app/auth.php';

require_role('admin');

$pdo = Database::pdo();

// CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$error = null;
$success = null;

// Handle POST actions (create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $token)) {
        http_response_code(400);
        exit('Bad CSRF token');
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '' || mb_strlen($name) > 50) {
                throw new Exception("Numele categoriei trebuie să fie între 1 și 50 caractere.");
            }

            $stmt = $pdo->prepare("INSERT INTO CategoriiProduse (Nume) VALUES (:n)");
            $stmt->execute([':n' => $name]);
            $success = "Categorie adăugată.";

        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');

            if ($id <= 0) throw new Exception("ID invalid.");
            if ($name === '' || mb_strlen($name) > 50) {
                throw new Exception("Numele categoriei trebuie să fie între 1 și 50 caractere.");
            }

            $stmt = $pdo->prepare("UPDATE CategoriiProduse SET Nume = :n WHERE ID_Categorie = :id");
            $stmt->execute([':n' => $name, ':id' => $id]);
            $success = "Categorie actualizată.";

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("ID invalid.");

            // Dacă există produse în categoria asta, FK-ul o să blocheze delete (normal)
            $stmt = $pdo->prepare("DELETE FROM CategoriiProduse WHERE ID_Categorie = :id");
            $stmt->execute([':id' => $id]);
            $success = "Categorie ștearsă.";
        }
    } catch (Throwable $e) {
        // Duplicate entry (UNIQUE) / FK constraint etc.
        $error = $e->getMessage();
    }
}

// Edit mode (GET ?edit=ID)
$edit = null;
if (!empty($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT ID_Categorie, Nume FROM CategoriiProduse WHERE ID_Categorie = :id");
    $stmt->execute([':id' => $id]);
    $edit = $stmt->fetch();
}

// Fetch list
$categories = $pdo->query("SELECT ID_Categorie, Nume FROM CategoriiProduse ORDER BY Nume ASC")->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Categorii</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 10px; }
    th { background: #f3f3f3; text-align: left; }
    .row { display: flex; gap: 12px; align-items: center; }
    .msg { padding: 10px; margin: 10px 0; border-radius: 6px; }
    .ok { background: #e7f7ea; border: 1px solid #b9e3c0; }
    .err { background: #fde8e8; border: 1px solid #f3b1b1; }
    a { text-decoration: none; }
    .top { display:flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    input[type=text] { padding: 8px; width: 320px; }
    button { padding: 8px 12px; cursor: pointer; }
  </style>
</head>
<body>

<div class="top">
  <div>
    <b>Admin</b> → CategoriiProduse
  </div>
  <div class="row">
    <a href="../dashboard.php">Dashboard</a>
    <a href="../logout.php">Logout</a>
  </div>
</div>

<?php if ($success): ?>
  <div class="msg ok"><?php echo h($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="msg err"><?php echo h($error); ?></div>
<?php endif; ?>

<h3><?php echo $edit ? "Editează categorie" : "Adaugă categorie"; ?></h3>

<form method="post" class="row" style="margin-bottom:18px;">
  <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
  <?php if ($edit): ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?php echo (int)$edit['ID_Categorie']; ?>">
    <input type="text" name="name" value="<?php echo h($edit['Nume']); ?>" required maxlength="50">
    <button type="submit">Salvează</button>
    <a href="categories.php">Anulează</a>
  <?php else: ?>
    <input type="hidden" name="action" value="create">
    <input type="text" name="name" placeholder="Ex: Lactate" required maxlength="50">
    <button type="submit">Adaugă</button>
  <?php endif; ?>
</form>

<h3>Lista categorii</h3>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Nume</th>
      <th>Acțiuni</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td><?php echo (int)$c['ID_Categorie']; ?></td>
        <td><?php echo h($c['Nume']); ?></td>
        <td class="row">
          <a href="categories.php?edit=<?php echo (int)$c['ID_Categorie']; ?>">Edit</a>

          <form method="post" onsubmit="return confirm('Sigur ștergi categoria?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$c['ID_Categorie']; ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p style="margin-top:16px;">
  Următorul pas: <a href="products.php">CRUD Produse</a> (după ce îl creezi).
</p>

</body>
</html>
