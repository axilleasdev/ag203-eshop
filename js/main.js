/**
 * TechShop - Main JavaScript
 * 
 * Handles:
 * - Add to Cart (AJAX POST to api/cart.php)
 * - Quantity update (+/- buttons)
 * - Remove item from cart
 * 
 * Uses vanilla JavaScript (Fetch API) instead of jQuery
 * to demonstrate modern async/await patterns.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==================== ADD TO CART ====================
    // Event delegation: listen on document for dynamically rendered buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async (e) => {
            const productId = e.target.dataset.productId;
            
            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=add&product_id=${productId}`
                });
                const data = await response.json();

                if (data.success) {
                    // Visual feedback: briefly change button text
                    e.target.textContent = '✓ Added!';
                    e.target.disabled = true;
                    setTimeout(() => {
                        e.target.textContent = 'Add to Cart';
                        e.target.disabled = false;
                    }, 1500);
                } else {
                    alert(data.message || 'Failed to add item.');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            }
        });
    });

    // ==================== QUANTITY BUTTONS ====================
    document.querySelectorAll('.btn-qty').forEach(button => {
        button.addEventListener('click', async (e) => {
            const cartId = e.target.dataset.cartId;
            const direction = e.target.dataset.action;

            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update&cart_id=${cartId}&direction=${direction}`
                });
                const data = await response.json();

                if (data.success) {
                    // Update total display
                    document.getElementById('cart-total').textContent = data.total;

                    // Find the row and update quantity or remove if gone
                    const row = e.target.closest('tr');
                    const item = data.items.find(i => i.cart_id == cartId);

                    if (item) {
                        row.querySelector('.qty-value').textContent = item.quantity;
                        row.querySelector('.line-total').textContent = 
                            '£' + (item.price * item.quantity).toFixed(2);
                    } else {
                        // Item was removed (quantity reached 0)
                        row.remove();
                        // If cart is now empty, reload to show empty state
                        if (!document.querySelector('.cart-table tbody tr')) {
                            location.reload();
                        }
                    }
                }
            } catch (error) {
                alert('Network error. Please try again.');
            }
        });
    });

    // ==================== REMOVE ITEM ====================
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async (e) => {
            const cartId = e.target.dataset.cartId;

            try {
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=remove&cart_id=${cartId}`
                });
                const data = await response.json();

                if (data.success) {
                    // Remove row from DOM
                    e.target.closest('tr').remove();
                    // Update total
                    document.getElementById('cart-total').textContent = data.total;
                    // If cart empty, reload for empty state
                    if (!document.querySelector('.cart-table tbody tr')) {
                        location.reload();
                    }
                }
            } catch (error) {
                alert('Network error. Please try again.');
            }
        });
    });
});
