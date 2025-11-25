[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/abb7pIlM)
# Taquilla Online — Practica PHP

Proyecto PHP básico para simular la compra de entradas de un parque temático.

---

## 🔌 1. Cómo se conecta la base de datos (Singleton)

Usamos el **patrón Singleton** para tener una única conexión a la base de datos reutilizable en toda la aplicación.

```php
// En cualquier archivo PHP
require_once 'db.php';
use App\DB\Database;

// Obtener la única instancia
$db = Database::getInstance();
$conn = $db->getConnection();

// Hacer consultas
$stmt = $conn->prepare("SELECT * FROM attractions");
$stmt->execute();
```

---

## 📦 2. Cómo se recupera el pedido pendiente

Cuando creas un pedido en `buy.php`, se guarda el ID en la sesión:

```php
// Al crear el pedido (buy.php)
$_SESSION['order_id'] = $order_id;
```

Luego en `preview.php` recuperas ese pedido:

```php
// Obtener el ID del pedido de la sesión
$order_id = $_SESSION['order_id'] ?? null;

// Consultar el pedido
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND status = 'PENDING'");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultar los items del pedido
$stmt = $conn->prepare("
    SELECT oi.*, tt.label, tt.price
    FROM order_items oi
    JOIN ticket_types tt ON oi.ticket_type_id = tt.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

---

## 🛡️ 3. Ejemplo de una consulta con prepared statement

Los **prepared statements** previenen inyección SQL. Siempre usamos `?` para los valores:

```php
// ❌ MAL - Vulnerable a SQL injection
$email = $_POST['email'];
$query = "SELECT * FROM users WHERE email = '$email'";

// ✅ BIEN - Prepared statement seguro
$email = $_POST['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

**Otro ejemplo (INSERT):**
```php
// Insertar un pedido
$stmt = $conn->prepare("INSERT INTO orders (buyer_email, total, status) VALUES (?, ?, 'PENDING')");
$stmt->execute([$email, $total]);
$order_id = $conn->lastInsertId();
```

---

## 🎥 4. Video demostración

**[Ver video](#)**

---

## 📁 Estructura del proyecto

```
src/
├── db.php          # Singleton de conexión
├── login.php       # Login con email
├── buy.php         # Selección de entradas
├── preview.php     # Vista previa del pedido
├── confirm.php     # Confirmación/cancelación
└── index.php       # Home con atracciones

db/
└── schema_and_seed.sql  # Base de datos (10 atracciones, 3 tipos de tickets)
```
https://drive.google.com/file/d/168HBSVNTrSn-EbBh0eeJ_t7gLrKr6--Q/view?usp=sharing
---

Hecho con ☕ por Mateo & Diego
