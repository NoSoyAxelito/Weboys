<?php
session_start();
if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

include '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    try {
        // Verificar si el producto existe
        $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit();
        }
        
        $stmt->close();
        
        // Eliminar el producto
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el producto']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o ID faltante']);
}

$conn->close();
?>