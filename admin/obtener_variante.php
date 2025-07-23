<?php
session_start();
if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

include '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM producto_variantes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $variante = $result->fetch_assoc();
            echo json_encode([
                'success' => true, 
                'variante' => $variante
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Variante no encontrada']);
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