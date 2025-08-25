<?php include("db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RFID Login Simulation</title>
  <style>
    /* Background gradient */
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #6366f1, #f472b6, #facc15);
      margin: 0;
      padding: 40px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
    }

    /* Card container */
    .container {
      width: 95%;
      max-width: 700px;
      background: rgba(255,255,255,0.9);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      backdrop-filter: blur(8px);
      animation: fadeIn 0.6s ease-in-out;
    }

    h1 {
      text-align: center;
      color: #1f2937;
      font-size: 28px;
      margin-bottom: 10px;
    }
    p {
      text-align: center;
      color: #374151;
      margin-bottom: 25px;
    }

    /* Form */
    form {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }
    input {
      flex: 1;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      outline: none;
      font-size: 14px;
      transition: 0.3s;
    }
    input:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.3);
    }
    button {
      padding: 12px 20px;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      background: linear-gradient(45deg, #2563eb, #3b82f6);
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(37,99,235,0.4);
    }

    /* Log area */
    .log {
      background: #0f172a;
      color: #d1fae5;
      padding: 20px;
      border-radius: 12px;
      font-size: 14px;
      line-height: 1.6;
      min-height: 150px;
      font-family: "Courier New", monospace;
      overflow-y: auto;
    }
    .granted { color: #22c55e; font-weight: bold; }
    .denied { color: #ef4444; font-weight: bold; }

    /* Fade-in animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔐 RFID Login Simulation</h1>
    <p>Enter a RFID UID</p>

    <form method="POST">
      <input type="text" name="rfid_uid" placeholder="Enter RFID UID (e.g., DEADBEEF)" required>
      <button type="submit" name="simulate">Simulate Scan</button>
    </form>

    <div class="log">
      <?php
      // ✅ Set Philippine timezone
      date_default_timezone_set('Asia/Manila');

      if (isset($_POST['simulate'])) {
          $rfid_uid = $_POST['rfid_uid'];

          // Check RFID in database
          $result = $conn->query("SELECT * FROM users WHERE rfid_uid='$rfid_uid'");
          if ($result->num_rows > 0) {
              $user = $result->fetch_assoc();
              echo "[Time: " . date('h:i:s A') . "] Card detected<br>";
              echo "UID: <b>{$rfid_uid}</b><br>";
              echo "Tag matched (USER: {$user['name']}) -> <span class='granted'>ACCESS GRANTED ✅</span><br>";
              echo "Charging port enabled...";
          } else {
              echo "[Time: " . date('h:i:s A') . "] Card detected<br>";
              echo "UID: <b>{$rfid_uid}</b><br>";
              echo "<span class='denied'>ACCESS DENIED ❌ (Unknown Tag)</span>";
          }
      } else {
          echo "[Waiting for RFID scan...]";
      }
      ?>
    </div>
  </div>
</body>
</html>
