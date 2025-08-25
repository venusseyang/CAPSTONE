<?php
// db.php
date_default_timezone_set('Asia/Manila');

$host = "localhost";
$user = "root";
$pass = "";         
$db   = "charging_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("DB Connection failed: " . $conn->connect_error);
}

 
$conn->query("
  UPDATE ports 
  SET status='available', user_id=NULL, start_time=NULL, end_time=NULL
  WHERE status='occupied' AND end_time <= NOW()
");
