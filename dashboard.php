<?php include("db.php"); ?>
<?php date_default_timezone_set("Asia/Manila"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Solar Charging Dashboard</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg,#a5b4fc,#f9a8d4,#fcd34d);
    margin: 0;
    padding: 30px;
  }
  h1 {
    text-align: center;
    color: #1f2937;
    font-size: 34px;
    margin: 10px 0 25px;
    letter-spacing: 1px;
  }
  .nav {
    text-align: center;
    margin-bottom: 20px;
  }
  .nav a {
    color: #2563eb;
    background: #fff;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin: 0 6px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: 0.3s;
  }
  .nav a:hover {
    background: #2563eb;
    color: #fff;
    transform: translateY(-2px);
  }

  /* Cards */
  .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 20px;
    margin: 20px auto 30px;
    max-width: 1100px;
  }
  .card {
    background: #fff;
    border-radius: 16px;
    padding: 22px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: 0.3s;
  }
  .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
  }
  .card h3 {
    margin: 0 0 10px;
    color: #6b7280;
    font-weight: 600;
  }
  .value {
    font-size: 34px;
    font-weight: 800;
    color: #2563eb;
  }
  .status-ok {
    display:inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    background: #d1fae5;
    color: #065f46;
    font-weight: 700;
  }
  .status-warn {
    display:inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-weight: 700;
  }

  /* Table */
  table {
    width: 95%;
    max-width: 1100px;
    margin: 0 auto;
    background: #fff;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 22px rgba(0,0,0,0.08);
  }
  th, td {
    padding: 16px;
    text-align: left;
  }
  th {
    background: #2563eb;
    color: #fff;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  tr:nth-child(even) { background:#f9fafb; }
  tr:nth-child(odd) { background:#fff; }
  tr:hover { background:#e0e7ff; transition:.3s; }
  .countdown { font-weight:700; color:#111827; }
</style>
</head>
<body>
  <h1>⚡ Solar Charging Station Dashboard ⚡</h1>
  <div class="nav">
    <a href="rfid_login.php">🔑 RFID Auth</a>
    <a href="crud_users.php">👥 Manage Users</a>
  </div>

<?php
// auto-release expired sessions
$conn->query("
  UPDATE ports 
  SET status='available', user_id=NULL, start_time=NULL, end_time=NULL
  WHERE status='occupied' AND end_time <= NOW()
");

$totalUsers = (int)$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$active     = (int)$conn->query("SELECT COUNT(*) c FROM ports WHERE status='occupied'")->fetch_assoc()['c'];
$available  = 3 - $active;
$statusTxt  = $available>0 ? "Operational" : "All Ports Busy";
$statClass  = $available>0 ? "status-ok" : "status-warn";
?>

  <div class="cards">
    <div class="card"><h3>Total Registered Users</h3><div class="value"><?php echo $totalUsers; ?></div></div>
    <div class="card"><h3>Active Charging Sessions</h3><div class="value"><?php echo $active; ?></div></div>
    <div class="card"><h3>Available Ports</h3><div class="value"><?php echo $available; ?>/3</div></div>
    <div class="card"><h3>System Status</h3><div class="<?php echo $statClass; ?>"><?php echo $statusTxt; ?></div></div>
  </div>

  <table>
    <thead><tr><th>Port</th><th>User</th><th>RFID UID</th><th>Start Time</th><th>Ends</th><th>Time Left</th></tr></thead>
    <tbody>
<?php
$res = $conn->query("
  SELECT p.port_id, p.start_time, p.end_time, u.name, u.rfid_uid
  FROM ports p
  LEFT JOIN users u ON u.user_id = p.user_id
  WHERE p.status='occupied'
  ORDER BY p.port_id ASC
");
$rows = [];
while($r=$res->fetch_assoc()){ $rows[]=$r; }

if(!$rows){
  echo "<tr><td colspan='6' style='text-align:center;color:#6b7280;font-weight:600'>No active sessions.</td></tr>";
} else {
  foreach($rows as $r){
    $endTs   = strtotime($r['end_time']);
    $leftSec = max(0, $endTs - time());
    echo "<tr>
      <td>Port {$r['port_id']}</td>
      <td>{$r['name']}</td>
      <td>{$r['rfid_uid']}</td>
      <td>".date('M d, Y h:i:s A', strtotime($r['start_time']))."</td>
      <td>".date('M d, Y h:i:s A', $endTs)."</td>
      <td class='countdown' data-left='{$leftSec}' id='cd{$r['port_id']}'>--:--</td>
    </tr>";
  }
}
?>
    </tbody>
  </table>

<script>
function fmt(sec){
  const m = Math.floor(sec/60), s = sec%60;
  return (m<10?'0':'')+m+':' + (s<10?'0':'')+s;
}
function tick(){
  document.querySelectorAll('.countdown').forEach(el=>{
    let left = parseInt(el.getAttribute('data-left'),10);
    if (left>0){ left--; el.setAttribute('data-left', left); el.textContent = fmt(left); }
    else { el.textContent = '00:00'; }
  });
}
tick(); setInterval(tick,1000);
</script>
</body>
</html>
