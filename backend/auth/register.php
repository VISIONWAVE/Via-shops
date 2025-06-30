<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $full_name = trim($_POST["full_name"]);
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);
  $confirm_password = trim($_POST["confirm_password"]);

  if ($password !== $confirm_password) {
    echo "❌ Passwords do not match.";
    exit;
  }

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Check if user already exists
  $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "❌ Email already registered.";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $full_name, $email, $hashed_password);

  if ($stmt->execute()) {
    header("Location: ../../login.html?registered=true");
    exit;
  } else {
    echo "❌ Registration failed. Try again.";
  }

  $stmt->close();
  $conn->close();
}
?>
