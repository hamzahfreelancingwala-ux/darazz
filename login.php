<?php
// 1. FORCE ERRORS TO SHOW (This stops the blank page and shows the error message)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. INCLUDE DATABASE
// Ensure db.php exists in the same folder!
require_once 'db.php';

session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Role Based Redirection using JavaScript
            if ($user['role'] == 'seller') {
                echo "<script>window.location.href='seller.php';</script>";
                exit;
            } else {
                echo "<script>window.location.href='index.php';</script>";
                exit;
            }
        } else {
            $error = "Incorrect email or password.";
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Daraz Clone</title>
    <style>
        body { 
            background: #eff0f5; 
            font-family: sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            width: 400px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
        }
        .logo-text {
            color: #f57224;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            display: block;
            text-decoration: none;
        }
        h2 { color: #424242; font-weight: 400; margin-bottom: 25px; font-size: 20px; }
        input { 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 15px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box; 
            outline: none;
        }
        input:focus { border-color: #f57224; }
        button { 
            width: 100%; 
            padding: 14px; 
            background: #f57224; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            text-transform: uppercase;
        }
        button:hover { background: #d35d1b; }
        .error-msg { 
            background: #fff0f0; 
            color: #d32f2f; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            font-size: 14px; 
            border: 1px solid #ffcdd2;
        }
        .footer-links { text-align: center; margin-top: 20px; font-size: 14px; color: #757575; }
        .footer-links a { color: #00a1ff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <a href="index.php" class="logo-text">daraz</a>
        <h2>Welcome! Please Login.</h2>
        
        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Phone Number or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">LOGIN</button>
        </form>
        
        <div class="footer-links">
            New member? <a href="signup.php">Register</a> here.
        </div>
    </div>
</body>
</html>
