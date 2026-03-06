<?php
// 1. FORCE ERRORS TO SHOW
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. INCLUDE DATABASE
require_once 'db.php';

session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Basic Validation
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if email already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $error = "This email is already registered.";
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $hashed_password, $role]);
                
                // Get the new ID and set session
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;

                // 3. ROLE-BASED REDIRECTION VIA JAVASCRIPT
                if ($role == 'seller') {
                    echo "<script>alert('Account Created! Welcome to Seller Center.'); window.location.href='seller.php';</script>";
                } else {
                    echo "<script>alert('Account Created! Welcome to Daraz.'); window.location.href='index.php';</script>";
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Daraz Clone</title>
    <style>
        :root { --daraz-orange: #f57224; }
        body { background: #eff0f5; font-family: 'Roboto', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .signup-container { background: white; width: 800px; display: flex; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .info-side { flex: 1; padding: 40px; background: #fff; display: flex; flex-direction: column; justify-content: center; }
        .form-side { flex: 1; padding: 40px; background: #fafafa; border-left: 1px solid #eee; }
        .logo-text { color: var(--daraz-orange); font-size: 30px; font-weight: bold; text-decoration: none; margin-bottom: 10px; display: block; }
        h2 { font-weight: 400; color: #424242; margin: 0 0 20px 0; }
        label { display: block; font-size: 13px; margin-bottom: 5px; color: #757575; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; outline: none; }
        input:focus { border-color: var(--daraz-orange); }
        .btn-signup { background: var(--daraz-orange); color: white; border: none; padding: 15px; width: 100%; cursor: pointer; font-size: 16px; border-radius: 4px; text-transform: uppercase; font-weight: bold; transition: 0.3s; }
        .btn-signup:hover { background: #d35d1b; }
        .error-box { background: #fff0f0; color: #d32f2f; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; border: 1px solid #ffcdd2; }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="info-side">
            <a href="index.php" class="logo-text">daraz</a>
            <h2>Create your account</h2>
            <p style="font-size:14px; color:#9e9e9e;">Join the biggest marketplace in the region.</p>
        </div>
        <div class="form-side">
            <?php if($error): ?>
                <div class="error-box"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="signup.php">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Enter your full name" required>
                
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
                
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimum 6 characters" required>
                
                <label>Select Your Role</label>
                <select name="role">
                    <option value="buyer">I want to Shop (Buyer)</option>
                    <option value="seller">I want to Sell (Seller)</option>
                </select>
                
                <button type="submit" class="btn-signup">SIGN UP</button>
            </form>
            <p style="text-align:center; font-size:13px; margin-top:15px;">
                Already a member? <a href="login.php" style="color:#00a1ff; text-decoration:none;">Login here</a>.
            </p>
        </div>
    </div>
</body>
</html>
