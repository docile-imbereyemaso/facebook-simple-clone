<?php
require_once "config/db.php";

$successMsg = "";
$errorMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Server-side validation
    if (empty($username)) {
        $errorMsg = "Please enter a username.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $errorMsg = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $errorMsg = "Password must be at least 6 characters.";
    } elseif ($password !== $confirmPassword) {
        $errorMsg = "Passwords do not match.";
    } else {
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            $successMsg = "Account created successfully!";
        } else {
            // Handle duplicate email
            if ($conn->errno == 1062) {
                $errorMsg = "Email already exists. Please use a different email.";
            } else {
                $errorMsg = "Error creating account: " . $conn->error;
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Facebook Signup Clone</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f0f2f5; display: flex; flex-direction: column; align-items: center; }
    .container { background: white; margin-top: 40px; width: 400px; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { margin: 0 0 5px; font-size: 24px; }
    p { margin: 0 0 20px; color: #606770; font-size: 14px; }
    input { width: 100%; padding: 12px; margin-bottom: 12px; border: 1px solid #ccd0d5; border-radius: 6px; font-size: 15px; }
    .btn { width: 100%; padding: 12px; background: #42b72a; color: white; border: none; border-radius: 6px; font-size: 18px; cursor: pointer; }
    .alert { padding: 12px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; text-align: center; }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
    .login-link a { color: #1877f2; text-decoration: none; font-weight: bold; }
    @media (max-width: 480px) { .container { width: 90%; padding: 20px; } }
  </style>
</head>
<body>

  <div class="container">
    <h2>Create a new account</h2>
    <p>It's quick and easy.</p>

    <!-- Display server-side messages -->
    <?php if($successMsg): ?>
      <div class="alert success"><?= $successMsg ?></div>
    <?php elseif($errorMsg): ?>
      <div class="alert error"><?= $errorMsg ?></div>
    <?php endif; ?>

    <form method="POST" id="signupForm">
      <input type="text" name="username" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password (min 6 characters)" required />
      <input type="password" name="confirmPassword" placeholder="Confirm Password" required />
      <button class="btn" type="submit">Sign Up</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="./index.php">Log In</a>
    </div>
  </div>

</body>
</html>
