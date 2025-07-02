<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../config/db.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);

    if ($productId > 0) {
        try {
            // Verify product exists
            $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            if ($stmt->fetch()) {
                // Initialize cart if not exists
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                // Update quantity
                $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $quantity;
                
                // Sync with database if logged in
                if (isset($_SESSION['user_id'])) {
                    $userId = $_SESSION['user_id'];
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (user_id, product_id, quantity)
                        VALUES (:user_id, :product_id, :quantity)
                        ON DUPLICATE KEY UPDATE quantity = quantity + :quantity
                    ");
                    $stmt->execute([
                        'user_id' => $userId,
                        'product_id' => $productId,
                        'quantity' => $quantity
                    ]);
                }
                
                $response['success'] = true;
                $response['cart_count'] = array_sum($_SESSION['cart']);
            } else {
                $response['error'] = 'Product not found';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Database error';
        }
    } else {
        $response['error'] = 'Invalid product ID';
    }
} else {
    $response['error'] = 'Invalid request method';
}

echo json_encode($response);
?>