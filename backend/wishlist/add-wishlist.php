<?php session_start();
$id=(int)($_POST['id']??0); $_SESSION['wishlist'][$id]=true; echo 'wish added';
