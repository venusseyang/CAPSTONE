<?php include("db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Users & RFID</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #60a5fa, #f472b6, #facc15);
      min-height: 100vh;
      margin: 0; padding: 0;
      display: flex; justify-content: center; align-items: flex-start;
    }
    .container {
      width: 95%; max-width: 1000px;
      background: rgba(255,255,255,0.95);
      border-radius: 16px;
      padding: 25px;
      margin: 40px auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    h1 {
      text-align: center; color:#1f2937; margin-bottom:20px;
    }
    form {
      display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;
    }
    form label {
      flex: 1 1 30%; display:flex; flex-direction: column;
      font-weight: 600; color:#374151;
    }
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
    .del { background: linear-gradient(45deg, #ef4444, #f87171); }
    .del:hover { transform:scale(1.05); }
    table {
      width:100%; border-collapse:collapse; border-radius:12px; overflow:hidden;
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
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
  if (isset($_POST['add'])) {
    $name = $_POST['name']; $email = $_POST['email']; $rfid_uid = $_POST['rfid_uid'];
    $check = $conn->query("SELECT * FROM users WHERE email='$email' OR rfid_uid='$rfid_uid'");
    if ($check->num_rows > 0) {
      echo "<div class='message error'>⚠️ Email or RFID already exists.</div>";
    } else {
      $sql = "INSERT INTO users (name,email,rfid_uid) VALUES ('$name','$email','$rfid_uid')";
      echo $conn->query($sql) ? "<div class='message success'>✅ User added!</div>" : "<div class='message error'>❌ Error: {$conn->error}</div>";
    }
  }
  if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id=$id");
    header("Location: crud_users.php"); exit();
  }
  ?>

  <table>
    <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>RFID UID</th><th>Actions</th></tr></thead>
    <tbody>
    <?php
      $result=$conn->query("SELECT * FROM users");
      while($row=$result->fetch_assoc()){
        echo "<tr>
          <td>{$row['user_id']}</td>
          <td>{$row['name']}</td>
          <td>{$row['email']}</td>
          <td>{$row['rfid_uid']}</td>
          <td><a href='crud_users.php?delete={$row['user_id']}'><button class='del'>Delete</button></a></td>
        </tr>";
      }
    ?>
    </tbody>
  </table>

  <div class="nav">
    <a href="rfid_login.php">➡ RFID Login</a> | 
    <a href="dashboard.php">📊 Dashboard</a>
  </div>
</div>
</body>
</html>
