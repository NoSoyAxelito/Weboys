document.addEventListener('DOMContentLoaded', function() {
    // Manejar eliminación de productos
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            
            fetch('procesos/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${encodeURIComponent(itemId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar visualmente el item
                    document.querySelector(`.cart-item[data-id="${itemId}"]`).remove();
                    // Actualizar total
                    updateCartTotal();
                    // Actualizar contador en el header
                    updateHeaderCartCount(data.cart_count);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el producto');
            });
        });
    });

    // Manejar aumento de cantidad
    document.querySelectorAll('.increase-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            updateQuantity(itemId, 1);
        });
    });

    // Manejar disminución de cantidad
    document.querySelectorAll('.decrease-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            updateQuantity(itemId, -1);
        });
    });

    // Función para actualizar cantidad
    function updateQuantity(itemId, change) {
        fetch('procesos/update-cart-quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${encodeURIComponent(itemId)}&change=${change}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const quantityElement = document.querySelector(`.cart-item[data-id="${itemId}"] .quantity`);
                const subtotalElement = document.querySelector(`.cart-item[data-id="${itemId}"] .subtotal`);
                
                // Actualizar cantidad mostrada
                quantityElement.textContent = data.new_quantity;
                
                // Actualizar subtotal
                if (subtotalElement) {
                    const unitPrice = parseFloat(subtotalElement.textContent.replace('$', '')) / parseInt(quantityElement.textContent);
                    subtotalElement.textContent = '$' + (unitPrice * data.new_quantity).toFixed(2);
                }
                
                // Actualizar total
                updateCartTotal();
                // Actualizar contador en el header
                updateHeaderCartCount(data.cart_count);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar la cantidad');
        });
    }

    // Función para actualizar el total del carrito
    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(element => {
            total += parseFloat(element.textContent.replace('$', ''));
        });
        document.getElementById('cart-total').textContent = total.toFixed(2);
    }

    // Función para actualizar el contador en el header
    function updateHeaderCartCount(count) {
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }
});