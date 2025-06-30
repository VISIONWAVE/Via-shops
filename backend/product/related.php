<?php
header('Content-Type: application/json');
$id = (int)($_GET['id'] ?? 0);

$data = json_decode(file_get_contents(__DIR__ . '/../../assets/data/products.json'), true);
$current = null;

foreach ($data as $item) {
  if ($item['id'] === $id) {
    $current = $item;
    break;
  }
}

if (!$current) {
  echo json_encode(['error' => 'Product not found']);
  exit;
}

// Filter by same category but different ID
$related = array_values(array_filter($data, function($p) use ($current) {
  return $p['id'] !== $current['id'] && $p['category'] === $current['category'];
}));

echo json_encode($related);
?>
// Note: This code retrieves related products based on the same category as the current product, excluding