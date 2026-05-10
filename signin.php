<?php
/**
 * Sign-In Page - Customer Authentication
 * 
 * Verifies credentials against database using password_verify().
 * On success: sets $_SESSION['customer_id'] and $_SESSION['first_name'],
 * then redirects to index.php where personalized welcome appears.
 * 
 * Security: password_verify() compares plaintext input with stored bcrypt hash
 * without ever decrypting the hash — timing-safe comparison.
 */
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        // Find customer by email using prepared statement
        $stmt = $pdo->prepare("SELECT id, first_name, password FROM customers WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $customer = $stmt->fetch();

        // Verify password against stored bcrypt hash
        if ($customer && password_verify($password, $customer['password'])) {
            // Set session variables for personalized experience
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['first_name'] = $customer['first_name'];
            header('Location: index.php');
            exit;
        } else {
            // Generic error message — don't reveal if email exists
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — TechShop</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">🛒 TechShop</a>
        <div class="nav-links">
            <a href="index.php">Shop</a>
            <a href="signin.php" class="active">Sign In</a>
            <a href="signup.php">Sign Up</a>
        </div>
    </nav>

    <main class="container">
        <div class="form-card">
            <h1>Sign In</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="signin-form" method="POST" action="signin.php" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($email ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <p class="form-footer">Don't have an account? <a href="signup.php">Create one here</a></p>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
