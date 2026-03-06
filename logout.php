<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session cookie to ensure complete logout
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <style>
        body {
            background: #eff0f5;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .loader-container {
            text-align: center;
            background: white;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #f57224; /* Daraz Orange */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #424242; font-weight: 400; font-size: 18px; }
    </style>
</head>
<body>

    <div class="loader-container">
        <div class="spinner"></div>
        <h2>Logging you out safely...</h2>
    </div>

    <script type="text/javascript">
        // Using JavaScript for redirection as requested
        setTimeout(function() {
            window.location.href = "login.php";
        }, 1500); // Small delay to show the "Professional" logout effect
    </script>
</body>
</html>
