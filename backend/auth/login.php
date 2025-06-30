<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);

  //USE PREPARED STATEMENTS TO PREVENT SQL INJECTION
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password"])) {
      // ✅ Save all necessary user info in session
      $_SESSION["user_id"] = $user["id"];
      $_SESSION["email"] = $user["email"];
      $_SESSION["full_name"] = $user["full_name"]; // <-- Add this line

      // ✅ Redirect to dashboard.php (not dashboard.html)
      header("Location: login-success.php");
      exit();
    } else {
      echo "❌ Incorrect password.";
    }
  } else {
    echo "❌ No account found with that email.";
  }

  $stmt->close();
  $conn->close();
}
?>
