<?php
session_start();
include 'connectiondb/connection.php';

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Remove session token from the database
    $update_stmt = $conn->prepare("UPDATE registerlanding SET session_token=NULL WHERE email=?");
    $update_stmt->bind_param("s", $email);
    $update_stmt->execute();
}

session_destroy();
header("Location: index.php");
exit();
?>