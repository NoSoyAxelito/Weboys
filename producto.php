<?php 
include 'includes/conexion.php';

// Verificar si el ID del producto existe y es válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$producto_id = intval($_GET['id']);
$producto = $conn->query("SELECT * FROM productos WHERE id = $producto_id AND activo = 1")->fetch_assoc();

// Si no se encuentra el producto, redirigir
if (!$producto) {
    header("Location: index.php");
    exit();
}

// Obtener todas las variantes activas del producto
$variantes_query = "SELECT * FROM producto_variantes WHERE producto_id = $producto_id AND activo = 1 ORDER BY color, talla";
$variantes_result = $conn->query($variantes_query);
$variantes = [];

while ($variante = $variantes_result->fetch_assoc()) {
    $variantes[] = $variante;
}

// Extraer colores únicos
$colores_unicos = array_unique(array_column($variantes, 'color'));

// Extraer tallas únicas
$tallas_unicas = array_unique(array_column($variantes, 'talla'));

// Crear array asociativo para acceso rápido al stock
$stock_por_variante = [];
foreach ($variantes as $variante) {
    $key = $variante['color'] . '_' . $variante['talla'];
    $stock_por_variante[$key] = $variante['stock'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> | Tienda</title>
    <style>
        :root {
            --color-primario: #a7ffeb;
            --color-primario-oscuro: #64ffda;
            --color-secundario: #e0f7fa;
            --color-texto: #263238;
            --color-fondo: #f5f5f5;
            --color-borde: #b2dfdb;
            --color-boton: #4db6ac;
            --color-boton-hover: #26a69a;
            --color-exito: #388e3c;
            --color-error: #d32f2f;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--color-fondo);
            color: var(--color-texto);
            line-height: 1.6;
        }
        
        header {
            background-color: white;
            padding: 1rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .cart-icon {
            position: relative;
            display: inline-block;
        }
        
        #cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--color-error);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .producto-detalle {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .producto-imagen {
            width: 100%;
            height: 300px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-secundario);
        }
        
        .producto-imagen img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .producto-info {
            padding: 1.5rem;
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--color-texto);
        }
        
        .precio {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-boton);
            margin-bottom: 1rem;
        }
        
        .descripcion {
            margin-bottom: 1.5rem;
            color: var(--color-texto);
        }
        
        .selector {
            margin-bottom: 1.5rem;
        }
        
        .selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-texto);
        }
        
        .selector select, 
        .selector input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--color-borde);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .selector select:focus, 
        .selector input:focus {
            outline: none;
            border-color: var(--color-primario-oscuro);
            box-shadow: 0 0 0 2px rgba(100, 255, 218, 0.2);
        }
        
        .stock-info {
            margin: 1rem 0;
            font-size: 0.9rem;
            padding: 0.5rem;
            border-radius: 4px;
            background-color: var(--color-secundario);
        }
        
        .out-of-stock {
            color: var(--color-error);
        }
        
        .in-stock {
            color: var(--color-exito);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background-color: var(--color-boton);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button[type="submit"]:hover {
            background-color: var(--color-boton-hover);
        }
        
        button[type="submit"]:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Estilos responsivos */
        @media (min-width: 768px) {
            .producto-detalle {
                grid-template-columns: 1fr 1fr;
            }
            
            .producto-imagen {
                height: 500px;
            }
        }
        
        @media (min-width: 992px) {
            .producto-detalle {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="cart-icon">
            <a href="carrito.php">🛒</a>
            <span id="cart-count">0</span>
        </div>
    </header>

    <main class="producto-detalle">
        <div class="producto-imagen">
            <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>
        
        <div class="producto-info">
            <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p class="precio">$<?= number_format($producto['precio'], 2) ?> MXN</p>
            <p class="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></p>
            
            <?php if (count($variantes) > 0): ?>
            <form class="add-to-cart-form">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                
                <!-- Selector de Color -->
                <div class="selector">
                    <label for="color">Color:</label>
                    <select name="color" id="color" required>
                        <option value="">Elige un color</option>
                        <?php foreach ($colores_unicos as $color): ?>
                            <option value="<?= htmlspecialchars($color) ?>"><?= ucfirst(htmlspecialchars($color)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Selector de Talla -->
                <div class="selector">
                    <label for="talla">Talla:</label>
                    <select name="talla" id="talla" required disabled>
                        <option value="">Primero elige un color</option>
                    </select>
                </div>
                
                <!-- Cantidad -->
                <div class="selector">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" name="cantidad" id="cantidad" min="1" value="1" disabled>
                </div>
                
                <!-- Información de stock -->
                <div class="stock-info" id="stock-info">
                    Selecciona color y talla para ver disponibilidad
                </div>
                
                <button type="submit" id="add-to-cart-btn" disabled>Añadir al carrito</button>
            </form>
            
            <?php else: ?>
                <p class="out-of-stock">Producto sin variantes disponibles</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Datos de variantes desde PHP
        const variantes = <?= json_encode($variantes) ?>;
        const stockPorVariante = <?= json_encode($stock_por_variante) ?>;
        
        const colorSelect = document.getElementById('color');
        const tallaSelect = document.getElementById('talla');
        const cantidadInput = document.getElementById('cantidad');
        const stockInfo = document.getElementById('stock-info');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        
        // Manejar cambio de color
        colorSelect?.addEventListener('change', function() {
            const colorSeleccionado = this.value;
            
            // Limpiar selector de tallas
            tallaSelect.innerHTML = '<option value="">Elige una talla</option>';
            tallaSelect.disabled = !colorSeleccionado;
            cantidadInput.disabled = true;
            addToCartBtn.disabled = true;
            stockInfo.textContent = colorSeleccionado ? 'Elige una talla' : 'Selecciona color y talla para ver disponibilidad';
            
            if (colorSeleccionado) {
                // Obtener tallas disponibles para el color seleccionado
                const tallasDisponibles = variantes
                    .filter(v => v.color === colorSeleccionado)
                    .map(v => v.talla);
                
                // Agregar opciones de talla
                [...new Set(tallasDisponibles)].forEach(talla => {
                    const option = document.createElement('option');
                    option.value = talla;
                    option.textContent = talla;
                    tallaSelect.appendChild(option);
                });
            }
        });
        
        // Manejar cambio de talla
        tallaSelect?.addEventListener('change', function() {
            const colorSeleccionado = colorSelect.value;
            const tallaSeleccionada = this.value;
            
            cantidadInput.disabled = !tallaSeleccionada;
            addToCartBtn.disabled = !tallaSeleccionada;
            
            if (colorSeleccionado && tallaSeleccionada) {
                const key = colorSeleccionado + '_' + tallaSeleccionada;
                const stock = stockPorVariante[key] || 0;
                
                if (stock > 0) {
                    cantidadInput.max = stock;
                    cantidadInput.value = Math.min(1, stock);
                    stockInfo.innerHTML = `<span class="in-stock">Stock disponible: ${stock} unidades</span>`;
                    addToCartBtn.disabled = false;
                } else {
                    cantidadInput.max = 0;
                    cantidadInput.value = 0;
                    stockInfo.innerHTML = '<span class="out-of-stock">Sin stock disponible</span>';
                    addToCartBtn.disabled = true;
                }
            } else {
                stockInfo.textContent = 'Selecciona color y talla para ver disponibilidad';
            }
        });
        
        // Validar cantidad al cambiar
        cantidadInput?.addEventListener('change', function() {
            const colorSeleccionado = colorSelect.value;
            const tallaSeleccionada = tallaSelect.value;
            
            if (colorSeleccionado && tallaSeleccionada) {
                const key = colorSeleccionado + '_' + tallaSeleccionada;
                const stock = stockPorVariante[key] || 0;
                
                if (this.value > stock) {
                    this.value = stock;
                    alert(`Solo hay ${stock} unidades disponibles`);
                }
                
                if (this.value < 1) {
                    this.value = 1;
                }
            }
        });
        
        // Manejar añadir al carrito
        document.querySelector('.add-to-cart-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validar que se hayan seleccionado color y talla
            const color = formData.get('color');
            const talla = formData.get('talla');
            const cantidad = parseInt(formData.get('cantidad'));
            
            if (!color || !talla) {
                alert('Por favor selecciona color y talla');
                return;
            }
            
            const key = color + '_' + talla;
            const stockDisponible = stockPorVariante[key] || 0;
            
            if (cantidad > stockDisponible) {
                alert(`Solo hay ${stockDisponible} unidades disponibles`);
                return;
            }
            
            fetch('procesos/add-to-car.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Producto añadido al carrito');
                    // Actualizar contador del carrito
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Opcional: Actualizar stock en tiempo real
                    if (data.new_stock !== undefined) {
                        stockPorVariante[key] = data.new_stock;
                        const stockInfo = document.getElementById('stock-info');
                        if (data.new_stock > 0) {
                            stockInfo.innerHTML = `<span class="in-stock">Stock disponible: ${data.new_stock} unidades</span>`;
                            cantidadInput.max = data.new_stock;
                        } else {
                            stockInfo.innerHTML = '<span class="out-of-stock">Sin stock disponible</span>';
                            addToCartBtn.disabled = true;
                            cantidadInput.disabled = true;
                        }
                    }
                } else {
                    alert(data.message || 'Error al añadir al carrito');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });
    </script>
</body>
</html>