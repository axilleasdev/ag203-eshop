<?php
/**
 * Shopping Cart Page
 * 
 * Displays items in the customer's cart with:
 * - Product details (name, price, image)
 * - Quantity controls (+/- buttons)
 * - Remove item button
 * - Total price calculation
 * 
 * Access control: only logged-in customers can view their cart.
 * Cart is database-based (persistent across sessions/devices).
 */
session_start();
require_once 'config/database.php';

// Access control: redirect to signin if not logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: signin.php');
    exit;
}

$customerId = $_SESSION['customer_id'];

// Fetch cart items with product details using JOIN
// SUM(p.price * c.quantity) calculates line totals
$stmt = $pdo->prepare("
    SELECT c.id AS cart_id, c.quantity, 
           p.id AS product_id, p.name, p.price, p.image_path, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.customer_id = :customer_id
    ORDER BY c.created_at DESC
");
$stmt->execute([':customer_id' => $customerId]);
$cartItems = $stmt->fetchAll();

// Calculate total price using PHP (could also use SQL SUM)
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — TechShop</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">🛒 TechShop</a>
        <div class="nav-links">
            <a href="index.php">Shop</a>
            <a href="cart.php" class="active">Cart</a>
            <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="container">
        <h1>Your Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <p>Your cart is empty.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr data-cart-id="<?= $item['cart_id'] ?>">
                                <td class="cart-product">
                                    <?php if ($item['image_path']): ?>
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" class="cart-thumb">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($item['name']) ?></span>
                                </td>
                                <td>£<?= number_format($item['price'], 2) ?></td>
                                <td class="cart-quantity">
                                    <!-- Quantity decrease button -->
                                    <button class="btn-qty" data-action="decrease" 
                                            data-cart-id="<?= $item['cart_id'] ?>">−</button>
                                    <span class="qty-value"><?= $item['quantity'] ?></span>
                                    <!-- Quantity increase button (max = stock) -->
                                    <button class="btn-qty" data-action="increase" 
                                            data-cart-id="<?= $item['cart_id'] ?>"
                                            data-max="<?= $item['stock'] ?>">+</button>
                                </td>
                                <td class="line-total">£<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <button class="btn btn-danger remove-item" 
                                            data-cart-id="<?= $item['cart_id'] ?>">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Cart total -->
                <div class="cart-total">
                    <strong>Total: £<span id="cart-total"><?= number_format($total, 2) ?></span></strong>
                </div>

                <div class="cart-actions">
                    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2026 TechShop — AG203 Assignment 2, University of Essex</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
