<?php
// 1. Force error reporting so you see the actual error instead of a 500 page
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Security Check: Only sellers allowed
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$seller_id = $_SESSION['user_id'];

// --- HANDLE PRODUCT ADDITION ---
if(isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $desc = $_POST['desc'];
    $price = $_POST['price'];
    $cat = $_POST['cat'];
    
    // Create uploads folder if it doesn't exist
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { 
        mkdir($target_dir, 0777, true); 
    }
    
    // Check if file was actually uploaded
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        $file_name = time() . "_" . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (seller_id, name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$seller_id, $name, $desc, $price, $cat, $target_file]);
                echo "<script>alert('Product Published!'); window.location.href='seller.php';</script>";
                exit;
            } catch (PDOException $e) {
                die("Database Error: " . $e->getMessage());
            }
        } else {
            echo "<script>alert('Failed to move uploaded file.');</script>";
        }
    } else {
        echo "<script>alert('Please select a valid image file.');</script>";
    }
}

// --- HANDLE ORDER STATUS UPDATE ---
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    echo "<script>window.location.href='seller.php';</script>";
    exit;
}

// Fetch Seller's Data
$products = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY id DESC");
$products->execute([$seller_id]);

// Fetch All Orders for this Demo
$orders = $pdo->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.buyer_id = u.id ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Center | Daraz Clone</title>
    <style>
        :root { --daraz-orange: #f57224; --blue: #00a1ff; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eff0f5; margin: 0; padding: 20px; }
        .nav { background: white; padding: 15px 40px; margin-bottom: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h2 { color: #424242; margin-top: 0; border-bottom: 2px solid var(--daraz-orange); padding-bottom: 10px; font-size: 18px; }
        label { display: block; margin: 10px 0 5px; font-size: 13px; color: #666; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .btn-submit { background: var(--daraz-orange); color: white; border: none; padding: 15px; width: 100%; margin-top: 20px; cursor: pointer; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #fafafa; color: #757575; }
        .img-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
    </style>
</head>
<body>

<div class="nav">
    <div style="font-size: 24px; font-weight: bold; color: var(--daraz-orange);">daraz <span style="color:#333; font-weight:normal; font-size:18px;">Seller Center</span></div>
    <div>
        <a href="index.php" style="text-decoration:none; color: #666; margin-right: 20px; font-weight:500;">Visit Shop</a>
        <a href="logout.php" style="text-decoration:none; color: #ff4d4d; font-weight:bold;">Logout</a>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h2>List New Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="name" placeholder="e.g. Wireless Headset" required>
            
            <label>Category</label>
            <select name="cat">
                <option>Electronics</option>
                <option>Fashion</option>
                <option>Home & Kitchen</option>
                <option>Beauty</option>
            </select>
            
            <label>Price (Rs.)</label>
            <input type="number" name="price" placeholder="Price in PKR" required>
            
            <label>Upload Image</label>
            <input type="file" name="product_image" accept="image/*" required>
            
            <label>Description</label>
            <textarea name="desc" rows="3" placeholder="Write something about the item..."></textarea>
            
            <button type="submit" name="add_product" class="btn-submit">Publish Product</button>
        </form>
    </div>

    <div>
        <div class="card" style="margin-bottom: 20px;">
            <h2>Recent Orders</h2>
            <table>
                <tr><th>ID</th><th>Customer</th><th>Status</th><th>Update</th></tr>
                <?php while($o = $orders->fetch()): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo $o['full_name']; ?></td>
                    <td style="color:var(--daraz-orange); font-weight:bold;"><?php echo $o['status']; ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                            <select name="status" style="padding:2px; font-size:12px; width:auto;">
                                <option value="Pending">Pending</option>
                                <option value="Shipped">Shipped</option>
                                <option value="Delivered">Delivered</option>
                            </select>
                            <button type="submit" name="update_status" style="background:#00a1ff; color:white; border:none; padding:4px 8px; border-radius:3px; cursor:pointer;">Go</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card">
            <h2>Your Inventory</h2>
            <table>
                <tr><th>Preview</th><th>Name</th><th>Category</th><th>Price</th></tr>
                <?php while($p = $products->fetch()): ?>
                <tr>
                    <td><img src="<?php echo $p['image_url']; ?>" class="img-preview"></td>
                    <td><?php echo $p['name']; ?></td>
                    <td><?php echo $p['category']; ?></td>
                    <td>Rs. <?php echo number_format($p['price']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>
