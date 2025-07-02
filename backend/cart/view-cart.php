<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../config/db.php';

$response = ['items' => [], 'total' => 0];

try {
    // Get cart items from database if logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, p.image, ci.quantity
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $dbItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge with session cart
        foreach ($dbItems as $item) {
            $response['items'][$item['id']] = $item;
            $response['total'] += $item['price'] * $item['quantity'];
        }
    }
    
    // Add session cart items (for guests or sync)
    if (isset($_SESSION['cart'])) {
        $productIds = array_keys($_SESSION['cart']);
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $pdo->prepare("
                SELECT id, name, price, image
                FROM products
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($productIds);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $quantity = $_SESSION['cart'][$product['id']];
                $response['items'][$product['id']] = array_merge($product, ['quantity' => $quantity]);
                $response['total'] += $product['price'] * $quantity;
            }
        }
    }
    
    $response['success'] = true;
} catch (PDOException $e) {
    $response['error'] = 'Database error';
}

echo json_encode($response);
?>