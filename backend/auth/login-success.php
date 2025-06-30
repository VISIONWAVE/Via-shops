<?php
session_start();
require_once '../config/db.php';

$showPage = false;
$loginFailed = false;
$loginError = "";
$full_name = "";

// === Settings ===
$logFile = "../logs/login_errors.log";
$adminEmail = "visionwave0@gmail.com";
$sendEmail = true;
$maxAttempts = 3;
$lockoutTime = 15 * 60; // 15 minutes

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $time = date("Y-m-d H:i:s");

    if (empty($email) || empty($password)) {
        $loginFailed = true;
        $loginError = "Email and password are required.";
    } else {
        // Get last attempt info
        $stmt = $conn->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($attempts, $last_attempt);
        $stmt->fetch();

        $lockedOut = false;
        if ($stmt->num_rows === 1 && $attempts >= $maxAttempts) {
            $elapsed = time() - strtotime($last_attempt);
            if ($elapsed < $lockoutTime) {
                $lockedOut = true;
                $loginFailed = true;
                $remaining = ceil(($lockoutTime - $elapsed) / 60);
                $loginError = "Too many failed attempts. Please try again in $remaining minute(s).";

                // Email alert on lockout
                if ($sendEmail) {
                    $subject = "🔒 User Lockout Alert";
                    $body = "A user has been locked out:\n\nTime: $time\nEmail: $email\nIP: $ip\nAttempts: $attempts\n";
                    @mail($adminEmail, $subject, $body);
                }
            } else {
                // Reset lockout
                $stmt = $conn->prepare("UPDATE login_attempts SET attempts = 0 WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }
        }

        if (!$lockedOut) {
            // Proceed with login check
            $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $full_name, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['full_name'] = $full_name;
                    $showPage = true;

                    // Reset login attempts
                    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                } else {
                    $loginFailed = true;
                    $loginError = "Incorrect password. Please try again.";
                }
            } else {
                $loginFailed = true;
                $loginError = "No user found with that email.";
            }

            // If failed login, update attempts
            if ($loginFailed) {
                $stmt = $conn->prepare("INSERT INTO login_attempts (email, attempts, last_attempt) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }
        }
    }

    // Log failure
    if ($loginFailed) {
        $message = "[{$time}] LOGIN FAILED from IP {$ip} using Email: {$email} — Reason: {$loginError}\n";
        file_put_contents($logFile, $message, FILE_APPEND);
    }
} else {
    header("Location: ../../login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
       <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <title><?php echo $showPage ? "Login Successful" : "Login Failed"; ?></title>
    <?php if ($showPage): ?>
        <meta http-equiv="refresh" content="2;url=dashboard.php">
    <?php endif; ?>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .message-box {
            background: #1e1e1e;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 255, 255, 0.15);
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: fadeIn 1s ease-in-out;
        }

        h2 {
            color: <?php echo $showPage ? '#03a9f4' : '#ff4c4c'; ?>;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .emoji {
            font-size: 40px;
            margin-bottom: 15px;
        }

        a {
            color: #03a9f4;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="message-box">
        <?php if ($showPage): ?>
            <div class="emoji">✅</div>
            <h2>Login successful!</h2>
            <p>Welcome, <strong><?php echo htmlspecialchars($full_name); ?></strong>!</p>
            <p>Redirecting to your dashboard...</p>
        <?php else: ?>
            <div class="emoji">❌</div>
            <h2>Login Failed</h2>
            <p><?php echo htmlspecialchars($loginError); ?></p>
            <p><a href="../../login.html">Back to Login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
