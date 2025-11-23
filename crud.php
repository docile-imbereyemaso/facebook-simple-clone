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
// delete single student
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: crud.php");
    exit();
}
// Fetch students (optional if you want dashboard CRUD)
$students = [];
$result = $conn->query("SELECT * FROM student ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
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
        /* General table styling */
table {
  width: 100%;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
  margin: 20px 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}

/* Table header */
thead {
  background-color: #4CAF50;
  color: white;
  text-align: left;
}

thead th {
  padding: 12px 15px;
  font-size: 16px;
}

/* Table body */
tbody td {
  padding: 12px 15px;
  border-bottom: 1px solid #ddd;
}

/* Alternating row colors */
tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

/* Hover effect */
tbody tr:hover {
  background-color: #f1f1f1;
  cursor: pointer;
}

/* Action links */
td a {
  text-decoration: none;
  margin-right: 10px;
  color: #2196F3;
  font-weight: 500;
  transition: color 0.3s;
}

td a:hover {
  color: #0b7dda;
}

/* Center text for empty row */
tbody tr td[colspan] {
  text-align: center;
  font-style: italic;
  color: #888;
}
/* Add User Button Styling */
#addUserBtn {
  display: inline-block;
  width: fit-content;
  padding: 10px 20px;
  background-color: #4CAF50; /* Green */
  color: white;
  font-size: 16px;
  font-weight: bold;
  text-decoration: none;
  border-radius: 5px;
  transition: background-color 0.3s, transform 0.2s;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

#addUserBtn:hover {
  background-color: #45a049; /* Slightly darker green */
  transform: translateY(-2px);
}

#addUserBtn:active {
  background-color: #3e8e41;
  transform: translateY(0);
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
                <span style="margin-left: 12px;">Student Information System(CRUD)</span>
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
            <a id="addUserBtn" href="crud-add.php">Add new user</a>
              <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Full Names</th>
        <th>Email</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if(count($students) > 0): ?>
        <?php foreach($students as $index => $student): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($student['full_name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td>
              <a href="crud-edit.php?id=<?= $student['id'] ?>">Edit</a> 
              <a href="?delete_id=<?= $student['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

            

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
