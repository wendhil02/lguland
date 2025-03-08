<?php
session_start();
include 'connectiondb/connection.php'; // Database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

$email = trim($_POST['email']);
$otp = trim($_POST['otp']);

error_log("User Email: " . $email);
error_log("User Entered OTP: " . $otp);

$stmt = $conn->prepare("SELECT id, first_name, last_name, otp, otp_expiry FROM registerlanding WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();

    // Debugging: Check stored OTP and expiry time
    error_log("Stored OTP in DB: " . $row['otp']);
    error_log("Current Time: " . date("Y-m-d H:i:s"));
    error_log("OTP Expiry Time: " . $row['otp_expiry']);
    error_log("Time Difference: " . (strtotime($row['otp_expiry']) - time()) . " seconds left");

    // Ensure OTP is integer and check expiry time
    if (intval(trim($row['otp'])) === intval(trim($otp)) && strtotime($row['otp_expiry']) > time()) {
        $_SESSION['id'] = $row['id'];
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $row['first_name']; // ✅ Siguraduhin may laman
        $_SESSION['last_name'] = $row['last_name'];   // ✅ Siguraduhin may laman
        $_SESSION['name'] = $row['first_name'] . " " . $row['last_name']; // Optional: Buong pangalan


        // ✅ Generate a new session token and store it in the database
        $_SESSION['session_token'] = bin2hex(random_bytes(32));

        $update_stmt = $conn->prepare("UPDATE registerlanding SET session_token=?, otp=NULL, otp_expiry=NULL WHERE email=?");
        $update_stmt->bind_param("ss", $_SESSION['session_token'], $email);
        $update_stmt->execute();
        $update_stmt->close();

        $_SESSION['success_message'] = "Login successful!";
        error_log("✅ OTP Verified! Redirecting to landingmainpage.php");

        header("Location: landingmainpage.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid or expired OTP!";
        error_log("❌ Invalid or expired OTP!");
    }
} else {
    $_SESSION['error_message'] = "User not found!";
    error_log("❌ User not found!");
}

// Redirect back to OTP verification page with email
header("Location: otp_verification.php?email=" . urlencode($email));
exit();
