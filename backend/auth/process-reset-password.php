<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: ../../reset-password.php?error=All fields are required.");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: ../../reset-password.php?token=$token&error=Passwords do not match.");
        exit();
    }

    // Get reset request
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($email, $expires_at);
        $stmt->fetch();

        if (strtotime($expires_at) < time()) {
            header("Location: ../../reset-password.php?error=Token has expired.");
            exit();
        }

        // Update password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed, $email);
        $update->execute();

        // Delete token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $delete->bind_param("s", $token);
        $delete->execute();

        header("Location: ../../reset-password.php?success=Password successfully updated.");
        exit();

    } else {
        header("Location: ../../reset-password.php?error=Invalid or expired token.");
        exit();
    }
} else {
    header("Location: ../../login.html");
    exit();
}
?>
