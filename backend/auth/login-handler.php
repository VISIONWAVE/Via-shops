<?php
session_start();
include '../config/db.php'; // DB connection file

// Get inputs
$email = $_POST['email'];
$password = $_POST['password'];
$ip = $_SERVER['REMOTE_ADDR'];

// 1. CHECK FOR LOCKOUT
$time_limit = date("Y-m-d H:i:s", strtotime("-15 minutes"));
$checkAttempts = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempt_time > ?");
$checkAttempts->bind_param("sss", $email, $ip, $time_limit);
$checkAttempts->execute();
$checkAttempts->bind_result($attempt_count);
$checkAttempts->fetch();
$checkAttempts->close();

if ($attempt_count >= 5) {
    $_SESSION['error'] = "⚠️ Too many login attempts. Try again after 15 minutes.";
    header("Location: ../login.html");
    exit();
}

// 2. FETCH USER RECORD
$query = $conn->prepare("SELECT * FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 3. VERIFY PASSWORD
    if (password_verify($password, $user['password'])) {
        // ✅ SUCCESS: Clear past failed attempts
        $clear = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
        $clear->bind_param("s", $email);
        $clear->execute();

        // Set login session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        header("Location: ../dashboard.html");
        exit();
    }
}

// 4. IF LOGIN FAILS — Log the failed attempt
$log = $conn->prepare("INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, NOW())");
$log->bind_param("ss", $email, $ip);
$log->execute();

$remaining = 5 - ($attempt_count + 1);
$_SESSION['error'] = "❌ Invalid credentials. You have $remaining attempt(s) left.";
header("Location: ../login.html");
exit();
?>
