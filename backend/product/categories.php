<?php
header('Content-Type: application/json');
$d = json_decode(file_get_contents(__DIR__.'/../../assets/data/products.json'), true);
$cats = array_unique(array_column($d,'category'));
sort($cats); echo json_encode($cats);
