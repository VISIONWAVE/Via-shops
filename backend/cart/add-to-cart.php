<?php
session_start();
$id = (int)($_POST['id']??0);
if(!$id){http_response_code(400);exit;}
$_SESSION['cart'][$id] = ($_SESSION['cart'][$id]??0)+1;
echo 'added';
