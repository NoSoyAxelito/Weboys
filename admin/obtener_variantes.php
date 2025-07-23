<?php
session_start();
if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

include '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['producto_id'])) {
    $producto_id = intval($_GET['producto_id']);
    
    if ($producto_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM producto_variantes WHERE producto_id = ? ORDER BY color, talla");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $variantes = [];
        while ($row = $result->fetch_assoc()) {
            $variantes[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'variantes' => $variantes
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o producto_id faltante']);
}

$conn->close();
?>