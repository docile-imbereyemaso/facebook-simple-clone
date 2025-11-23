<?php
session_start();
require_once "config/db.php";

// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user info from session
$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: index.php");
    exit();
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$full_name = $email = "";
$error = "";
$student_id = "";

// Get student ID from URL
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    
    // Fetch existing student data
    $stmt = $conn->prepare("SELECT id, full_name, email FROM student WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        $full_name = $student['full_name'];
        $email = $student['email'];
    } else {
        $error = "User not found";
        $student_id = ""; // Invalid ID
    }
    $stmt->close();
} else {
    $error = "No user ID provided";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
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
        // Check if email already exists for another user
        $check_stmt = $conn->prepare("SELECT id FROM student WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $student_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Email already exists for another user";
        } else {
            // Update student data
            $update_stmt = $conn->prepare("UPDATE student SET full_name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $full_name, $email, $student_id);
            
            if ($update_stmt->execute()) {
                // Set success message in session
                $_SESSION['success_message'] = "User '$full_name' has been successfully updated!";
                
                // Redirect to dashboard
                header("Location: crud.php");
                exit();
            } else {
                $error = "Error updating user: " . $conn->error;
            }
            
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Dashboard Clone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">

   <style>
    /* Center the main container */
.main {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 20px;
  width: 100%;
  min-height: calc(100vh - 100px); /* Adjust if you have a header/navbar */
  background-color: #f4f6f8;
}

/* Edit box styling */
.edit-box {
  background-color: #fff;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
}

/* Form title */
.edit-box h3 {
  text-align: center;
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #333;
}

/* Labels */
.edit-box label {
  display: block;
  font-weight: bold;
  margin-bottom: 6px;
  margin-top: 15px;
  color: #555;
  font-size: 14px;
}

/* Input fields */
.edit-box input[type="text"],
.edit-box input[type="email"] {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 5px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.edit-box input[type="text"]:focus,
.edit-box input[type="email"]:focus {
  border-color: #4CAF50;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
  outline: none;
}

/* Error messages */
.error {
  color: #e74c3c;
  font-size: 12px;
  display: none; /* can be toggled via JS */
}

.server-error {
  background-color: #f8d7da;
  color: #721c24;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 15px;
  border: 1px solid #f5c6cb;
  font-size: 14px;
  text-align: center;
}

/* Submit button */
.edit-box button[type="submit"] {
  width: 100%;
  padding: 12px;
  background-color: #4CAF50;
  color: #fff;
  font-size: 16px;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  margin-top: 20px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.edit-box button[type="submit"]:hover {
  background-color: #45a049;
  transform: translateY(-2px);
}

.edit-box button[type="submit"]:active {
  background-color: #3e8e41;
  transform: translateY(0);
}

/* Back link styling */
.edit-box .back {
  display: block;
  margin-top: 15px;
  text-align: center;
  color: #2196F3;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s;
}

.edit-box .back:hover {
  color: #0b7dda;
}

/* Responsive adjustments */
@media (max-width: 500px) {
  .edit-box {
    padding: 25px 20px;
  }
}

   </style>
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="nav-left">
            <i class="fab fa-facebook fb-logo"></i>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search Facebook">
            </div>
        </div>

        <div class="nav-middle">
            <div class="nav-icon active"><i class="fas fa-home"></i></div>
            <div class="nav-icon"><i class="fas fa-store"></i></div>
            <div class="nav-icon"><i class="fas fa-tv"></i></div>
            <div class="nav-icon"><i class="fas fa-users"></i></div>
            <div class="nav-icon"><i class="fas fa-gamepad"></i></div>
        </div>

        <div class="nav-right">
            <div class="circle-icon"><i class="fas fa-bars"></i></div>
            <div class="circle-icon"><i class="fab fa-facebook-messenger"></i></div>
            <div class="circle-icon">
                <i class="fas fa-bell"></i>
                <span style="position:absolute; top:10px; right:55px; background:#e41e3f; color:white; font-size:11px; padding:2px 5px; border-radius:50%;">1</span>
            </div>
            <div class="circle-icon">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" style="width:100%; height:100%; border-radius:50%;">
            </div>
            <div class="circle-icon">
                <a href="?logout=true" style="color: inherit; text-decoration: none;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- LEFT SIDEBAR -->
      <aside class="sidebar-left">
            <div class="sidebar-item">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=000&color=fff" alt="Profile">
                <span><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder" style="color: blue;"><i class="fas fa-circle-notch"></i></div>
                <span style="margin-left: 12px;">Meta AI</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder icon-friends"><i class="fas fa-user-friends"></i></div>
                <span style="margin-left: 12px;">Friends</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder" style="color: #1877f2;"><i class="fas fa-chart-bar"></i></div>
                <a style="margin-left: 12px;" href="./crud.php">Student Information System(CRUD)</a>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder icon-memories"><i class="fas fa-clock"></i></div>
                <span style="margin-left: 12px;">Memories</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder icon-saved"><i class="fas fa-bookmark"></i></div>
                <span style="margin-left: 12px;">Saved</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder icon-groups"><i class="fas fa-users-rectangle"></i></div>
                <span style="margin-left: 12px;">Groups</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder" style="color: #f02849;"><i class="fas fa-video"></i></div>
                <span style="margin-left: 12px;">Reels</span>
            </div>
            <div class="sidebar-item">
                <div class="icon-placeholder icon-market"><i class="fas fa-store"></i></div>
                <span style="margin-left: 12px;">Marketplace</span>
            </div>

            <!-- LOGOUT -->
            <div class="sidebar-item">
                <a href="?logout=true" style="display:flex; align-items:center; text-decoration:none; color:inherit;">
                    <div class="icon-placeholder" style="background:#e4e6eb; width:36px; height:36px; border-radius:50%; display:flex; justify-content:center; align-items:center;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span style="margin-left: 12px;">Logout</span>
                </a>
            </div>
        </aside>

        <!-- FEED -->
        <section class="feed">
        
<div class="main">
  <div class="edit-box">
    <h3>Edit User</h3>

    <!-- Display server-side error message -->
    <?php if (!empty($error)): ?>
      <div class="server-error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($student_id)): ?>
      <form id="editForm" action="" method="POST">
        <!-- Hidden field to store student ID -->
        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">

        <label>Full Names</label>
        <input type="text" name="full_name" id="fullName" value="<?php echo htmlspecialchars($full_name); ?>" required>
        

        <label>Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <span id="emailError" class="error">Please enter a valid email</span>

        <button type="submit">Update User</button>
      </form>
    <?php else: ?>
      <div class="server-error">Unable to load user data. Please check if the user exists.</div>
      <a class="back" href="dashboard.php">← Back to Dashboard</a>
    <?php endif; ?>
  </div>
</div>
             

            

            <!-- Add stories, posts, etc. -->
        </section>

        <!-- RIGHT SIDEBAR -->
        <aside class="sidebar-right">
            <!-- Right sidebar content -->
        </aside>

    </div>

    <!-- Floating edit button -->
    <div style="position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: #fff; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 20px;">
        <i class="fas fa-edit"></i>
    </div>

</body>
</html>
