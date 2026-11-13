<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "clothing_shop_db";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed. Please check db.php settings.");
}
$conn->set_charset("utf8mb4");
