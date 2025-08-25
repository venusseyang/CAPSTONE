<?php include("db.php"); ?>
<?php date_default_timezone_set("Asia/Manila"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>RFID Login (Simulation)</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg,#a5b4fc,#f9a8d4,#fcd34d);
    margin: 0;
    padding: 40px;
    display: flex;
    justify-content: center;
    min-height: 100vh;
  }
  .card {
    width: 95%;
    max-width: 760px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(0,0,0,.25);
    padding: 28px;
    animation: fadeIn 0.6s ease-in-out;
  }
  @keyframes fadeIn { from {opacity:0; transform:translateY(15px);} to {opacity:1; transform:translateY(0);} }
  h1 {
    margin: 0 0 8px;
    color: #1f2937;
    text-align: center;
    font-size: 28px;
  }
  .sub {
    margin: 0 0 20px;
    text-align: center;
    color: #374151;
    font-size: 14px;
  }
  form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 20px;
  }
  input {
    flex: 1;
    min-width: 260px;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 14px;
  }
  input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.3);
    outline: none;
  }
  button {
    padding: 12px 18px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(45deg,#2563eb,#3b82f6);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
  }
  button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
  .log {
    background: #111827;
    color: #e5e7eb;
    padding: 16px;
    border-radius: 12px;
    min-height: 140px;
    font-family: Consolas, monospace;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 20px;
  }
  .ok { color: #22c55e; font-weight: 700; }
  .bad { color: #ef4444; font-weight: 700; }
  .nav { margin-top: 8px; text-align: center; }
  .nav a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
    margin: 0 8px;
    padding: 6px 12px;
    border-radius: 8px;
    background: #f3f4f6;
    transition: 0.3s;
  }
  .nav a:hover {
    background: #2563eb;
    color: #fff;
  }
</style>
</head>
<body>
<div class="card">
  <h1>🔐 RFID Authentication</h1>
  <p class="sub">Simulate a scan by entering an RFID UID. <br><b>PHT:</b> <?php echo date('M d, Y h:i:s A'); ?></p>

  <form method="POST">
    <input type="text" name="rfid_uid" placeholder="Enter RFID UID (e.g., DEADBEEF)" required>
    <button type="submit" name="simulate">Simulate Scan</button>
  </form>

  <div class="log">
<?php
// Release expired sessions
$conn->query("
  UPDATE ports 
  SET status='available', user_id=NULL, start_time=NULL, end_time=NULL
  WHERE status='occupied' AND end_time <= NOW()
");

if(isset($_POST['simulate'])){
  $rfid = strtoupper(trim($_POST['rfid_uid']));
  echo "[Time: ".date('h:i:s A')."] Card detected\n<br>UID: <b>{$rfid}</b><br>";

  $stmt = $conn->prepare("SELECT user_id,name FROM users WHERE rfid_uid=?");
  $stmt->bind_param("s",$rfid);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if(!$user){
    echo "<span class='bad'>ACCESS DENIED ❌ (Unknown RFID)</span>";
  } else {
    $check = $conn->prepare("SELECT port_id,end_time FROM ports WHERE user_id=? AND status='occupied' LIMIT 1");
    $check->bind_param("i",$user['user_id']);
    $check->execute();
    $active = $check->get_result()->fetch_assoc();

    if($active){
      echo "User: <b>{$user['name']}</b><br>";
      echo "<span class='ok'>Already charging</span> on Port {$active['port_id']}. Ends at <b>".date('h:i:s A', strtotime($active['end_time']))."</b>.";
    } else {
      $free = $conn->query("SELECT port_id FROM ports WHERE status='available' ORDER BY port_id ASC LIMIT 1")->fetch_assoc();
      if(!$free){
        echo "<span class='bad'>All 3 ports are busy. Please wait…</span>";
      } else {
        $pid = (int)$free['port_id'];
        $assign = $conn->prepare("UPDATE ports SET status='occupied', user_id=?, start_time=NOW(), end_time=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE port_id=?");
        $assign->bind_param("ii",$user['user_id'],$pid);
        if($assign->execute()){
          $conn->query("INSERT INTO logs(user_id,port_id,start_time,end_time)
                        SELECT {$user['user_id']}, {$pid}, start_time, end_time FROM ports WHERE port_id={$pid}");
          $times = $conn->query("SELECT start_time,end_time FROM ports WHERE port_id={$pid}")->fetch_assoc();
          echo "User: <b>{$user['name']}</b><br>";
          echo "<span class='ok'>ACCESS GRANTED ✅</span> → Assigned to <b>Port {$pid}</b><br>";
          echo "Charging until <b>".date('h:i:s A', strtotime($times['end_time']))."</b> (30 minutes).";
        } else {
          echo "<span class='bad'>Failed to assign port.</span>";
        }
      }
    }
  }
} else {
  echo "[Waiting for RFID scan…]";
}
?>
  </div>

  <div class="nav">
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="crud_users.php">👥 Manage Users</a>
  </div>
</div>
</body>
</html>
