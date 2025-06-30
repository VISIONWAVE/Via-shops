<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", time() + 3600); // valid for 1 hour

    // Store token
    $stmt = $conn->prepare("REPLACE INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $token, $expires);
    $stmt->execute();

    // Generate reset link
    $link = "http://localhost:8000/reset-password.html?token=$token";

    // Send email (simple version)
    $subject = "🔐 Reset Your Password";
    $body = "Click the link below to reset your password:\n\n$link\n\nThis link expires in 1 hour.";
    $headers = "From: noreply@visionwave.com";

    if (mail($email, $subject, $body, $headers)) {
        echo "✅ Reset link sent to your email.";
    } else {
        echo "❌ Failed to send reset email.";
    }
}
?>
