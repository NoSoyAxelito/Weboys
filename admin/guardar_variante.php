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
    $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
    $producto_id = intval($_POST['producto_id']);
    $color = trim($_POST['color']);
    $talla = trim($_POST['talla']);
    $stock = intval($_POST['stock']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones básicas
    if ($producto_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
        exit();
    }

    if (empty($color)) {
        echo json_encode(['success' => false, 'message' => 'El color es obligatorio']);
        exit();
    }

    if (empty($talla)) {
        echo json_encode(['success' => false, 'message' => 'La talla es obligatoria']);
        exit();
    }

    if ($stock < 0) {
        echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
        exit();
    }

    try {
        // Verificar que el producto existe
        $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit();
        }
        $stmt->close();

        if ($variant_id > 0) {
            // Verificar que no existe otra variante con la misma combinación color-talla
            $stmt = $conn->prepare("SELECT id FROM producto_variantes WHERE producto_id = ? AND color = ? AND talla = ? AND id != ?");
            $stmt->bind_param("issi", $producto_id, $color, $talla, $variant_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una variante con este color y talla']);
                exit();
            }
            $stmt->close();

            // Actualizar variante existente
            $stmt = $conn->prepare("UPDATE producto_variantes SET color = ?, talla = ?, stock = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $color, $talla, $stock, $activo, $variant_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Variante actualizada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la variante']);
            }
        } else {
            // Verificar que no existe una variante con la misma combinación color-talla
            $stmt = $conn->prepare("SELECT id FROM producto_variantes WHERE producto_id = ? AND color = ? AND talla = ?");
            $stmt->bind_param("iss", $producto_id, $color, $talla);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una variante con este color y talla']);
                exit();
            }
            $stmt->close();

            // Crear nueva variante
            $stmt = $conn->prepare("INSERT INTO producto_variantes (producto_id, color, talla, stock, activo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $producto_id, $color, $talla, $stock, $activo);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Variante creada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la variante']);
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