<?php include("db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Users & RFID</title>
  <style>
    /* Background */
    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: linear-gradient(135deg, #a5b4fc, #f9a8d4, #fcd34d);
      margin: 0;
      padding: 40px;
      display: flex;
      justify-content: center;
    }

    /* Table container */
    .table-container {
      width: 95%;
      max-width: 1000px;
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      animation: fadeIn 0.6s ease-in-out;
    }

    h1 {
      text-align: center;
      padding: 20px;
      margin: 0;
      background: linear-gradient(45deg, #6366f1, #3b82f6);
      color: #fff;
      font-size: 26px;
    }

    /* Form inside table */
    form {
      display: contents;
    }

    input {
      padding: 8px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      outline: none;
      width: 95%;
    }
    input:focus {
      border-color: #6366f1;
      box-shadow: 0 0 5px rgba(99,102,241,0.4);
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 14px;
      text-align: center;
    }

    th {
      background: #4f46e5;
      color: #fff;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    tr:nth-child(even) { background: #f9fafb; }
    tr:nth-child(odd) { background: #ffffff; }
    tr:hover { background: #e0e7ff; transition: 0.3s; }

    /* Buttons */
    button {
      padding: 8px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      color: #fff;
      transition: 0.3s;
    }
    .add { background: linear-gradient(45deg, #10b981, #34d399); }
    .add:hover { box-shadow: 0 4px 12px rgba(16,185,129,0.5); }

    .del { background: linear-gradient(45deg, #ef4444, #f87171); }
    .del:hover { box-shadow: 0 4px 12px rgba(239,68,68,0.5); }

    /* Messages */
    .message {
      padding: 12px;
      margin: 15px;
      border-radius: 8px;
      font-weight: bold;
      text-align: center;
    }
    .success { background: #d1fae5; color: #065f46; }
    .error { background: #fee2e2; color: #991b1b; }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="table-container">
    <h1>🌟 Manage Users & RFID 🌟</h1>

    <?php
    // ADD
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $rfid_uid = $_POST['rfid_uid'];

        // Check duplicate
        $check = $conn->query("SELECT * FROM users WHERE email='$email' OR rfid_uid='$rfid_uid'");
        if ($check->num_rows > 0) {
            echo "<div class='message error'>⚠️ Email or RFID already exists.</div>";
        } else {
            $sql = "INSERT INTO users (name, email, rfid_uid) VALUES ('$name', '$email', '$rfid_uid')";
            if ($conn->query($sql)) {
                echo "<div class='message success'>✅ User added successfully!</div>";
            } else {
                echo "<div class='message error'>❌ Error: " . $conn->error . "</div>";
            }
        }
    }

    // DELETE
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $conn->query("DELETE FROM users WHERE user_id=$id");
        header("Location: crud_users.php");
        exit();
    }
    ?>

    <table>
      <thead>
        <tr>
          <th>User ID</th><th>Name</th><th>Email</th><th>RFID UID</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Existing users -->
        <?php
        $result = $conn->query("SELECT * FROM users");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
              <td>{$row['user_id']}</td>
              <td>{$row['name']}</td>
              <td>{$row['email']}</td>
              <td>{$row['rfid_uid']}</td>
              <td>
                <a href='crud_users.php?delete={$row['user_id']}'><button class='del'>🗑 Delete</button></a>
              </td>
            </tr>";
        }
        ?>
        <!-- Add new row -->
        <tr>
          <form method="POST" action="">
            <td>Auto</td>
            <td><input type="text" name="name" required></td>
            <td><input type="email" name="email" required></td>
            <td><input type="text" name="rfid_uid" required></td>
            <td><button type="submit" name="add" class="add">➕ Add</button></td>
          </form>
        </tr>
      </tbody>
    </table>
  </div>
</body>
</html>
