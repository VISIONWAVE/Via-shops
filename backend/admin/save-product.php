<?php
// backend/admin/save-product.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

require_once __DIR__.'/../config/db.php'; // optional if saving to DB later

$name     = trim($_POST['name'] ?? '');
$price    = (float)($_POST['price'] ?? 0);
$category = trim($_POST['category'] ?? '');
$desc     = trim($_POST['description'] ?? '');

if (!$name || !$price || !$category || empty($_FILES['image']['name'])) {
    die("⚠️ Missing required fields.");
}

// Validate image
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize      = 2 * 1024 * 1024; // 2MB max

if ($_FILES['image']['size'] > $maxSize) {
    die("⚠️ Image too large. Max 2MB.");
}
if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    die("⚠️ Invalid file type.");
}

// Slugify category to use as folder name
$slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $category));
$imgRoot   = __DIR__ . '/../../assets/images';
$targetDir = "$imgRoot/$slug";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Generate safe filename
$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '.' . strtolower($ext);
$destPath = "$targetDir/$filename";

if (!move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
    die("❌ Failed to save uploaded image.");
}

// Append to JSON
$jsonFile = __DIR__ . '/../../assets/data/products.json';
$products = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

$nextId = $products ? max(array_column($products, 'id')) + 1 : 1;

$products[] = [
    'id'          => $nextId,
    'name'        => $name,
    'price'       => $price,
    'image'       => "$slug/$filename", // relative path
    'category'    => $category,
    'rating'      => 0,
    'description' => $desc
];

file_put_contents($jsonFile, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

header('Location: ../../products.html?added=1');
exit;
?>
