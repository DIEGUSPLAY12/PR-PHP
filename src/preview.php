<?php
session_start();
require_once 'db.php';

use App\DB\Database;

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    $_SESSION['flash_message'] = "Debe iniciar sesión para ver esta página";
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$order = null;
$orderItems = [];
$total = 0;

// Recuperar el último pedido PENDING del email en $_SESSION['username']
try {
    $stmt = $db->prepare("
        SELECT o.id, o.total, o.created_at
        FROM orders o
        WHERE o.buyer_email = ? AND o.status = 'PENDING'
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['username']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Recuperar los items del pedido
        $stmt = $db->prepare("
            SELECT oi.*, tt.label, tt.code
            FROM order_items oi
            JOIN ticket_types tt ON oi.ticket_type_id = tt.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $order['total'];
    }
} catch (PDOException $e) {
    error_log("Error al recuperar el pedido: " . $e->getMessage());
    $_SESSION['flash_message'] = "Error al cargar el pedido. Por favor, intente nuevamente.";
}

// Procesar mensajes flash
$flashMessage = '';
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Vista previa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="public/styles.css" />
</head>
<body>
  <div class="container">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if ($flashMessage): ?>
      <?php echo htmlspecialchars($flashMessage); ?>
    <?php endif; ?>
  </div>

  <header>
    <h1>Vista previa del pedido</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="buy.php">Editar compra</a>
    </nav>
  </header>

  <?php if (!$order): ?>
    <section aria-labelledby="no-order-title">
      <h2 id="no-order-title">No hay pedidos pendientes</h2>
      <p>No se encontraron pedidos pendientes para su cuenta.</p>
      <a href="buy.php">Realizar una nueva compra</a>
    </section>
  <?php else: ?>
    <!-- Contenido del carrito/pedido -->
    <section aria-labelledby="cart-title">
      <h2 id="cart-title">Resumen</h2>
      <div id="cart-preview">
        <?php foreach ($orderItems as $item): ?>
          <div class="cart-item">
            <span><?php echo htmlspecialchars($item['label']); ?> x <?php echo $item['quantity']; ?></span>
            <span><?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?> €</span>
          </div>
        <?php endforeach; ?>
        <div class="cart-total"><strong>Total: <?php echo number_format($total, 2); ?> €</strong></div>
      </div>
    </section>

    <!-- Acciones: confirmar o cancelar -->
    <form action="confirm.php" method="post" style="display:inline">
      <button id="finalize-button" type="submit" name="action" value="confirm">Confirmar compra</button>
    </form>

    <form action="confirm.php" method="post" style="display:inline">
      <button id="cancel-button" type="submit" name="action" value="cancel">Cancelar pedido</button>
    </form>
  <?php endif; ?>
  </div>

</body>
</html>
