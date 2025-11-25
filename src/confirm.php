<?php
session_start();
require_once 'db.php';

use App\DB\Database;

// Verificar si el usuario está logueado
if (!isset($_SESSION['username'])) {
    $_SESSION['flash_message'] = "Debe iniciar sesión para realizar esta acción";
    header('Location: index.php');
    exit;
}

// Verificar que se haya enviado una acción
if (!isset($_POST['action'])) {
    $_SESSION['flash_message'] = "Acción no válida";
    header('Location: preview.php');
    exit;
}

$action = $_POST['action'];
$db = Database::getInstance()->getConnection();
$orderNumber = null;

try {
    // Recuperar el último pedido PENDING del usuario
    $stmt = $db->prepare("
        SELECT id FROM orders
        WHERE buyer_email = ? AND status = 'PENDING'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['username']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['flash_message'] = "No se encontró ningún pedido pendiente";
        header('Location: preview.php');
        exit;
    }

    $orderId = $order['id'];
    $orderNumber = $orderId;

    // Procesar la acción
    if ($action === 'confirm') {
        // Cambiar pedido a COMPLETED
        $stmt = $db->prepare("UPDATE orders SET status = 'COMPLETED' WHERE id = ?");
        $stmt->execute([$orderId]);

        $_SESSION['flash_message'] = "¡Pedido confirmado correctamente!";

    } elseif ($action === 'cancel') {
        // Marcar pedido como CANCELLED
        $stmt = $db->prepare("UPDATE orders SET status = 'CANCELLED' WHERE id = ?");
        $stmt->execute([$orderId]);

        $_SESSION['flash_message'] = "Pedido cancelado correctamente";
        $orderNumber = null;
    }

} catch (PDOException $e) {
    error_log("Error al procesar el pedido: " . $e->getMessage());
    $_SESSION['flash_message'] = "Error al procesar el pedido. Por favor, intente nuevamente.";
    $orderNumber = null;
}

// Procesar mensajes flash para mostrar en la página
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
  <title>Taquilla — Confirmación</title>
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
    <h1>Resultado de la operación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
      <a href="buy.php">Nueva compra</a>
    </nav>
  </header>

  <main>
    <?php if ($action === 'confirm' && $orderNumber): ?>
      <!-- Mostrar el número de pedido cuando esté COMPLETED -->
      <p>Tu número de pedido es: <strong id="order-number"><?php echo $orderNumber; ?></strong></p>
    <?php endif; ?>

  </main>
  </div>

</body>
</html>
