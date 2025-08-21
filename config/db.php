<?php
// اتصال به دیتابیس
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "morghdari";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("❌ اتصال به دیتابیس برقرار نشد: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
