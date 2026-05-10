<?php
/**
 * Sign-Up Page - Customer Registration
 * 
 * Collects: first_name, last_name, email, password, confirm_password
 * Security: password_hash(PASSWORD_BCRYPT) — one-way hash with unique salt
 * Validation: server-side checks (empty fields, email format, password match)
 * On success: creates session and redirects to index.php with welcome message
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
    // Collect and sanitize input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists (prepared statement prevents SQL injection)
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            // Hash password with bcrypt (cost=10, unique salt per user)
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Insert new customer
            $stmt = $pdo->prepare(
                "INSERT INTO customers (first_name, last_name, email, password) 
                 VALUES (:first_name, :last_name, :email, :password)"
            );
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email,
                ':password' => $hashed
            ]);

            // Auto-login after registration: set session variables
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['first_name'] = $first_name;

            // Redirect to home with personalized welcome
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — TechShop</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">🛒 TechShop</a>
        <div class="nav-links">
            <a href="index.php">Shop</a>
            <a href="signin.php">Sign In</a>
            <a href="signup.php" class="active">Sign Up</a>
        </div>
    </nav>

    <main class="container">
        <div class="form-card">
            <h1>Create Account</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="signup-form" method="POST" action="signup.php" novalidate>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                           value="<?= htmlspecialchars($first_name ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?= htmlspecialchars($last_name ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($email ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <p class="form-footer">Already have an account? <a href="signin.php">Sign in here</a></p>
        </div>
    </main>

    <script src="js/main.js"></script>
</body>
</html>
