<?php
// backend/auth/process-forgot-password.php

require_once '../config/db.php';
require_once '../lib/PHPMailer/PHPMailer.php';
require_once '../lib/PHPMailer/SMTP.php';
require_once '../lib/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $time = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $full_name);
        $stmt->fetch();

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour

        // Save reset info
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at)
                                  VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
        $insert->bind_param("sss", $email, $token, $expires);
        $insert->execute();

        // Reset link
        $link = "http://localhost:8000/reset-password.html?token=$token";

        // PHPMailer Email Setup
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'visionwave0563@gmail.com'; // ✅ your email
            $mail->Password = 'flqf wosi zskf ikzc';     // ✅ your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('visionwave0563@gmail.com', 'VisionWave');
            $mail->addAddress($email, $full_name);
            $mail->Subject = "🔐 Reset Your Password - VisionWave";
            $mail->Body = "Hello $full_name,\n\nWe received a request to reset your password.\n\nReset link: $link\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.\n\nTime: $time\nIP: $ip";

            $mail->send();

            header("Location: ../../forgot-password.html?status=sent");
            exit();

        } catch (Exception $e) {
            header("Location: ../../forgot-password.html?error=Email failed to send.");
            exit();
        }

    } else {
        header("Location: ../../forgot-password.html?error=Email not found.");
        exit();
    }

} else {
    header("Location: ../../login.html");
    exit();
}
