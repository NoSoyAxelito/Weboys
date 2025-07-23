<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);


    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    
    if (!$stmt->execute()) {
        error_log("Error en consulta SQL: " . $stmt->error);
        header("Location: login.php?error=1");
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    
        
        if (password_verify($password, $user["password"])) {
            $_SESSION["admin"] = $user["id"];
            header("Location: panel.php");
            exit();
        } else {

        }
    }
    
    header("Location: login.php?error=1");
    exit();
}
?>
