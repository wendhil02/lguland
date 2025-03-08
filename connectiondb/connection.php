<?php
$servername = "localhost:3306";
$username = "root";
$password = "";
$database = "land";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
