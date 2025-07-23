<?php
session_start();
if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

include '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $imagen = isset($_POST['imagen']) ? trim($_POST['imagen']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones básicas
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        exit();
    }

    if ($precio <= 0) {
        echo json_encode(['success' => false, 'message' => 'El precio debe ser mayor a 0']);
        exit();
    }

    try {
        if ($id > 0) {
            // Actualizar producto existente
            $stmt = $conn->prepare("UPDATE productos SET nombre = ?, precio = ?, descripcion = ?, imagen = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("sdssii", $nombre, $precio, $descripcion, $imagen, $activo, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
            }
        } else {
            // Crear nuevo producto
            $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, descripcion, imagen, activo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssi", $nombre, $precio, $descripcion, $imagen, $activo);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto creado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el producto']);
            }
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>