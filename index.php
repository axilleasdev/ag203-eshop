<?php
/**
 * Home Page - Product Catalogue
 * 
 * Displays company info, product grid from database, and personalized
 * welcome message for logged-in customers. "Add to Cart" buttons
 * appear only for authenticated users.
 */
session_start();
require_once 'config/database.php';

// Check if customer is logged in via session
$isLoggedIn = isset($_SESSION['customer_id']);

// Fetch all products from database, ordered by newest first
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop — Quality Electronics</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Navigation bar: changes based on login state -->
    <nav class="navbar">
        <a href="index.php" class="logo">🛒 TechShop</a>
        <div class="nav-links">
            <a href="index.php" class="active">Shop</a>
            <?php if ($isLoggedIn): ?>
                <!-- Logged-in: show cart, greeting, logout -->
                <a href="cart.php">Cart</a>
                <span class="welcome">Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!</span>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <!-- Visitor: show sign-in/sign-up -->
                <a href="signin.php">Sign In</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <!-- Company presentation section -->
        <section class="hero">
            <h1>Welcome to TechShop</h1>
            <p>Your one-stop destination for premium tech accessories. We offer carefully selected electronics with fast shipping and excellent customer support.</p>
        </section>

        <!-- Product grid: dynamically generated from products table -->
        <section class="products-section">
            <h2>Our Products</h2>
            <?php if (empty($products)): ?>
                <p class="empty-state">No products available at the moment.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <!-- Product image with alt text for accessibility -->
                            <?php if ($product['image_path']): ?>
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image-placeholder">📦</div>
                            <?php endif; ?>

                            <div class="product-card-body">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="product-price">£<?= number_format($product['price'], 2) ?></p>
                                <p class="product-stock">
                                    <?= $product['stock'] > 0 ? "In Stock ({$product['stock']})" : '<span class="out-of-stock">Out of Stock</span>' ?>
                                </p>

                                <!-- Add to Cart: only for logged-in users with stock > 0 -->
                                <?php if ($isLoggedIn && $product['stock'] > 0): ?>
                                    <button class="btn btn-primary add-to-cart" 
                                            data-product-id="<?= $product['id'] ?>">
                                        Add to Cart
                                    </button>
                                <?php elseif (!$isLoggedIn): ?>
                                    <a href="signin.php" class="btn btn-secondary">Sign in to buy</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2026 TechShop — AG203 Assignment 2, University of Essex</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
