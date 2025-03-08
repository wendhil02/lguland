<?php
session_start();
include 'connectiondb/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

 // PHPMailer autoload

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $new_otp = rand(100000, 999999);

    // Update OTP in the database
    $stmt = $conn->prepare("UPDATE registerlanding SET otp = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ?");
    $stmt->bind_param("ss", $new_otp, $email);

    if ($stmt->execute()) {
        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gamitin ang SMTP provider (Gmail, Outlook, etc.)
            $mail->SMTPAuth = true;
            $mail->Username = 'wendhil10@gmail.com'; // Palitan ng email mo
            $mail->Password = 'qfcz ekjf bfte zptm'; // Gumamit ng App Password kung Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', 'Your Website Name');
            $mail->addAddress($email);
            $mail->Subject = "Your New OTP Code";
            $mail->Body = "Your OTP code is: " . $new_otp . "\nThis code will expire in 5 minutes.";

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

