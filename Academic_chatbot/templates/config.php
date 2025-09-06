<?php
// config.php — DB connection (used by other PHP files)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";          // default XAMPP password is empty
$DB_NAME = "tutoring_db"; // change if you used a different DB name

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connect Error: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
