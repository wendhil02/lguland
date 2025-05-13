<?php
$servername = "localhost";
$username = "smar_bpasmart";
$password = "DbOKjutmNG1c073D";
$database = "smar_land";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
