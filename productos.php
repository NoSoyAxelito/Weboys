<?php include 'includes/conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ... (meta, título, CSS) ... -->
</head>
<body>
    <header><!-- ... --></header>

    <main class="productos-grid">
        <?php
        $sql = "SELECT id, nombre, precio, imagen FROM productos WHERE activo = 1";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()):
        ?>
            <div class="producto-card" onclick="window.location='producto.php?id=<?= $row['id'] ?>'">
                <img src="<?= $row['imagen'] ?>" alt="<?= $row['nombre'] ?>">
                <h3><?= $row['nombre'] ?></h3>
                <p>$<?= number_format($row['precio'], 2) ?></p>
            </div>
        <?php endwhile; ?>
    </main>

    <script>
        // Redirige al hacer clic en cualquier parte de la card
        document.querySelectorAll('.producto-card').forEach(card => {
            card.style.cursor = 'pointer';
        });
    </script>
</body>
</html>