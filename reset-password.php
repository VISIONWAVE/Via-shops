<!-- reset-password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password | VisionWave</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
</head>
<body class="dark-mode">

  <div class="login-container neumorphic-card">
    <h2>Reset Your Password</h2>

    <!-- Display error or success messages -->
    <?php if (isset($_GET['error'])): ?>
      <div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php elseif (isset($_GET['success'])): ?>
      <div class="success-message"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="backend/auth/process-reset-password.php" method="POST" class="neumorphic-form">
      <!-- Hidden token from email link -->
      <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>" />

      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" name="password" required placeholder="Enter new password" />
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" required placeholder="Re-enter new password" />
      </div>

      <button type="submit" class="btn-primary">Reset Password</button>
      <p class="form-footer"><a href="login.html">← Back to Login</a></p>
    </form>
  </div>

  <script src="assets/js/darkmode.js"></script>
</body>
</html>
