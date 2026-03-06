<?php
// Enable error reporting to catch any hidden issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// 1. Add to Cart Logic
if (isset($_GET['add'])) {
    $pid = $_GET['add'];
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    $_SESSION['cart'][] = $pid;
    echo "<script>window.location.href='cart.php';</script>";
    exit;
}

// 2. Remove from Cart
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    }
    echo "<script>window.location.href='cart.php';</script>";
    exit;
}

// 3. SECURE CHECKOUT LOGIC
if (isset($_POST['checkout'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login to checkout'); window.location.href='login.php';</script>";
        exit;
    }
    
    $buyer_id = $_SESSION['user_id'];
    $total = $_POST['total_amount'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert order with explicit timestamp
        $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, total_amount, status, created_at) VALUES (?, ?, 'Pending', NOW())");
        $stmt->execute([$buyer_id, $total]);
        
        // Clear cart session
        unset($_SESSION['cart']); 
        
        $pdo->commit();
        echo "<script>alert('Order Placed Successfully!'); window.location.href='profile.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        // This will print the error clearly if Step 1 (the SQL ALTER TABLE) wasn't done
        die("<div style='padding:20px; background:#fff0f0; border:1px solid #d32f2f; color:#d32f2f; font-family:sans-serif;'>
                <strong>Checkout Error:</strong> " . $e->getMessage() . "
                <br><br><em>Tip: Make sure you ran the SQL command to fix the 'total_amount' column in phpMyAdmin.</em>
             </div>");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart | Daraz</title>
    <style>
        :root { --daraz-orange: #f57224; --bg: #eff0f5; }
        body { font-family: sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        .cart-container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 350px; gap: 20px; }
        .cart-list { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .summary { background: white; padding: 20px; border-radius: 8px; height: fit-content; position: sticky; top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 15px; font-weight: 400; color: #424242; }
        .item { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f1f1f1; }
        .item img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; margin-right: 20px; background: #f9f9f9; }
        .item-info { flex: 1; }
        .item-name { font-size: 16px; margin-bottom: 5px; color: #212121; }
        .item-price { color: var(--daraz-orange); font-weight: bold; font-size: 18px; }
        .btn-remove { color: #999; text-decoration: none; font-size: 12px; margin-top: 5px; display: inline-block; }
        .btn-remove:hover { color: #f57224; }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: #757575; }
        .total-price { font-size: 22px; color: var(--daraz-orange); font-weight: bold; }
        .btn-checkout { background: var(--daraz-orange); color: white; border: none; width: 100%; padding: 15px; border-radius: 4px; font-weight: bold; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-checkout:hover { background: #d35d1b; }
        .empty-msg { text-align: center; padding: 50px; color: #757575; font-size: 18px; }
    </style>
</head>
<body>

<div style="max-width:1000px; margin: 0 auto 20px;">
    <a href="index.php" style="color: var(--daraz-orange); text-decoration: none; font-weight: bold; font-size: 14px;">← CONTINUE SHOPPING</a>
</div>

<div class="cart-container">
    <div class="cart-list">
        <h2>Shopping Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?> items)</h2>
        
        <?php
        $grand_total = 0;
        if (!empty($_SESSION['cart'])) {
            // Prepared statement for the specific IDs in cart
            $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($_SESSION['cart']);
            
            $products_indexed = [];
            while($p = $stmt->fetch()) { $products_indexed[$p['id']] = $p; }

            foreach ($_SESSION['cart'] as $key => $id) {
                // Check if product still exists in DB
                if(isset($products_indexed[$id])) {
                    $item = $products_indexed[$id];
                    $grand_total += $item['price'];
                    ?>
                    <div class="item">
                        <img src="<?php echo $item['image_url']; ?>" onerror="this.src='https://via.placeholder.com/80'">
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">Rs. <?php echo number_format($item['price']); ?></div>
                            <a href="cart.php?remove=<?php echo $key; ?>" class="btn-remove">REMOVE ITEM</a>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            echo "<div class='empty-msg'>
                    <div style='font-size: 40px; margin-bottom:10px;'>🛒</div>
                    Your cart is empty.
                  </div>";
        }
        ?>
    </div>

    <div class="summary">
        <h2>Order Summary</h2>
        <div class="summary-row">
            <span>Subtotal</span>
            <span>Rs. <?php echo number_format($grand_total); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping Fee</span>
            <span style="color: #4caf50;">FREE</span>
        </div>
        <div class="summary-row" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
            <span style="font-weight: bold; color: #212121;">Total Amount</span>
            <span class="total-price">Rs. <?php echo number_format($grand_total); ?></span>
        </div>
        
        <form method="POST">
            <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
            <button type="submit" name="checkout" class="btn-checkout" <?php echo ($grand_total == 0) ? 'disabled style="background:#ccc; cursor:not-allowed;"' : ''; ?>>
                PROCEED TO CHECKOUT
            </button>
        </form>
    </div>
</div>

</body>
</html>
