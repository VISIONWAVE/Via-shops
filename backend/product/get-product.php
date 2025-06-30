<?php
header('Content-Type: application/json');
$id = (int)($_GET['id']??0);
$data = json_decode(file_get_contents(__DIR__.'/../../assets/data/products.json'), true);
foreach ($data as $p) if ($p['id']===$id){ echo json_encode($p); exit;}
http_response_code(404); echo json_encode(['error'=>'Not found']);
