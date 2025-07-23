<?php
session_start();
include '../includes/conexion.php';

if (!isset($_POST['item_id'], $_POST['change'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$item_id = $_POST['item_id'];
$change = intval($_POST['change']);

if (!isset($_SESSION['carrito'][$item_id])) {
    echo json_encode(['success' => false, 'message' => 'Item no encontrado en el carrito']);
    exit;
}

// Obtener datos del producto para validar stock
$producto_id = $_SESSION['carrito'][$item_id]['producto_id'];
$color = $_SESSION['carrito'][$item_id]['color'];
$talla = $_SESSION['carrito'][$item_id]['talla'];

// Verificar stock disponible
$stock_query = "SELECT stock FROM producto_variantes 
               WHERE producto_id = ? AND color = ? AND talla = ? AND activo = 1";
$stmt = $conn->prepare($stock_query);
$stmt->bind_param('iss', $producto_id, $color, $talla);
$stmt->execute();
$stock_result = $stmt->get_result();

if ($stock_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Variante no disponible']);
    exit;
}

$stock = $stock_result->fetch_assoc()['stock'];
$nueva_cantidad = $_SESSION['carrito'][$item_id]['cantidad'] + $change;

// Validar nueva cantidad
if ($nueva_cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'La cantidad no puede ser menor a 1']);
    exit;
}

if ($nueva_cantidad > $stock) {
    echo json_encode([
        'success' => false, 
        'message' => 'No hay suficiente stock. Máximo disponible: ' . $stock
    ]);
    exit;
}

// Actualizar cantidad
$_SESSION['carrito'][$item_id]['cantidad'] = $nueva_cantidad;

echo json_encode([
    'success' => true,
    'new_quantity' => $nueva_cantidad,
    'cart_count' => array_sum(array_column($_SESSION['carrito'], 'cantidad'))
]);
?>