<?php
session_start();
include 'includes/conexion.php';

// Verificar si hay productos en el carrito
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Procesar acciones del carrito (eliminar, actualizar cantidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $item_id = $_POST['item_id'] ?? null;
        
        switch ($action) {
            case 'remove':
                if (isset($carrito[$item_id])) {
                    unset($carrito[$item_id]);
                    $_SESSION['carrito'] = $carrito;
                }
                break;
                
            case 'update_quantity':
                $new_quantity = (int)($_POST['quantity'] ?? 0);
                if (isset($carrito[$item_id]) && $new_quantity > 0) {
                    $carrito[$item_id]['cantidad'] = $new_quantity;
                    $_SESSION['carrito'] = $carrito;
                }
                break;
        }
        
        // Si es una solicitud AJAX, devolver JSON y terminar
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            
            // Recalcular total
            $total = 0;
            if (!empty($carrito)) {
                $ids = array_column($carrito, 'producto_id');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "SELECT id, precio FROM productos WHERE id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $types = str_repeat('i', count($ids));
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $productos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos[$row['id']] = $row;
                }
                
                foreach ($carrito as $item) {
                    if (isset($productos[$item['producto_id']])) {
                        $total += $productos[$item['producto_id']]['precio'] * $item['cantidad'];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'cart_count' => count($carrito),
                'total' => number_format($total, 2),
                'item_removed' => ($action === 'remove') ? $item_id : null
            ]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --verde-menta: #4ECDC4;
            --verde-menta-claro: #6FDFD6;
            --verde-menta-oscuro: #3BB5AD;
            --verde-agua: #A8E6CF;
            --verde-suave: #F0FFF8;
            --blanco: #FFFFFF;
            --gris-claro: #F8F9FA;
            --gris-medio: #6C757D;
            --gris-oscuro: #343A40;
            --negro: #212529;
            --sombre: 0 4px 6px rgba(0, 0, 0, 0.1);
            --sombre-hover: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--verde-suave) 0%, var(--gris-claro) 100%);
            color: var(--negro);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* Header mejorado */
        header {
            background: linear-gradient(135deg, var(--verde-menta) 0%, var(--verde-menta-claro) 100%);
            padding: 2rem 1rem;
            text-align: center;
            box-shadow: var(--sombre);
            position: relative;
            margin-bottom: 2rem;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.1) 2px, transparent 2px),
                        radial-gradient(circle at 70% 70%, rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 30px 30px, 50px 50px;
        }
        
        h1 {
            color: var(--blanco);
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .header-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.2);
            color: var(--blanco);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
        }
        
        .header-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: var(--sombre-hover);
        }
        
        /* Contenedor principal */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }
        
        .cart-container {
            background: var(--blanco);
            border-radius: 20px;
            box-shadow: var(--sombre);
            padding: 2rem;
            border: 1px solid rgba(78, 205, 196, 0.1);
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--verde-suave);
        }
        
        .cart-title {
            font-size: 1.8rem;
            color: var(--verde-menta-oscuro);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .cart-subtitle {
            color: var(--gris-medio);
            font-size: 1rem;
        }
        
        /* Items del carrito */
        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            border-radius: 15px;
            background: var(--gris-claro);
            border-left: 5px solid var(--verde-menta);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 90%, var(--verde-agua) 100%);
            pointer-events: none;
        }
        
        .cart-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--sombre-hover);
            background: var(--blanco);
            border-left-color: var(--verde-menta-claro);
        }
        
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-right: 1.5rem;
            border-radius: 12px;
            border: 3px solid var(--verde-agua);
            transition: transform 0.3s ease;
        }
        
        .cart-item:hover img {
            transform: scale(1.05);
            border-color: var(--verde-menta);
        }
        
        .cart-item-info {
            flex-grow: 1;
            position: relative;
            z-index: 1;
        }
        
        .cart-item-info h3 {
            margin: 0 0 0.75rem 0;
            color: var(--verde-menta-oscuro);
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .product-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .detail-tag {
            background: var(--verde-agua);
            color: var(--verde-menta-oscuro);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .unit-price {
            color: var(--gris-medio);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        /* Controles de cantidad */
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--blanco);
            padding: 0.5rem;
            border-radius: 25px;
            border: 2px solid var(--verde-agua);
            width: fit-content;
        }
        
        .quantity-controls button {
            background: var(--verde-menta);
            color: var(--blanco);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        
        .quantity-controls button:hover {
            background: var(--verde-menta-oscuro);
            transform: scale(1.1);
        }
        
        .quantity-controls button:active {
            transform: scale(0.95);
        }
        
        .quantity {
            margin: 0 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--negro);
            min-width: 30px;
            text-align: center;
        }
        
        /* Precio y botón eliminar */
        .item-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .subtotal {
            font-weight: 700;
            color: var(--verde-menta-oscuro);
            font-size: 1.3rem;
            text-align: right;
        }
        
        .remove-from-cart {
            background: linear-gradient(135deg, #FF6B6B, #FF5252);
            color: var(--blanco);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .remove-from-cart:hover {
            background: linear-gradient(135deg, #FF5252, #F44336);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
        }
        
        /* Total del carrito */
        .cart-total {
            margin-top: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--verde-suave) 0%, var(--verde-agua) 100%);
            border-radius: 20px;
            border: 2px solid var(--verde-menta);
            text-align: right;
        }
        
        .total-label {
            font-size: 1.8rem;
            margin: 0 0 1.5rem 0;
            color: var(--verde-menta-oscuro);
            font-weight: 700;
        }
        
        #checkout-btn {
            background: linear-gradient(135deg, var(--verde-menta) 0%, var(--verde-menta-claro) 100%);
            color: var(--blanco);
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--sombre);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        #checkout-btn:hover {
            background: linear-gradient(135deg, var(--verde-menta-oscuro) 0%, var(--verde-menta) 100%);
            transform: translateY(-3px);
            box-shadow: var(--sombre-hover);
        }
        
        #checkout-btn:active {
            transform: translateY(-1px);
        }
        
        /* Carrito vacío */
        .cart-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gris-medio);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: var(--verde-menta);
            margin-bottom: 1rem;
        }
        
        .empty-title {
            font-size: 1.8rem;
            color: var(--verde-menta-oscuro);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .empty-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .shop-now-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--verde-menta) 0%, var(--verde-menta-claro) 100%);
            color: var(--blanco);
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--sombre);
        }
        
        .shop-now-btn:hover {
            background: linear-gradient(135deg, var(--verde-menta-oscuro) 0%, var(--verde-menta) 100%);
            transform: translateY(-2px);
            box-shadow: var(--sombre-hover);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                padding: 1.5rem 1rem;
            }
            
            .cart-container {
                padding: 1.5rem;
                border-radius: 15px;
            }
            
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.25rem;
            }
            
            .cart-item img {
                width: 100px;
                height: 100px;
                margin-right: 0;
                margin-bottom: 1rem;
                align-self: center;
            }
            
            .item-right {
                align-self: stretch;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .subtotal {
                font-size: 1.2rem;
            }
            
            .cart-total {
                text-align: center;
                padding: 1.5rem;
            }
            
            .total-label {
                font-size: 1.5rem;
            }
            
            #checkout-btn {
                width: 100%;
                justify-content: center;
                padding: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            .product-details {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .detail-tag {
                text-align: center;
            }
            
            .cart-item img {
                width: 80px;
                height: 80px;
            }
        }
        
        /* Animaciones */
        .cart-item {
            animation: slideInUp 0.5s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Animación para eliminar items */
        .cart-item.removing {
            animation: slideOutRight 0.3s ease-out forwards;
        }
        
        @keyframes slideOutRight {
            to {
                opacity: 0;
                transform: translateX(100px);
                height: 0;
                margin-bottom: 0;
                padding-top: 0;
                padding-bottom: 0;
                border: none;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>🛒 Tu Carrito </h1>
        <a href="productos.php" class="header-link">
            ← Seguir comprando
        </a>
    </header>

    <main>
        <div class="cart-container">
            <?php if (!empty($carrito)): ?>
                <div class="cart-header">
                    <h2 class="cart-title">Productos en tu carrito</h2>
                    <p class="cart-subtitle"><?= count($carrito) ?> producto<?= count($carrito) > 1 ? 's' : '' ?> seleccionado<?= count($carrito) > 1 ? 's' : '' ?></p>
                </div>

                <div id="cart-items">
                    <?php 
                    // Obtener IDs de productos en el carrito
                    $ids = array_column($carrito, 'producto_id');
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $sql = "SELECT * FROM productos WHERE id IN ($placeholders)";
                        $stmt = $conn->prepare($sql);
                        
                        // Bind parameters
                        $types = str_repeat('i', count($ids));
                        $stmt->bind_param($types, ...$ids);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        // Mapear productos por ID para fácil acceso
                        $productos = [];
                        while ($row = $result->fetch_assoc()) {
                            $productos[$row['id']] = $row;
                        }
                        
                        // Mostrar cada item del carrito
                        foreach ($carrito as $item_id => $item): 
                            $producto = $productos[$item['producto_id']] ?? null;
                            if (!$producto) continue;
                            
                            $subtotal = $producto['precio'] * $item['cantidad'];
                            $total += $subtotal;
                    ?>
                            <div class="cart-item" data-id="<?= $item_id ?>">
                                <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                
                                <div class="cart-item-info">
                                    <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                    <div class="product-details">
                                        <span class="detail-tag">🎨 <?= htmlspecialchars($item['color']) ?></span>
                                        <span class="detail-tag">📏 <?= htmlspecialchars($item['talla']) ?></span>
                                    </div>
                                    <p class="unit-price">💰 Precio unitario: $<?= number_format($producto['precio'], 2) ?></p>
                                    <div class="quantity-controls">
                                        <button class="decrease-quantity" data-id="<?= $item_id ?>">−</button>
                                        <span class="quantity"><?= $item['cantidad'] ?></span>
                                        <button class="increase-quantity" data-id="<?= $item_id ?>">+</button>
                                    </div>
                                </div>
                                
                                <div class="item-right">
                                    <p class="subtotal">$<?= number_format($subtotal, 2) ?></p>
                                    <button class="remove-from-cart" data-id="<?= $item_id ?>">
                                        🗑️ Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; 
                    }?>
                </div>

                <div class="cart-total">
                    <p class="total-label">Total: $<span id="cart-total"><?= number_format($total, 2) ?></span></p>
                    <button id="checkout-btn">
                        💳 Proceder al Pago
                    </button>
                </div>

            <?php else: ?>
                <div class="cart-empty">
                    <div class="empty-icon">🛍️</div>
                    <h2 class="empty-title">Tu carrito está vacío</h2>
                    <p class="empty-message">¡Descubre nuestros increíbles productos y empieza a llenar tu carrito!</p>
                    <a href="productos.php" class="shop-now-btn">
                        🛒 Explorar productos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar aumento/disminución de cantidad
            document.querySelectorAll('.quantity-controls button').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    const quantityElement = this.parentElement.querySelector('.quantity');
                    let quantity = parseInt(quantityElement.textContent);
                    
                    if (this.classList.contains('increase-quantity')) {
                        quantity++;
                    } else if (this.classList.contains('decrease-quantity')) {
                        if (quantity > 1) quantity--;
                    }
                    
                    updateQuantity(itemId, quantity);
                });
            });
            
            // Manejar eliminación de items
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-id');
                    const cartItem = document.querySelector(`.cart-item[data-id="${itemId}"]`);
                    
                    // Animación de eliminación
                    cartItem.classList.add('removing');
                    
                    // Eliminar después de la animación
                    setTimeout(() => {
                        removeItem(itemId);
                    }, 300);
                });
            });
            
            // Función para actualizar cantidad
            function updateQuantity(itemId, quantity) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=update_quantity&item_id=${itemId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar la cantidad mostrada
                        const quantityElement = document.querySelector(`.cart-item[data-id="${itemId}"] .quantity`);
                        if (quantityElement) {
                            quantityElement.textContent = quantity;
                        }
                        
                        // Recalcular y actualizar subtotal
                        updateSubtotal(itemId, quantity);
                        
                        // Actualizar total general
                        document.getElementById('cart-total').textContent = data.total;
                        
                        // Actualizar contador de productos
                        const cartSubtitle = document.querySelector('.cart-subtitle');
                        if (cartSubtitle) {
                            const count = data.cart_count;
                            cartSubtitle.textContent = `${count} producto${count > 1 ? 's' : ''} seleccionado${count > 1 ? 's' : ''}`;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Función para eliminar item
            function removeItem(itemId) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=remove&item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar el elemento del DOM si no se ha eliminado ya por la animación
                        const cartItem = document.querySelector(`.cart-item[data-id="${itemId}"]`);
                        if (cartItem) {
                            cartItem.remove();
                        }
                        
                        // Actualizar total general
                        document.getElementById('cart-total').textContent = data.total;
                        
                        // Actualizar contador de productos
                        const cartSubtitle = document.querySelector('.cart-subtitle');
                        if (cartSubtitle) {
                            const count = data.cart_count;
                            if (count > 0) {
                                cartSubtitle.textContent = `${count} producto${count > 1 ? 's' : ''} seleccionado${count > 1 ? 's' : ''}`;
                            } else {
                                // Si no hay más productos, recargar la página para mostrar el carrito vacío
                                location.reload();
                            }
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Función para actualizar subtotal de un item
            function updateSubtotal(itemId, quantity) {
                const unitPriceText = document.querySelector(`.cart-item[data-id="${itemId}"] .unit-price`).textContent;
                const unitPrice = parseFloat(unitPriceText.replace(/[^0-9.]/g, ''));
                const subtotal = unitPrice * quantity;
                
                const subtotalElement = document.querySelector(`.cart-item[data-id="${itemId}"] .subtotal`);
                if (subtotalElement) {
                    subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
                }
            }
            
            // Manejar el botón de pago
            document.getElementById('checkout-btn')?.addEventListener('click', function() {
                // Aquí puedes redirigir a la página de pago
                window.location.href = 'checkout.php';
            });
        });
    </script>
</body>
</html>