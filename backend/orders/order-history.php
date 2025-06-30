<?php
session_start();
require_once __DIR__.'/../config/db.php';
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
header('Content-Type: application/json');
echo json_encode($orders);
