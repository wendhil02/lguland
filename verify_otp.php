<?php
session_start();
include 'connectiondb/connection.php';

// ✅ Set correct timezone
date_default_timezone_set('Asia/Manila');

// ✅ Ensure session variables exist
if (!isset($_SESSION['email']) || !isset($_SESSION['session_token']) || !isset($_SESSION['id'])) {
    $_SESSION['error_message'] = "Session expired. Please log in again.";
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$session_token = $_SESSION['session_token']; 

// ✅ Debug: Check if session variables are set correctly
// echo "Email: $email | Session Token: $session_token";
// exit();

// ✅ Check if OTP is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $user_otp = trim($_POST['otp']);

    // ✅ Fetch OTP details from the database
    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM registerlanding WHERE email = ? AND session_token = ?");
    $stmt->bind_param("ss", $email, $session_token);
    $stmt->execute();
    $stmt->store_result();

    // ✅ If user is found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_otp, $otp_expiry);
        $stmt->fetch();

        // ✅ Get current time in correct format
        $current_time = date("Y-m-d H:i:s");
        $expiry_time = strtotime($otp_expiry);
        $current_timestamp = time();

        // ✅ Debug: Check OTP values
        // echo "Stored OTP: $stored_otp | User OTP: $user_otp | Expiry: $otp_expiry | Current Time: $current_time | Time Difference: " . ($expiry_time - $current_timestamp);
        // exit();

        if ($stored_otp === $user_otp && $expiry_time >= $current_timestamp) {
            $_SESSION['otp_verified'] = true;

            // ✅ Clear OTP after successful verification
            $clear_otp = $conn->prepare("UPDATE registerlanding SET otp = NULL, otp_expiry = NULL WHERE email = ?");
            $clear_otp->bind_param("s", $email);
            $clear_otp->execute();
            $clear_otp->close();

            // ✅ Redirect to main page
            header("Location: landingmainpage.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid or expired OTP.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid session or OTP. Please try again.";
    }

    $stmt->close();
    $conn->close();
}

//  Redirect back if verification failed
header("Location: otp_verification.php");
exit();
?>
