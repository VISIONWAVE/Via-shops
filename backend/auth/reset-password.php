<?php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($token) || empty($password) || empty($confirm)) {
        die("❌ All fields are required.");
    }

    if ($password !== $confirm) {
        die("❌ Passwords do not match.");
    }

    // Check token
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $expires_at);
    $stmt->fetch();

    if ($stmt->num_rows !== 1) {
        die("❌ Invalid or expired token.");
    }

    if (strtotime($expires_at) < time()) {
        die("❌ Token expired. Please request a new one.");
    }

    // Hash new password
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // Update user's password
    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->bind_param("si", $hashed, $user_id);
    $update->execute();

    // Delete token
    $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $delete->bind_param("s", $token);
    $delete->execute();

    echo "✅ Password successfully reset. <a href='../../login.html'>Login now</a>";
} else {
    header("Location: ../../login.html");
    exit();
}
?>
