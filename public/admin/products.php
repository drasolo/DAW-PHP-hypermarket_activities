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

// Load categories for dropdown
$categories = $pdo->query("SELECT ID_Categorie, Nume FROM CategoriiProduse ORDER BY Nume ASC")->fetchAll();

// Helper: validate category exists
function category_exists(array $categories, int $id): bool {
    foreach ($categories as $c) if ((int)$c['ID_Categorie'] === $id) return true;
    return false;
}

// Handle POST actions
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
            $price = $_POST['price'] ?? '';
            $catId = (int)($_POST['category_id'] ?? 0);

            if ($name === '' || mb_strlen($name) > 50) throw new Exception("Nume produs invalid (1–50).");
            if (!is_numeric($price) || (float)$price < 0) throw new Exception("Preț invalid.");
            if ($catId <= 0 || !category_exists($categories, $catId)) throw new Exception("Categorie invalidă.");

            $stmt = $pdo->prepare("INSERT INTO Produs (Nume, Pret, ID_Categorie) VALUES (:n, :p, :c)");
            $stmt->execute([':n' => $name, ':p' => (float)$price, ':c' => $catId]);

            $success = "Produs adăugat.";

        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $price = $_POST['price'] ?? '';
            $catId = (int)($_POST['category_id'] ?? 0);

            if ($id <= 0) throw new Exception("ID invalid.");
            if ($name === '' || mb_strlen($name) > 50) throw new Exception("Nume produs invalid (1–50).");
            if (!is_numeric($price) || (float)$price < 0) throw new Exception("Preț invalid.");
            if ($catId <= 0 || !category_exists($categories, $catId)) throw new Exception("Categorie invalidă.");

            $stmt = $pdo->prepare("UPDATE Produs SET Nume=:n, Pret=:p, ID_Categorie=:c WHERE ID_Produs=:id");
            $stmt->execute([':n' => $name, ':p' => (float)$price, ':c' => $catId, ':id' => $id]);

            $success = "Produs actualizat.";

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception("ID invalid.");

            $stmt = $pdo->prepare("DELETE FROM Produs WHERE ID_Produs = :id");
            $stmt->execute([':id' => $id]);

            $success = "Produs șters.";
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// Edit mode
$edit = null;
if (!empty($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT ID_Produs, Nume, Pret, ID_Categorie FROM Produs WHERE ID_Produs = :id");
    $stmt->execute([':id' => $id]);
    $edit = $stmt->fetch();
}

// Fetch products
$products = $pdo->query("
    SELECT p.ID_Produs, p.Nume, p.Pret, c.Nume AS Categorie, p.ID_Categorie
    FROM Produs p
    JOIN CategoriiProduse c ON c.ID_Categorie = p.ID_Categorie
    ORDER BY p.ID_Produs DESC
")->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Produse</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 10px; }
    th { background: #f3f3f3; text-align: left; }
    .row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .msg { padding: 10px; margin: 10px 0; border-radius: 6px; }
    .ok { background: #e7f7ea; border: 1px solid #b9e3c0; }
    .err { background: #fde8e8; border: 1px solid #f3b1b1; }
    a { text-decoration: none; }
    .top { display:flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    input[type=text], input[type=number], select { padding: 8px; }
    button { padding: 8px 12px; cursor: pointer; }
  </style>
</head>
<body>

<div class="top">
  <div><b>Admin</b> → Produse</div>
  <div class="row">
    <a href="categories.php">Categorii</a>
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

<?php if (count($categories) === 0): ?>
  <div class="msg err">
    Nu ai categorii. Creează mai întâi categorii în <a href="categories.php">CategoriiProduse</a>.
  </div>
<?php endif; ?>

<h3><?php echo $edit ? "Editează produs" : "Adaugă produs"; ?></h3>

<form method="post" class="row" style="margin-bottom:18px;">
  <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">

  <?php if ($edit): ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?php echo (int)$edit['ID_Produs']; ?>">
  <?php else: ?>
    <input type="hidden" name="action" value="create">
  <?php endif; ?>

  <input type="text" name="name" placeholder="Nume produs" required maxlength="50"
         value="<?php echo $edit ? h($edit['Nume']) : ''; ?>">

  <input type="number" name="price" placeholder="Preț" required step="0.01" min="0"
         value="<?php echo $edit ? h((string)$edit['Pret']) : ''; ?>">

  <select name="category_id" required>
    <option value="">Alege categorie</option>
    <?php foreach ($categories as $c): ?>
      <?php
        $cid = (int)$c['ID_Categorie'];
        $sel = $edit && (int)$edit['ID_Categorie'] === $cid ? 'selected' : '';
      ?>
      <option value="<?php echo $cid; ?>" <?php echo $sel; ?>>
        <?php echo h($c['Nume']); ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit"><?php echo $edit ? "Salvează" : "Adaugă"; ?></button>
  <?php if ($edit): ?>
    <a href="products.php">Anulează</a>
  <?php endif; ?>
</form>

<h3>Lista produse</h3>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Nume</th>
      <th>Preț</th>
      <th>Categorie</th>
      <th>Acțiuni</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?php echo (int)$p['ID_Produs']; ?></td>
        <td><?php echo h($p['Nume']); ?></td>
        <td><?php echo h((string)$p['Pret']); ?></td>
        <td><?php echo h($p['Categorie']); ?></td>
        <td class="row">
          <a href="products.php?edit=<?php echo (int)$p['ID_Produs']; ?>">Edit</a>

          <form method="post" onsubmit="return confirm('Sigur ștergi produsul?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$p['ID_Produs']; ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
