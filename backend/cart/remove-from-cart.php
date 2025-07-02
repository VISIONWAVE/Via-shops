<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../config/db.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        
        // Sync with database if logged in
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("
                    DELETE FROM cart_items 
                    WHERE user_id = ? AND product_id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $productId]);
            } catch (PDOException $e) {
                // Log error but don't fail the request
                error_log("Cart removal error: " . $e->getMessage());
            }
        }
        
        $response['success'] = true;
        $response['cart_count'] = array_sum($_SESSION['cart']);
    } else {
        $response['error'] = 'Invalid product or not in cart';
    }
} else {
    $response['error'] = 'Invalid request method';
}

echo json_encode($response);
?>