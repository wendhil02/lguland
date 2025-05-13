<?php
session_start();
include 'connectiondb/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ✅ Set timezone to match the database
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $new_otp = rand(100000, 999999);
    
    // ✅ Generate an expiry timestamp (5 minutes from now)
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // ✅ Update OTP in the database
    $stmt = $conn->prepare("UPDATE registerlanding SET otp = ?, otp_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $new_otp, $otp_expiry, $email);

    if ($stmt->execute()) {
        // ✅ Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'unifiedlgu@gmail.com';
            $mail->Password = 'kbyt zdmk khsd pcvt'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('unifiedlgu@gmail.com', 'LGU E-Services');
            $mail->addAddress($email);
            $mail->Subject = "Your New OTP Code";
            $mail->Body = "Your OTP code is: <b>" . $new_otp . "</b><br>This code will expire in 5 minutes.";
            $mail->isHTML(true);

            $mail->send();
            $_SESSION['success_message'] = "A new OTP has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Email error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error_message'] = "Failed to resend OTP.";
    }

    $stmt->close();
    header("Location: otp_verification.php?email=" . urlencode($email));
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: index.php");
    exit();
}


