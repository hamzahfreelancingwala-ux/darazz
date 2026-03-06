<?php 
include 'db.php'; 
session_start();

// --- 1. SEARCH & CATEGORY LOGIC ---
$search_query = "";
$display_title = "Just For You";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    // Logic for Search Bar
    $search_query = $_GET['search'];
    $display_title = "Search results for '" . htmlspecialchars($search_query) . "'";
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search_query%", "%$search_query%"]);
} elseif (isset($_GET['cat']) && !empty($_GET['cat'])) {
    // Logic for Sidebar Category Links
    $category = $_GET['cat'];
    $display_title = htmlspecialchars($category) . " Collection";
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    $stmt->execute([$category]);
} else {
    // Default View
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daraz | Best Online Shopping Store</title>
    <style>
        :root {
            --daraz-orange: #f57224;
            --daraz-bg: #eff0f5;
            --text-main: #212121;
            --text-light: #757575;
            --shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: var(--daraz-bg);
            color: var(--text-main);
        }

        /* --- Header --- */
        header {
            background-color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            padding: 12px 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
        }

        .logo {
            font-size: 32px;
            font-weight: 900;
            color: var(--daraz-orange);
            text-decoration: none;
        }

        .search-container {
            flex: 1;
            margin: 0 30px;
            display: flex;
        }

        .search-container input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            background: #f3f3f3;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        .search-container button {
            background-color: var(--daraz-orange);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-weight: bold;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-main);
            margin-left: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        /* --- Layout --- */
        .main-content {
            max-width: 1200px;
            margin: 20px auto;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 20px;
            padding: 0 15px;
        }

        /* --- Sidebar --- */
        .sidebar {
            background: #fff;
            padding: 15px 0;
            border-radius: 8px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .sidebar h3 {
            padding: 0 20px;
            font-size: 14px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #424242;
            font-size: 14px;
            transition: 0.2s;
        }

        .sidebar ul li a:hover {
            background-color: #f5f5f5;
            color: var(--daraz-orange);
        }

        /* --- Products --- */
        .section-title { font-size: 20px; margin-bottom: 20px; font-weight: 500; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: #fff;
            border-radius: 4px;
            overflow: hidden;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            border-color: #eee;
        }

        .product-image { width: 100%; height: 200px; object-fit: cover; }
        .product-info { padding: 12px; }
        .product-name { font-size: 14px; height: 38px; overflow: hidden; margin-bottom: 8px; }
        .product-price { color: var(--daraz-orange); font-size: 18px; font-weight: bold; }
        
        .btn-buy {
            width: 100%;
            background: var(--daraz-orange);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<header>
    <div class="nav-container">
        <a href="index.php" class="logo">daraz</a>
        
        <form class="search-container" action="index.php" method="GET">
            <input type="text" name="search" placeholder="Search in Daraz" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">SEARCH</button>
        </form>

        <div class="nav-menu">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">My Account</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Signup</a>
            <?php endif; ?>
            <a href="cart.php">🛒 Cart</a>
        </div>
    </div>
</header>

<div class="main-content">
    <aside class="sidebar">
        <h3>Categories</h3>
        <ul>
            <li><a href="index.php">All Products</a></li>
            <li><a href="index.php?cat=Electronics">Electronics</a></li>
            <li><a href="index.php?cat=Fashion">Fashion</a></li>
            <li><a href="index.php?cat=Home%20%26%20Kitchen">Home & Kitchen</a></li>
            <li><a href="index.php?cat=Beauty">Health & Beauty</a></li>
        </ul>
    </aside>

    <main>
        <h2 class="section-title"><?php echo $display_title; ?></h2>

        <div class="product-grid">
            <?php
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    ?>
                    <div class="product-card">
                        <img src="<?php echo $row['image_url']; ?>" class="product-image">
                        <div class="product-info">
                            <div class="product-name"><?php echo $row['name']; ?></div>
                            <div class="product-price">Rs. <?php echo number_format($row['price']); ?></div>
                            <button class="btn-buy" onclick="window.location.href='cart.php?add=<?php echo $row['id']; ?>'">ADD TO CART</button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='padding: 20px;'>No items found in this section.</p>";
            }
            ?>
        </div>
    </main>
</div>

</body>
</html>
