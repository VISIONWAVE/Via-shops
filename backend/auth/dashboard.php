<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.html");
    exit();
}

require_once('../config/db.php');

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'User';
$email = $_SESSION['email'] ?? '';

// 🛒 Cart & wishlist session
$wishlist = $_SESSION['wishlist'] ?? [];
$cart = $_SESSION['cart'] ?? [];
$wishlistCount = count($wishlist);
$cartQty = array_sum($cart);

// Auto-create `orders` table
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'pending',
    total DECIMAL(10,2) DEFAULT 0.00
)");

// Fetch total orders
$totalOrders = 0;
$resultTotal = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE user_id = $user_id");
if ($resultTotal && $row = $resultTotal->fetch_assoc()) {
    $totalOrders = $row['total'];
}

// Fetch 3 recent orders
$recentOrders = [];
$resultRecent = $conn->query("SELECT id, created_at, status, total FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 3");
if ($resultRecent) {
    while ($row = $resultRecent->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Fetch monthly order stats
$monthlyOrders = array_fill(1, 6, 0);
$resultMonthly = $conn->query("SELECT MONTH(created_at) AS month, COUNT(*) AS count FROM orders WHERE user_id = $user_id GROUP BY MONTH(created_at)");
if ($resultMonthly) {
    while ($row = $resultMonthly->fetch_assoc()) {
        $month = (int)$row['month'];
        if ($month >= 1 && $month <= 6) {
            $monthlyOrders[$month] = (int)$row['count'];
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Dashboard | VisionWave</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="shortcut icon" href="../../assets/images/favicon.ico" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #121212;
      color: #fff;
    }
    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      width: 250px;
      background-color: #1e1e1e;
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.2);
    }
    .sidebar h3 {
      color: #03a9f4;
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      color: #bbb;
      padding: 10px 0;
      text-decoration: none;
    }
    .sidebar a:hover {
      color: #03a9f4;
    }
    .main {
      flex-grow: 1;
      padding: 40px;
      background-color: #181818;
    }
    .main h2 {
      margin-bottom: 10px;
    }
    .card {
      background: #252525;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px #111;
    }
    .card h3 {
      color: #03a9f4;
    }
    table {
      width: 100%;
      background: #2a2a2a;
      border-collapse: collapse;
      color: #fff;
    }
    th, td {
      padding: 10px;
      border: 1px solid #444;
      text-align: left;
    }
    th {
      background: #03a9f4;
      color: #000;
    }
    canvas {
      background: #fff;
      border-radius: 10px;
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <div class="sidebar">
    <h3>👤 Dashboard</h3>
    <a href="../../products.html">🛍 Products</a>
    <a href="../../wishlist.html">💖 Wishlist (<?= $wishlistCount ?>)</a>
    <a href="../../cart.html">🛒 Cart (<?= $cartQty ?>)</a>
    <a href="../../order-history.html">📦 Order History</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main">
    <h2>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h2>
    <p><?= htmlspecialchars($email) ?></p>

    <div class="card">
      <h3>Total Orders: <?= $totalOrders ?></h3>
    </div>

    <div class="card">
      <h3>Recent Orders</h3>
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="4">No recent orders found.</td></tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td><?= $order['id'] ?></td>
                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                <td><?= ucfirst($order['status']) ?></td>
                <td><?= number_format($order['total'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Monthly Orders Chart</h3>
      <canvas id="orderChart" height="200"></canvas>
    </div>
  </div>
</div>

<script>
const ctx = document.getElementById('orderChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
      label: 'Orders',
      data: <?= json_encode(array_values($monthlyOrders)) ?>,
      backgroundColor: '#03a9f4'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
      title: {
        display: true,
        text: 'Monthly Order Summary',
        color: '#fff'
      }
    },
    scales: {
      x: { ticks: { color: '#fff' }, grid: { color: '#333' }},
      y: { ticks: { color: '#fff' }, grid: { color: '#333' }}
    }
  }
});
</script>

</body>
</html>
