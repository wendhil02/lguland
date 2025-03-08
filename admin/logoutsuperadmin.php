<?php
session_start();
include '../connectiondb/connection.php';
// Check if the user is a super admin before logging out
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Super Admin') {
    // Destroy all session data
    session_unset(); // Unsets all session variables
    session_destroy(); // Destroys the session

    // Redirect the super admin to the login page
    header("Location: ../admin/secretpageindex.php"); // Replace with the actual login page URL
    exit();
} else {
    // If not a super admin, redirect to an error page or login page
    header("Location: ../admin/secretpageindex.php");
    exit();
}
?>
