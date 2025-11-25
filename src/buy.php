<?php
session_start();
require_once 'db.php';

use App\DB\Database;

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

$error = '';
$ticket_types = [];

try {
    $stmt = $conn->prepare("SELECT id, code, label, price, description FROM ticket_types ORDER BY id");
    $stmt->execute();
    $ticket_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error al cargar los tipos de entrada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['quantity'] ?? [];
    $total_items = 0;
    $items = [];

    foreach ($quantities as $ticket_id => $quantity) {
        $quantity = (int)$quantity;

        if ($quantity < 0 || $quantity > 100) {
            $error = 'Las cantidades deben estar entre 0 y 100.';
            break;
        }

        if ($quantity > 0) {
            $total_items += $quantity;
            $items[$ticket_id] = $quantity;
        }
    }

    if (empty($error) && $total_items === 0) {
        $error = 'Debes seleccionar al menos una entrada.';
    }

    if (empty($error)) {
        try {
            $conn->beginTransaction();

            $total = 0;
            $order_items_data = [];

            foreach ($items as $ticket_id => $quantity) {
                $stmt = $conn->prepare("SELECT id, price FROM ticket_types WHERE id = ?");
                $stmt->execute([$ticket_id]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$ticket) {
                    throw new Exception('Tipo de entrada no válido.');
                }

                $subtotal = $ticket['price'] * $quantity;
                $total += $subtotal;

                $order_items_data[] = [
                    'ticket_type_id' => $ticket_id,
                    'quantity' => $quantity,
                    'unit_price' => $ticket['price']
                ];
            }

            $stmt = $conn->prepare("INSERT INTO orders (buyer_email, total, status) VALUES (?, ?, 'PENDING')");
            $stmt->execute([$_SESSION['username'], $total]);
            $order_id = $conn->lastInsertId();

            foreach ($order_items_data as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['ticket_type_id'], $item['quantity'], $item['unit_price']]);
            }

            $conn->commit();

            $_SESSION['order_id'] = $order_id;
            header('Location: preview.php');
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Error al crear el pedido: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Compra</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="public/styles.css" />
</head>
<body>
  <div class="container">

  <div id="flash-message" aria-live="polite">
    <?php if ($error) : ?>
        <?php echo htmlspecialchars($error); ?>
    <?php endif; ?>
  </div>

  <header>
    <h1>Compra de entradas</h1>
    <p>Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <nav>
      <a href="index.php">Home</a>
      <a href="login.php">Cambiar de usuario</a>
    </nav>
  </header>

  <form id="buy-form" method="post" novalidate>
    <p>Selecciona cantidades (0–100). El precio se muestra junto al tipo:</p>

    <fieldset>
      <legend>Tipos de entrada</legend>

      <?php foreach ($ticket_types as $ticket) : ?>
        <div class="ticket-row">
          <label for="quantity-<?php echo $ticket['id']; ?>" id="ticket-type-<?php echo $ticket['id']; ?>">
            <?php echo htmlspecialchars($ticket['label']); ?> —
            <span class="ticket-price"><?php echo number_format($ticket['price'], 2); ?> €</span>
          </label>
          <input
            id="quantity-<?php echo $ticket['id']; ?>"
            name="quantity[<?php echo $ticket['id']; ?>]"
            type="number"
            min="0"
            max="100"
            step="1"
            value="<?php echo isset($_POST['quantity'][$ticket['id']]) ? (int)$_POST['quantity'][$ticket['id']] : 0; ?>"
            inputmode="numeric"
          />
        </div>
      <?php endforeach; ?>
    </fieldset>

    <button type="submit">Ir a vista previa</button>
  </form>
  </div>

</body>
</html>
