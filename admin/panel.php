<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: ../index.html");
    exit();
}

include '../includes/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #a7ffc4; /* Verde menta claro */
            --secondary-color: #4dbd90; /* Verde menta medio */
            --dark-color: #1e3f33; /* Verde oscuro */
            --light-color: #e8f5e9; /* Verde muy claro */
            --accent-color: #ff9e80; /* Coral para acentos */
            --text-color: #263238; /* Gris oscuro para texto */
            --text-light: #ffffff; /* Blanco para texto claro */
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: var(--text-light);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        header div {
            display: flex;
            gap: 1rem;
        }

        .back-btn, .logout-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn {
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        .back-btn:hover {
            background-color: #d0e8d0;
        }

        .logout-btn {
            background-color: var(--dark-color);
            color: var(--text-light);
        }

        .logout-btn:hover {
            background-color: #2d5a4a;
        }

        main {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        button {
            background-color: var(--secondary-color);
            color: var(--text-light);
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        button:hover {
            background-color: #3da57a;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        #addProductBtn {
            margin-bottom: 2rem;
            background-color: var(--accent-color);
        }

        #addProductBtn:hover {
            background-color: #ff8a65;
        }

        form {
            background-color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(77, 189, 144, 0.2);
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row > div {
            flex: 1;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        #adminProductList {
            list-style: none;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        #adminProductList li {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        #adminProductList li:last-child {
            border-bottom: none;
        }

        #adminProductList li:hover {
            background-color: var(--light-color);
        }

        #adminProductList li > div {
            display: flex;
            gap: 0.8rem;
        }

        .active {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .inactive {
            color: #e53935;
            font-weight: 500;
        }

        .edit-btn {
            background-color: #42a5f5;
        }

        .edit-btn:hover {
            background-color: #1e88e5;
        }

        .variants-btn {
            background-color: #7e57c2;
        }

        .variants-btn:hover {
            background-color: #673ab7;
        }

        .delete-btn {
            background-color: #ef5350;
        }

        .delete-btn:hover {
            background-color: #e53935;
        }

        #variantsList {
            background-color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }

        #variantsContainer {
            list-style: none;
            margin: 1.5rem 0;
        }

        #variantsContainer li {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #variantsContainer li:last-child {
            border-bottom: none;
        }

        #variantsContainer li > div {
            display: flex;
            gap: 0.8rem;
        }

        .edit-variant-btn {
            background-color: #42a5f5;
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        .delete-variant-btn {
            background-color: #ef5350;
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
                text-align: center;
            }

            header div {
                width: 100%;
                justify-content: center;
            }

            #adminProductList li {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            #adminProductList li > div {
                width: 100%;
                justify-content: flex-end;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1rem;
            }

            header div {
                flex-direction: column;
                align-items: stretch;
            }

            .back-btn, .logout-btn {
                justify-content: center;
            }

            #adminProductList li > div {
                flex-direction: column;
                gap: 0.5rem;
            }

            #adminProductList li > div button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-cog"></i> Panel de Administración</h1>
        <div>
            <a href="../productos.php" class="back-btn"><i class="fas fa-store"></i> Ver tienda</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </header>

    <main>
        <button id="addProductBtn"><i class="fas fa-plus"></i> Añadir Producto</button>
        
        <div id="productFormContainer" style="display: none;">
            <form id="productForm" action="guardar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="productId">
                <div class="form-row">
                    <div>
                        <label for="productName">Nombre</label>
                        <input type="text" name="nombre" id="productName" placeholder="Nombre del producto" required>
                    </div>
                    <div>
                        <label for="productPrice">Precio</label>
                        <input type="number" name="precio" id="productPrice" placeholder="Precio" required step="0.01">
                    </div>
                </div>
                
                <label for="productDescription">Descripción</label>
                <textarea name="descripcion" id="productDescription" placeholder="Descripción del producto" rows="4"></textarea>
                
                <label for="productImage">URL de la imagen</label>
                <input type="text" name="imagen" id="productImage" placeholder="URL de la imagen del producto">
                
                <div style="margin: 1rem 0;">
                    <label>
                        <input type="checkbox" name="activo" id="productActive" value="1" checked>
                        Producto activo
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="cancelBtn"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit"><i class="fas fa-save"></i> Guardar Producto</button>
                </div>
            </form>
        </div>

        <!-- Sección para gestionar variantes -->
        <div id="variantFormContainer" style="display: none;">
            <h3 id="variantTitle">Gestionar Variantes</h3>
            <form id="variantForm">
                <input type="hidden" name="producto_id" id="variantProductId">
                <input type="hidden" name="variant_id" id="variantId">
                
                <div class="form-row">
                    <div>
                        <label for="variantColor">Color</label>
                        <input type="text" name="color" id="variantColor" placeholder="Color" required>
                    </div>
                    <div>
                        <label for="variantTalla">Talla</label>
                        <input type="text" name="talla" id="variantTalla" placeholder="Talla" required>
                    </div>
                    <div>
                        <label for="variantStock">Stock</label>
                        <input type="number" name="stock" id="variantStock" placeholder="Stock" required min="0">
                    </div>
                </div>
                
                <div style="margin: 1rem 0;">
                    <label>
                        <input type="checkbox" name="activo" id="variantActive" value="1" checked>
                        Variante activa
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="cancelVariantBtn"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit"><i class="fas fa-save"></i> Guardar Variante</button>
                </div>
            </form>
        </div>

        <h2><i class="fas fa-box-open"></i> Productos Existentes</h2>
        <ul id="adminProductList">
            <?php
            $sql = "SELECT p.*, 
                           COUNT(pv.id) as total_variantes,
                           SUM(CASE WHEN pv.activo = 1 THEN pv.stock ELSE 0 END) as stock_total
                    FROM productos p 
                    LEFT JOIN producto_variantes pv ON p.id = pv.producto_id 
                    GROUP BY p.id 
                    ORDER BY p.id DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $estado = $row["activo"] ? "Activo" : "Inactivo";
                    $estadoClass = $row["activo"] ? "active" : "inactive";
                    $stockTotal = $row["stock_total"] ?? 0;
                    $totalVariantes = $row["total_variantes"];
                    
                    echo '
                    <li>
                        <span><strong>' . htmlspecialchars($row["nombre"]) . '</strong> - $' . number_format($row["precio"], 2) . 
                        ' - <i class="fas fa-cubes"></i> Stock: ' . $stockTotal . ' - <i class="fas fa-list"></i> Variantes: ' . $totalVariantes . 
                        ' - <span class="' . $estadoClass . '"><i class="fas fa-circle"></i> ' . $estado . '</span></span>
                        <div>
                            <button class="edit-btn" data-id="' . $row["id"] . '"><i class="fas fa-edit"></i> Editar</button>
                            <button class="variants-btn" data-id="' . $row["id"] . '" data-name="' . htmlspecialchars($row["nombre"]) . '"><i class="fas fa-layer-group"></i> Variantes</button>
                            <button class="delete-btn" data-id="' . $row["id"] . '"><i class="fas fa-trash"></i> Eliminar</button>
                        </div>
                    </li>
                    ';
                }
            } else {
                echo '<li style="padding: 2rem; text-align: center;">No hay productos registrados.</li>';
            }
            ?>
        </ul>

        <!-- Lista de variantes (se muestra dinámicamente) -->
        <div id="variantsList" style="display: none;">
            <h3 id="variantListTitle"><i class="fas fa-layer-group"></i> Variantes del Producto</h3>
            <button id="addVariantBtn"><i class="fas fa-plus"></i> Añadir Variante</button>
            <ul id="variantsContainer"></ul>
            <div style="text-align: right;">
                <button id="closeVariantsBtn"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos
        const addProductBtn = document.getElementById('addProductBtn');
        const productFormContainer = document.getElementById('productFormContainer');
        const productForm = document.getElementById('productForm');
        const cancelBtn = document.getElementById('cancelBtn');
        
        const variantFormContainer = document.getElementById('variantFormContainer');
        const variantForm = document.getElementById('variantForm');
        const cancelVariantBtn = document.getElementById('cancelVariantBtn');
        const addVariantBtn = document.getElementById('addVariantBtn');
        const variantsList = document.getElementById('variantsList');
        const closeVariantsBtn = document.getElementById('closeVariantsBtn');

        // Mostrar/ocultar formulario de producto
        addProductBtn.addEventListener('click', function() {
            if (productFormContainer.style.display === 'block') {
                hideProductForm();
                addProductBtn.innerHTML = '<i class="fas fa-plus"></i> Añadir Producto';
            } else {
                resetProductForm();
                productFormContainer.style.display = 'block';
                addProductBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
            }
        });

        cancelBtn.addEventListener('click', function() {
            hideProductForm();
            addProductBtn.innerHTML = '<i class="fas fa-plus"></i> Añadir Producto';
        });

        function resetProductForm() {
            productForm.reset();
            document.getElementById('productId').value = '';
            document.getElementById('productActive').checked = true;
        }

        function hideProductForm() {
            productFormContainer.style.display = 'none';
            resetProductForm();
        }

        // Gestión de variantes
        cancelVariantBtn.addEventListener('click', function() {
            hideVariantForm();
        });

        addVariantBtn.addEventListener('click', function() {
            resetVariantForm();
            variantFormContainer.style.display = 'block';
        });

        closeVariantsBtn.addEventListener('click', function() {
            variantsList.style.display = 'none';
        });

        function resetVariantForm() {
            variantForm.reset();
            document.getElementById('variantId').value = '';
            document.getElementById('variantActive').checked = true;
        }

        function hideVariantForm() {
            variantFormContainer.style.display = 'none';
            resetVariantForm();
        }

        // Manejar edición de productos
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('obtener_producto.php?id=' + productId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('productId').value = data.producto.id;
                            document.getElementById('productName').value = data.producto.nombre;
                            document.getElementById('productPrice').value = data.producto.precio;
                            document.getElementById('productDescription').value = data.producto.descripcion || '';
                            document.getElementById('productImage').value = data.producto.imagen || '';
                            document.getElementById('productActive').checked = data.producto.activo == 1;
                            
                            productFormContainer.style.display = 'block';
                            addProductBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
                        } else {
                            alert('Error al cargar el producto');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar el producto');
                    });
            });
        });

        // Manejar gestión de variantes
        document.querySelectorAll('.variants-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const productName = this.getAttribute('data-name');
                
                document.getElementById('variantProductId').value = productId;
                document.getElementById('variantListTitle').innerHTML = '<i class="fas fa-layer-group"></i> Variantes de: ' + productName;
                
                cargarVariantes(productId);
                variantsList.style.display = 'block';
            });
        });

        function cargarVariantes(productId) {
            fetch('obtener_variantes.php?producto_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('variantsContainer');
                    container.innerHTML = '';
                    
                    if (data.success && data.variantes.length > 0) {
                        data.variantes.forEach(variante => {
                            const estado = variante.activo == 1 ? 'Activa' : 'Inactiva';
                            const estadoClass = variante.activo == 1 ? 'active' : 'inactive';
                            
                            const li = document.createElement('li');
                            li.innerHTML = `
                                <span><strong>${variante.color}</strong> - Talla: ${variante.talla} - <i class="fas fa-cubes"></i> Stock: ${variante.stock} - <span class="${estadoClass}"><i class="fas fa-circle"></i> ${estado}</span></span>
                                <div>
                                    <button class="edit-variant-btn" data-id="${variante.id}"><i class="fas fa-edit"></i> Editar</button>
                                    <button class="delete-variant-btn" data-id="${variante.id}"><i class="fas fa-trash"></i> Eliminar</button>
                                </div>
                            `;
                            container.appendChild(li);
                        });
                        
                        // Agregar event listeners a los nuevos botones
                        agregarEventListenersVariantes();
                    } else {
                        container.innerHTML = '<li style="padding: 1rem; text-align: center;">No hay variantes registradas</li>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar las variantes');
                });
        }

        function agregarEventListenersVariantes() {
            // Editar variante
            document.querySelectorAll('.edit-variant-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const variantId = this.getAttribute('data-id');
                    
                    fetch('obtener_variante.php?id=' + variantId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('variantId').value = data.variante.id;
                                document.getElementById('variantColor').value = data.variante.color;
                                document.getElementById('variantTalla').value = data.variante.talla;
                                document.getElementById('variantStock').value = data.variante.stock;
                                document.getElementById('variantActive').checked = data.variante.activo == 1;
                                
                                variantFormContainer.style.display = 'block';
                            } else {
                                alert('Error al cargar la variante');
                            }
                        });
                });
            });

            // Eliminar variante
            document.querySelectorAll('.delete-variant-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if(confirm('¿Estás seguro de que quieres eliminar esta variante?')) {
                        const variantId = this.getAttribute('data-id');
                        
                        fetch('eliminar_variante.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + variantId
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Variante eliminada correctamente');
                                const productId = document.getElementById('variantProductId').value;
                                cargarVariantes(productId);
                            } else {
                                alert('Error al eliminar la variante: ' + data.message);
                            }
                        });
                    }
                });
            });
        }

        // Manejar eliminación de productos
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('¿Estás seguro de que quieres eliminar este producto y todas sus variantes?')) {
                    const productId = this.getAttribute('data-id');
                    
                    fetch('eliminar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + productId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Producto eliminado correctamente');
                            location.reload();
                        } else {
                            alert('Error al eliminar el producto: ' + data.message);
                        }
                    });
                }
            });
        });

        // Manejar envío del formulario de producto
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('guardar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Manejar envío del formulario de variante
        variantForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('guardar_variante.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideVariantForm();
                    const productId = document.getElementById('variantProductId').value;
                    cargarVariantes(productId);
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });
    });
    </script>
</body>
</html>