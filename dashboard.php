<?php
session_start();
require_once "config/db.php";

// Enhanced cache control headers
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle logout - enhanced session destruction
if (isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login with cache prevention
    header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    header("Location: index.php");
    exit();
}

// Handle delete student
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Fetch students from database
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard</title>
<!-- Additional meta tags for cache control -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<style>
  * {margin:0; padding:0; box-sizing:border-box; font-family:Arial, sans-serif;}
  body {display:flex; background:#f5f5f5;}
  .sidebar {width:260px; background:#1c1e21; color:#fff; height:100vh; padding:20px 10px; position:fixed; top:0; left:0; overflow-y:auto;}
  .sidebar h2 {margin-bottom:20px; font-size:20px; padding-left:10px;}
  .menu-item {padding:10px; display:flex; align-items:center; gap:10px; cursor:pointer; border-radius:6px;}
  .menu-item:hover {background:#3a3b3c;}
  .main {margin-left:260px; padding:20px; width:100%;}
  a {color:#3b49df; cursor:pointer;}
  table {width:100%; border-collapse:collapse; background:#fff;}
  th, td {border:1px solid #ccc; padding:10px; font-size:14px;}
  th {background:#f0f0f0;}
  .alert {padding:10px; margin-bottom:15px; border-radius:6px; background:#d4edda; color:#155724; font-size:14px;}
  @media (max-width: 900px) {.sidebar {width:200px;} .main {margin-left:200px;}}
  @media (max-width: 600px) {
    .sidebar {position:absolute; width:200px; transform:translateX(-100%); transition:0.3s; z-index:10;}
    .sidebar.show {transform:translateX(0);}
    .toggle-btn {background:#1c1e21; color:white; padding:10px 15px; cursor:pointer; position:fixed; top:10px; left:10px; border-radius:6px;}
    .main {margin-left:0; padding-top:60px;}
  }
</style>
</head>
<body>

<div class="toggle-btn" onclick="toggleSidebar()">☰</div>

<div class="sidebar" id="sidebar">
  <h2>facebook Dashboard</h2>
  <div class="menu-item">🏠 Home</div>
  <div class="menu-item">👤 Profile</div>
  <div class="menu-item">📧 Messages</div>
  <div class="menu-item">⚙ Settings</div>
  <div class="menu-item"><a style="color:white; text-decoration:none;" href="?logout=1" onclick="clearCache()">🚪 Logout</a></div>
</div>

<div class="main">
  <div class="alert">Successfully logged in as <?= htmlspecialchars($_SESSION['username']); ?>!</div>

  <a id="addUserBtn" href="add-user.php">Add new user</a>

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
              <a href="edit-user.php?id=<?= $student['id'] ?>">Edit</a> 
              <a href="?delete_id=<?= $student['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("show");
}

// Clear browser cache when logging out
function clearCache() {
  if ('caches' in window) {
    caches.keys().then(function(names) {
      for (let name of names)
        caches.delete(name);
    });
  }
}

// Additional protection against back button
window.onpageshow = function(event) {
  if (event.persisted) {
    window.location.reload();
  }
};

// Prevent page from being cached
window.onbeforeunload = function() {};
</script>

</body>
</html>