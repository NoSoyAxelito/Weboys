<?php
session_start();
include '../includes/conexion.php';

if (!isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de item no proporcionado']);
    exit;
}

$item_id = $_POST['item_id'];

if (isset($_SESSION['carrito'][$item_id])) {
    unset($_SESSION['carrito'][$item_id]);
    echo json_encode([
        'success' => true,
        'cart_count' => array_sum(array_column($_SESSION['carrito'], 'cantidad'))
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item no encontrado en el carrito']);
}
?>