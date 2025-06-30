<?php
header('Content-Type: application/json');
$q = strtolower($_GET['q']??'');
$d = json_decode(file_get_contents(__DIR__.'/../../assets/data/products.json'), true);
$found = array_values(array_filter($d, fn($p)=>strpos(strtolower($p['name']),$q)!==false));
echo json_encode($found);
