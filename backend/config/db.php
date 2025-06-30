<?php
$servername = "localhost";
$username = "root";
$password = ""; // default is empty on XAMPP/Laragon
$dbname = "ecommerce";
$port = 3306; // optional, use only if you changed the port

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}
?>
