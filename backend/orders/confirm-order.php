<?php session_start(); ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Order Confirmed</title>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body class="dark-mode">
<h1>✅ Thank you, <?= htmlspecialchars($_POST['name']??'Customer') ?>!</h1>
<p>Your order has been placed. We’ll email you shortly.</p>
<a href="products.php">Continue Shopping</a>
<script>localStorage.removeItem('cart');</script>
</body></html>
