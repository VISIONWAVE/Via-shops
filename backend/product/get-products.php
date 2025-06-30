<?php
header('Content-Type: application/json');
$file = __DIR__.'/../../assets/data/products.json';
echo file_get_contents($file);   // swap for DB SELECT later
