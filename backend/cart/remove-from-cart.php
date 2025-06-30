<?php
session_start();
$id = (int)($_POST['id']??0);
unset($_SESSION['cart'][$id]);
echo 'removed';
