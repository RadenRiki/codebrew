<?php
$host = "Localhost";
$user = "root";
$password = "";
$database = "codebrew_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn -> connect_error) {
    die("Connection failed: " . mysqli_connect_error());
}