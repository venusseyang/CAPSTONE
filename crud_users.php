<?php include("db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Users & RFID</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #60a5fa, #f472b6, #facc15);
      min-height: 100vh; margin: 0; padding: 0;
      display: flex; justify-content: center; align-items: flex-start;
    }
    .container {
      width: 95%; max-width: 1100px;
      background: rgba(255,255,255,0.95);
      border-radius: 16px; padding: 25px; margin: 40px auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    h1 { text-align: center; color:#1f2937; margin-bottom:20px; }
    form { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
    form label { flex: 1 1 30%; display:flex; flex-direction: column; font-weight: 600; color:#374151; }
    form input {
      padding:10px; border:1px solid #d1d5db; border-radius:8px;
      margin-top:5px; transition:0.3s;
    }
    form input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.3); }
    button {
      border:none; border-radius:8px; padding:10px 15px;
      cursor:pointer; font-weight:600; color:white; transition:0.3s;
    }
    .add { background: linear-gradient(45deg, #10b981, #34d399); }
    .add:hover { transform:scale(1.05); }
    .soft { background: linear-gradient(45deg, #f59e0b, #fbbf24); }
    .hard { background: linear-gradient(45deg, #ef4444, #f87171); }
    .restore { background: linear-gradient(45deg, #3b82f6, #2563eb); }
    .toggle { background: linear-gradient(45deg, #9333ea, #a855f7); margin-bottom:15px; }
    table {
      width:100%; border-collapse:collapse; border-radius:12px; overflow:hidden;
      box-shadow: 0 6px 12px rgba(0,0,0,0.1); margin-bottom:20px;
    }
    th, td { padding:14px; text-align:left; }
    th { background:#4f46e5; color:white; text-transform:uppercase; font-size:13px; }
    tr:nth-child(even) { background:#f9fafb; }
    tr:nth-child(odd) { background:#ffffff; }
    tr:hover { background:#e0e7ff; transition:0.3s; }
    .message { padding:12px; margin-bottom:15px; border-radius:8px; font-weight:bold; }
    .success { background:#d1fae5; color:#065f46; }
    .error { background:#fee2e2; color:#991b1b; }
    .nav { margin-top:20px; text-align:center; }
    .nav a { margin:0 10px; text-decoration:none; font-weight:600; color:#2563eb; }
    #archivedTable { display:none; }
  </style>
</head>
<body>
<div class="container">
  <h1>🌟 Manage Users & RFID 🌟</h1>

  <form method="POST" action="">
    <label>Name: <input type="text" name="name" required></label>
    <label>Email: <input type="email" name="email" required></label>
    <label>RFID UID: <input type="text" name="rfid_uid" required></label>
    <button type="submit" name="add" class="add">Add User</button>
  </form>

  <?php
  // ADD USER
  if (isset($_POST['add'])) {
    $name = $_POST['name']; $email = $_POST['email']; $rfid_uid = $_POST['rfid_uid'];
    $check = $conn->query("SELECT * FROM users WHERE (email='$email' OR rfid_uid='$rfid_uid') AND status='active'");
    if ($check->num_rows > 0) {
      echo "<div class='message error'>⚠️ Email or RFID already exists.</div>";
    } else {
      $sql = "INSERT INTO users (name,email,rfid_uid,status) VALUES ('$name','$email','$rfid_uid','active')";
      echo $conn->query($sql) ? "<div class='message success'>✅ User added!</div>" : "<div class='message error'>❌ Error: {$conn->error}</div>";
    }
  }

  //  ARCHIVED
  if (isset($_GET['soft_delete'])) {
    $id = (int)$_GET['soft_delete'];
    $conn->query("UPDATE users SET status='deleted' WHERE user_id=$id");
    header("Location: crud_users.php"); exit();
  }

  // RESTORE
  if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $conn->query("UPDATE users SET status='active' WHERE user_id=$id");
    header("Location: crud_users.php"); exit();
  }

  //  DELETE
  if (isset($_GET['hard_delete'])) {
    $id = (int)$_GET['hard_delete'];
    $conn->query("DELETE FROM users WHERE user_id=$id");
    header("Location: crud_users.php"); exit();
  }
  ?>

  <!-- Active Users -->
  <h2>Active Users</h2>
  <table>
    <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>RFID UID</th><th>Actions</th></tr></thead>
    <tbody>
    <?php
      $result=$conn->query("SELECT * FROM users WHERE status='active'");
      if($result->num_rows == 0){
        echo "<tr><td colspan='5' style='text-align:center'>No active users.</td></tr>";
      }
      while($row=$result->fetch_assoc()){
        echo "<tr>
          <td>{$row['user_id']}</td>
          <td>{$row['name']}</td>
          <td>{$row['email']}</td>
          <td>{$row['rfid_uid']}</td>
          <td>
            <a href='crud_users.php?soft_delete={$row['user_id']}'><button class='soft'> ARCHIVED</button></a>
            <a href='crud_users.php?hard_delete={$row['user_id']}' onclick=\"return confirm('Are you sure? This cannot be undone!')\"><button class='hard'>Delete</button></a>
          </td>
        </tr>";
      }
    ?>
    </tbody>
  </table>

  <!-- Archived Users Toggle -->
  <button class="toggle" onclick="toggleArchived()">Show Archived Users</button>

  <div id="archivedTable">
    <h2>Archived Users</h2>
    <table>
      <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>RFID UID</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
        $result=$conn->query("SELECT * FROM users WHERE status='deleted'");
        if($result->num_rows == 0){
          echo "<tr><td colspan='5' style='text-align:center'>No archived users.</td></tr>";
        }
        while($row=$result->fetch_assoc()){
          echo "<tr>
            <td>{$row['user_id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['rfid_uid']}</td>
            <td>
              <a href='crud_users.php?restore={$row['user_id']}'><button class='restore'>Restore</button></a>
              <a href='crud_users.php?hard_delete={$row['user_id']}' onclick=\"return confirm('Permanently delete this user?')\"><button class='hard'>Delete</button></a>
            </td>
          </tr>";
        }
      ?>
      </tbody>
    </table>
  </div>

  <div class="nav">
    <a href="rfid_login.php">➡ RFID Login</a> | 
    <a href="dashboard.php">📊 Dashboard</a>
  </div>
</div>

<script>
function toggleArchived(){
  var x=document.getElementById("archivedTable");
  if(x.style.display==="none" || x.style.display===""){
    x.style.display="block";
    event.target.textContent="Hide Archived Users";
  } else {
    x.style.display="none";
    event.target.textContent="Show Archived Users";
  }
}
</script>
</body>
</html>
