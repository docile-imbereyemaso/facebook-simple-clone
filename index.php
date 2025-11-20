<?php
session_start();
require_once "config/db.php";

$errorMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email)) {
        $errorMsg = "Please enter your email or phone number.";
    } elseif (empty($password)) {
        $errorMsg = "Please enter your password.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $errorMsg = "Incorrect password.";
            }
        } else {
            $errorMsg = "Email not found.";
        }

        $stmt->close();
    }
}

// Handle Google login redirect (simplified placeholder)
if (isset($_GET['google_login']) && $_GET['google_login'] == 1) {
    // In real implementation, you would use Google OAuth
    // For now, redirect to dashboard as a demo
    $_SESSION['user_id'] = 0; // example user_id for Google
    $_SESSION['username'] = "Google User";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Facebook Login</title>
<style>
    /* General styling same as your original CSS */
    body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
    .container { display: flex; justify-content: center; align-items: center; height: 100vh; padding: 20px; }
    .left-panel { flex: 1; padding: 40px; }
    .left-panel h1 { color: #1877f2; font-size: 55px; margin-bottom: 10px; }
    .left-panel p { font-size: 22px; color: #333; max-width: 400px; }
    .right-panel { flex: 1; max-width: 380px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.15); text-align: center; }
    .google-btn { width: 100%; padding: 12px; background: #b49592ff; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-bottom: 20px; }
    form input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; }
    .error { color: red; font-size: 13px; text-align: left; display: block; margin-bottom: 10px; }
    .login-btn { width: 100%; padding: 12px; background-color: #1877f2; color: white; border: none; border-radius: 6px; font-size: 18px; cursor: pointer; margin-top: 10px; }
    .forgot { display: block; margin: 10px 0; color: #1877f2; text-decoration: none; }
    .create-account { background-color: #42b72a; width: 100%; padding: 12px; display: inline-block; border-radius: 6px; color: white; text-decoration: none; font-size: 17px; margin-top: 10px; }
    .create-page { display: block; margin-top: 20px; color: #606770; font-size: 14px; }
    @media (max-width: 850px) { .container { flex-direction: column; text-align: center; } .left-panel { padding: 20px; } .right-panel { margin-top: 20px; width: 100%; } }
    @media (max-width: 480px) { .left-panel h1 { font-size: 40px; } .left-panel p { font-size: 18px; } }
</style>
</head>

<body>
<div class="container">
    <div class="left-panel">
        <h1>facebook</h1>
        <p>Connect with friends and the world around you on Facebook.</p>
    </div>

    <div class="right-panel">
        <!-- Google login -->
        <form method="GET">
            <button type="submit" name="google_login" value="1" class="google-btn">LOGIN WITH GOOGLE ACCOUNT</button>
        </form>

        <!-- Email login -->
        <?php if($errorMsg): ?>
            <div class="error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="text" name="email" placeholder="Email or phone number" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit" class="login-btn">Log In</button>
            <a href="#" class="forgot">Forgot password?</a>
        </form>

        <hr />
        <a class="create-account" href="./create-account.php">Create new account</a>
        <a href="#" class="create-page">Create a Page for a celebrity, brand or business.</a>
    </div>
</div>
</body>
</html>
