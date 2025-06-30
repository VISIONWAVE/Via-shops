// ----------------------------------------------
// 4.  backend/cart/checkout.php (replaced)
// ----------------------------------------------
<?php
session_start();
require_once __DIR__.'/../config/db.php';
if(empty($_SESSION['cart'])){die('Cart empty');}

$name = $_POST['name']??'';
$addr = $_POST['address']??'';
$total = array_sum($_SESSION['cart']);   // demo total

//  Insert order header
$stmt = $conn->prepare("INSERT INTO orders (customer_name,address,total) VALUES (?,?,?)");
$stmt->bind_param("ssd",$name,$addr,$total);
$stmt->execute();
$orderId = $stmt->insert_id;

//  Insert order items
foreach($_SESSION['cart'] as $pid=>$qty){
  $s = $conn->prepare("INSERT INTO order_items (order_id,product_id,qty) VALUES (?,?,?)");
  $s->bind_param("iii",$orderId,$pid,$qty); $s->execute();
}

unset($_SESSION['cart']);
header("Location: ../../order-confirmation.php?order=$orderId");
?>