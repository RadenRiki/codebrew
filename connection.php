<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "codebrew_db";

$conn = mysqli_connect($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>