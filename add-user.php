<?php
session_start();
require_once "config/db.php";

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$full_name = $email = "";
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    // Basic validation
    if (empty($full_name)) {
        $error = "Full name is required";
    } elseif (empty($email)) {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM student WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Email already exists in the database";
        } else {
            // Insert new student
            $insert_stmt = $conn->prepare("INSERT INTO student (full_name, email) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $full_name, $email);
            
            if ($insert_stmt->execute()) {
                // Set success message in session
                $_SESSION['success_message'] = "User '$full_name' has been successfully added!";
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error adding user: " . $conn->error;
            }
            
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Add New User</title>

<style>
  *{margin:0; padding:0; box-sizing:border-box; font-family:Arial, sans-serif;}
  body{display:flex; background:#f5f5f5;}

  /* Sidebar */
  .sidebar{
    width:260px;
    background:#1c1e21;
    color:white;
    height:100vh;
    padding:20px 10px;
    position:fixed;
    top:0;
    left:0;
    overflow-y:auto;
  }
  .sidebar h2 {margin-bottom:20px; font-size:20px; padding-left:10px;}
  .menu-item{
    padding:10px;
    display:flex;
    align-items:center;
    gap:10px;
    cursor:pointer;
    border-radius:6px;
  }
  .menu-item:hover{background:#3a3b3c;}

  /* Main */
  .main{
    margin-left:260px;
    padding:25px;
    width:100%;
  }

  .form-box{
    background:white;
    padding:25px;
    max-width:500px;
    margin:auto;
    border-radius:10px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
  }

  h3 {margin-bottom:15px;}

  label{display:block; margin-top:12px; font-weight:bold;}
  input{
    width:100%;
    padding:10px;
    margin-top:5px;
    border:1px solid #ccc;
    border-radius:6px;
  }

  .error{
    color:red;
    font-size:13px;
    margin-top:3px;
  }

  .server-error {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    border: 1px solid #f5c6cb;
  }

  button{
    margin-top:20px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#1877f2;
    color:white;
    font-size:16px;
    cursor:pointer;
  }

  a.back{
    display:block;
    margin-top:15px;
    text-align:center;
    color:#3b49df;
    font-size:14px;
  }

  /* Mobile responsive */
  @media(max-width:600px){
    .sidebar{
      position:absolute;
      width:200px;
      transform:translateX(-100%);
      transition:0.3s;
      z-index:10;
    }
    .sidebar.show{transform:translateX(0);}
    .toggle-btn{
      background:#1c1e21;
      color:white;
      padding:10px 15px;
      cursor:pointer;
      position:fixed;
      top:10px;
      left:10px;
      border-radius:6px;
    }
    .main{margin-left:0; padding-top:60px;}
  }
</style>
</head>

<body>

<div class="toggle-btn" onclick="toggleSidebar()">☰</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <h2>facebook Dashboard</h2>
  <div class="menu-item">🏠 Home</div>
  <div class="menu-item">👤 Profile</div>
  <div class="menu-item">📧 Messages</div>
  <div class="menu-item">⚙ Settings</div>
  <div class="menu-item"><a style="color:white; text-decoration:none;" href="dashboard.php?logout=1">🚪 Logout</a></div>
</div>

<!-- MAIN CONTENT -->
<div class="main">
  <div class="form-box">
    <h3>Add New User</h3>

    <!-- Display server-side error message -->
    <?php if (!empty($error)): ?>
      <div class="server-error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form id="addForm" action="add-user.php" method="POST">
      <label>Full Names</label>
      <input type="text" name="full_name" id="fullName" value="<?php echo htmlspecialchars($full_name); ?>" required>
      <span id="nameError" class="error">Please enter full name</span>

      <label>Email</label>
      <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
      <span id="emailError" class="error">Please enter a valid email</span>

      <button type="submit">Add User</button>
    </form>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("show");
}

// Client-side validation
document.getElementById("addForm").addEventListener("submit", function(e){
  const fullName = document.getElementById("fullName");
  const email = document.getElementById("email");
  
  let valid = true;

  // Reset error displays
  document.getElementById("nameError").style.display = "none";
  document.getElementById("emailError").style.display = "none";

  if (!fullName.value.trim()) {
    document.getElementById("nameError").style.display = "block";
    valid = false;
  }

  if (!email.value.trim()) {
    document.getElementById("emailError").style.display = "block";
    valid = false;
  } else if (!isValidEmail(email.value)) {
    document.getElementById("emailError").textContent = "Please enter a valid email";
    document.getElementById("emailError").style.display = "block";
    valid = false;
  }

  if (!valid) e.preventDefault();
});

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}
</script>

</body>
</html>