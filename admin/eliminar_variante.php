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
        // Verificar si la variante existe
        $stmt = $conn->prepare("SELECT id FROM producto_variantes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Variante no encontrada']);
            exit();
        }
        
        $stmt->close();
        
        // Eliminar la variante
        $stmt = $conn->prepare("DELETE FROM producto_variantes WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Variante eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la variante']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la variante']);
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