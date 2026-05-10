<?php
/**
 * Cart API - Add, Update, Remove cart items
 * 
 * Handles AJAX requests from the frontend:
 * - POST action=add: Add product to cart (or increment if exists)
 * - POST action=update: Change quantity (+/-)
 * - POST action=remove: Delete item from cart
 * 
 * Returns JSON responses for dynamic UI updates without page reload.
 * Security: session check + prepared statements + input validation.
 */
session_start();
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Access control: must be logged in
if (!isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$customerId = $_SESSION['customer_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    /**
     * ADD TO CART
     * If product already in cart, increment quantity.
     * Otherwise, insert new cart row with quantity = 1.
     */
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }

        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = :cid AND product_id = :pid");
        $stmt->execute([':cid' => $customerId, ':pid' => $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Increment quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = :id");
            $stmt->execute([':id' => $existing['id']]);
        } else {
            // Insert new cart item
            $stmt = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (:cid, :pid, 1)");
            $stmt->execute([':cid' => $customerId, ':pid' => $productId]);
        }

        // Return updated cart count for badge
        $stmt = $pdo->prepare("SELECT SUM(quantity) AS count FROM cart WHERE customer_id = :cid");
        $stmt->execute([':cid' => $customerId]);
        $count = $stmt->fetch()['count'] ?? 0;

        echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => (int)$count]);
        break;

    /**
     * UPDATE QUANTITY
     * Increase or decrease quantity. If quantity reaches 0, remove item.
     */
    case 'update':
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $direction = $_POST['direction'] ?? '';

        if ($cartId <= 0 || !in_array($direction, ['increase', 'decrease'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        if ($direction === 'increase') {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = :id AND customer_id = :cid");
            $stmt->execute([':id' => $cartId, ':cid' => $customerId]);
        } else {
            // Decrease: if quantity = 1, remove item entirely
            $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE id = :id AND customer_id = :cid");
            $stmt->execute([':id' => $cartId, ':cid' => $customerId]);
            $item = $stmt->fetch();

            if ($item && $item['quantity'] <= 1) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :id AND customer_id = :cid");
                $stmt->execute([':id' => $cartId, ':cid' => $customerId]);
            } else {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE id = :id AND customer_id = :cid");
                $stmt->execute([':id' => $cartId, ':cid' => $customerId]);
            }
        }

        // Return updated cart data for UI refresh
        $stmt = $pdo->prepare("
            SELECT c.id AS cart_id, c.quantity, p.price
            FROM cart c JOIN products p ON c.product_id = p.id
            WHERE c.customer_id = :cid
        ");
        $stmt->execute([':cid' => $customerId]);
        $items = $stmt->fetchAll();

        $total = 0;
        foreach ($items as $i) {
            $total += $i['price'] * $i['quantity'];
        }

        echo json_encode(['success' => true, 'total' => number_format($total, 2), 'items' => $items]);
        break;

    /**
     * REMOVE FROM CART
     * Delete cart row entirely. customer_id check prevents unauthorized deletion.
     */
    case 'remove':
        $cartId = (int)($_POST['cart_id'] ?? 0);
        if ($cartId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :id AND customer_id = :cid");
        $stmt->execute([':id' => $cartId, ':cid' => $customerId]);

        // Return updated total
        $stmt = $pdo->prepare("
            SELECT SUM(p.price * c.quantity) AS total
            FROM cart c JOIN products p ON c.product_id = p.id
            WHERE c.customer_id = :cid
        ");
        $stmt->execute([':cid' => $customerId]);
        $result = $stmt->fetch();
        $total = $result['total'] ? number_format($result['total'], 2) : '0.00';

        echo json_encode(['success' => true, 'total' => $total]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
