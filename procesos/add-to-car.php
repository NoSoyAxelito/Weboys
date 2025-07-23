<?php
session_start();
include '../includes/conexion.php';

// Verificar que todos los campos requeridos estén presentes
if (!isset($_POST['producto_id'], $_POST['cantidad'], $_POST['talla'], $_POST['color'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

$producto_id = intval($_POST['producto_id']);
$cantidad = intval($_POST['cantidad']);
$talla = $conn->real_escape_string($_POST['talla']);
$color = $conn->real_escape_string($_POST['color']);

// Validar cantidad mínima
if ($cantidad < 1) {
    echo json_encode(['success' => false, 'message' => 'La cantidad debe ser al menos 1']);
    exit;
}

// Verificar stock específico para esta variante (color + talla)
$stock_query = "SELECT stock FROM producto_variantes 
               WHERE producto_id = $producto_id 
               AND color = '$color' 
               AND talla = '$talla' 
               AND activo = 1";
$stock_result = $conn->query($stock_query);

if (!$stock_result || $stock_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Variante no disponible']);
    exit;
}

$stock = $stock_result->fetch_assoc()['stock'];

if ($cantidad > $stock) {
    echo json_encode([
        'success' => false, 
        'message' => 'No hay suficiente stock', 
        'stock_disponible' => $stock
    ]);
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Identificador único para variantes (color + talla)
$item_id = "{$producto_id}-{$color}-{$talla}";

// Actualizar o agregar item al carrito
if (isset($_SESSION['carrito'][$item_id])) {
    $nueva_cantidad = $_SESSION['carrito'][$item_id]['cantidad'] + $cantidad;
    
    // Verificar nuevamente el stock con la cantidad acumulada
    if ($nueva_cantidad > $stock) {
        echo json_encode([
            'success' => false, 
            'message' => 'No hay suficiente stock para la cantidad solicitada',
            'stock_disponible' => $stock,
            'en_carrito' => $_SESSION['carrito'][$item_id]['cantidad']
        ]);
        exit;
    }
    
    $_SESSION['carrito'][$item_id]['cantidad'] = $nueva_cantidad;
} else {
    $_SESSION['carrito'][$item_id] = [
        'producto_id' => $producto_id,
        'cantidad' => $cantidad,
        'talla' => $talla,
        'color' => $color
    ];
}

// Obtener información del producto para el mensaje
$producto = $conn->query("SELECT nombre, precio FROM productos WHERE id = $producto_id")->fetch_assoc();

echo json_encode([
    'success' => true,
    'cart_count' => array_sum(array_column($_SESSION['carrito'], 'cantidad')), // Cantidad total de items
    'item_count' => count($_SESSION['carrito']), // Items únicos en el carrito
    'message' => "{$cantidad} x {$producto['nombre']} ({$color}, {$talla}) añadido al carrito",
    'new_stock' => ($stock - $cantidad) // Nuevo stock disponible
]);
?>
