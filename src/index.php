<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="public/styles.css" />
</head>
<body>
  <div class="container">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite"></div>

  <header>
    <h1>Parque Temático</h1>
    <!-- Enlace a login -->
    <nav>
      <a href="login.php">Iniciar compra</a>
    </nav>
  </header>

  <!-- Imagen temática (el alumno la coloca directamente en el HTML) -->
  <figure>
    <img id="theme-image" src="./img/tematica.jpg" alt="Imagen temática del parque"  style="width: 100%; height: 450px;" />
  </figure>

  <?php
  require_once 'db.php';

  use App\DB\Database;

  // Obtener conexión a la base de datos
  $db = Database::getInstance()->getConnection();

  // Determinar el filtro actual
  $filtro = $_GET['filter'] ?? 'all';

  // Construir la consulta según el filtro
  $sql = "SELECT * FROM attractions";
  $params = [];

  if ($filtro === 'maintenance') {
      $sql .= " WHERE maintenance = 1";
  } elseif ($filtro === 'available') {
      $sql .= " WHERE maintenance = 0";
  }

  $sql .= " ORDER BY name";

  $stmt = $db->prepare($sql);
  $stmt->execute($params);
  $atracciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $contador = count($atracciones);
    ?>

  <!-- Filtro tipo desplegable (select) -->
  <section aria-labelledby="filtro-title">
    <h2 id="filtro-title">Filtrar atracciones</h2>
    <form method="GET" action="">
      <label for="filter-maintenance">Estado:</label>
      <select id="filter-maintenance" name="filter" onchange="this.form.submit()">
        <option value="all"<?php if ($filtro === 'all') echo 'selected'; ?>>Todas</option>
        <option value="maintenance" <?php if ($filtro === 'maintenance') echo 'selected'; ?>>En mantenimiento</option>
        <option value="available" <?php if ($filtro === 'available') echo 'selected'; ?>>Disponibles</option>
      </select>
    </form>
    <span>Mostrando: <strong id="attraction-count"><?php echo $contador; ?></strong></span>
  </section>

  <!-- Lista de atracciones -->
  <section aria-labelledby="lista-title">
    <h2 id="lista-title">Atracciones</h2>
    <div id="attraction-list">
      <?php if ($contador > 0) : ?>
            <?php foreach ($atracciones as $atraccion) : ?>
          <article class="attraction">
            <h3><?php echo htmlspecialchars($atraccion['name']); ?></h3>
            <p><?php echo htmlspecialchars($atraccion['description']); ?></p>
            <span class="badge">
                <?php if ($atraccion['maintenance'] == 1) : ?>
                En mantenimiento
                <?php else : ?>
                Disponible
                <?php endif; ?>
            </span>
                <?php if ($atraccion['duration_minutes']) : ?>
              <p>Duración: <?php echo $atraccion['duration_minutes']; ?> minutos</p>
                <?php endif; ?>
                <?php if ($atraccion['min_height_cm']) : ?>
              <p>Altura mínima: <?php echo $atraccion['min_height_cm']; ?> cm</p>
                <?php endif; ?>
                <?php if ($atraccion['category']) : ?>
              <p>Categoría: <?php echo htmlspecialchars($atraccion['category']); ?></p>
                <?php endif; ?>
          </article>
            <?php endforeach;  ?>
      <?php else : ?>
        <p>No se encontraron atracciones con los filtros seleccionados.</p>
      <?php endif; ?>
    </div>
  </section>
  </div>

</body>
</html>
