<?php
include 'db.php';
session_start();

// Check if logged in
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders | Daraz Clone</title>
    <style>
        :root { --daraz-orange: #f57224; --bg-gray: #eff0f5; }
        body { font-family: 'Roboto', sans-serif; background: var(--bg-gray); margin: 0; }
        header { background: #fff; padding: 15px 50px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .logo { font-size: 28px; font-weight: bold; color: var(--daraz-orange); text-decoration: none; }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; }
        .order-card { background: #fff; margin-bottom: 20px; border-radius: 4px; padding: 20px; border-left: 5px solid var(--daraz-orange); }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-Pending { background: #fff3e0; color: #ff9800; }
        .status-Shipped { background: #e3f2fd; color: #2196f3; }
        .status-Delivered { background: #e8f5e9; color: #4caf50; }
        
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .order-id { font-weight: bold; color: #212121; }
        .order-date { color: #757575; font-size: 14px; }
        .btn-logout { color: #757575; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">Daraz</a>
    <div>
        <a href="index.php" style="text-decoration:none; color:#333; margin-right:20px;">Home</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</header>

<div class="container">
    <h2>My Orders</h2>
    <?php if(count($orders) > 0): ?>
        <?php foreach($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-id">Order #<?php echo $order['id']; ?></span><br>
                        <span class="order-date">Placed on: <?php echo $order['created_at']; ?></span>
                    </div>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
                <div class="order-details">
                    <p>Total Amount: <strong>Rs. <?php echo number_get($order['total_amount']); ?></strong></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align:center; background:#fff; padding:50px;">
            <p>You haven't placed any orders yet.</p>
            <button onclick="window.location.href='index.php'" style="background:var(--daraz-orange); color:white; border:none; padding:10px 20px; cursor:pointer;">START SHOPPING</button>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
