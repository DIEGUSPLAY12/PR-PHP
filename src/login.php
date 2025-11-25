<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $error = 'Por favor, introduce tu email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        $_SESSION['username'] = $email;
        header('Location: buy.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="public/styles.css" />
</head>
<body>
  <div class="container">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite">
    <?php if ($error) : ?>
        <?php echo htmlspecialchars($error); ?>
    <?php endif; ?>
  </div>

  <header>
    <h1>Identificación</h1>
    <nav>
      <a href="index.php">Volver a Home</a>
    </nav>
  </header>

  <!-- Formulario de login con email -->
  <form id="login-form" method="post" novalidate>
    <div>
      <label for="email-input">Email:</label>
      <input
        id="email-input"
        name="email"
        type="email"
        required
        placeholder="nombre@dominio.com"
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
      />
    </div>
    <button type="submit">Continuar a compra</button>
  </form>
  </div>

</body>
</html>
