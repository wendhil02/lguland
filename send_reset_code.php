<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();
include 'connectiondb/connection.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM registerlanding WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(50)); // Secure token
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // 1-hour expiry
        
        $stmt = $conn->prepare("UPDATE registerlanding SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Send reset link via PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP Server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'unifiedlgu@gmail.com'; // Your Gmail
                $mail->Password   = 'kbyt zdmk khsd pcvt'; // App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Sender & Recipient
                $mail->setFrom('your-email@gmail.com', 'LGU System');
                $mail->addAddress($email);

                // Email Content
               $base_url = "https://smartbarangayconnect.com/";  // Define the base URL manually
$reset_link = $base_url . "reset_password.php?token=" . urlencode($token);


                $mail->isHTML(true);
                $mail->Subject = "LGU - Password Reset Request";
                $mail->Body    = "<p>Dear Citizen,</p>
                                  <p>You requested a password reset. Click the link below to proceed:</p>
                                  <p><a href='$reset_link' style='color: blue; font-weight: bold;'>Reset Password</a></p>
                                  <p>If you did not request this, please ignore this email.</p>
                                  <p>Regards,<br>LGU Support Team</p>";

                if ($mail->send()) {
                    echo '<div class="fixed inset-0 flex items-center justify-center z-50">
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-6 rounded-md shadow-lg flex items-center gap-4 max-w-lg mx-auto" role="alert">
                                <img src="assets/img/logo.jpg" class="w-10 h-10">
                                <div class="text-center">
                                    <p class="font-bold text-lg">Notice from LGU:</p>
                                    <p>A password reset link has been sent to your registered email. Please check your inbox.</p>
                                </div>
                            </div>
                          </div>';
                } else {
                    echo '<div class="fixed inset-0 flex items-center justify-center z-50">
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-md shadow-lg flex items-center gap-4 max-w-lg mx-auto" role="alert">
                                <img src="assets/img/logo.jpg" class="w-10 h-10">
                                <div class="text-center">
                                    <p class="font-bold text-lg">LGU Notification:</p>
                                    <p>There was an issue sending the reset email. Please try again later.</p>
                                </div>
                            </div>
                          </div>';
                }
            } catch (Exception $e) {
                echo '<div class="fixed inset-0 flex items-center justify-center z-50">
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-md shadow-lg flex items-center gap-4 max-w-lg mx-auto" role="alert">
                            <img src="assets/img/logo.jpg" class="w-10 h-10">
                            <div class="text-center">
                                <p class="font-bold text-lg">LGU Notification:</p>
                                <p>Failed to send email due to a system error. Please contact support.</p>
                            </div>
                        </div>
                      </div>';
            }
        } else {
            echo '<div class="fixed inset-0 flex items-center justify-center z-50">
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-md shadow-lg flex items-center gap-4 max-w-lg mx-auto" role="alert">
                        <img src="assets/img/logo.jpg" class="w-10 h-10">
                        <div class="text-center">
                            <p class="font-bold text-lg">LGU Advisory:</p>
                            <p>We encountered an issue saving your reset request. Please try again.</p>
                        </div>
                    </div>
                  </div>';
        }
    } else {
        echo '<div class="fixed inset-0 flex items-center justify-center z-50">
                <div class="bg-gray-100 border-l-4 border-gray-500 text-gray-700 p-6 rounded-md shadow-lg flex items-center gap-4 max-w-lg mx-auto" role="alert">
                    <img src="assets/img/logo.jpg" class="w-10 h-10">
                    <div class="text-center">
                        <p class="font-bold text-lg">LGU Information:</p>
                        <p>The email you entered is not found in our records. Please check and try again.</p>
                    </div>
                </div>
              </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <title>LGU password reset</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen w-screen bg-cover bg-center relative" style="background-image: url('assets/img/lgupic.jpg');">
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
<script>
    setTimeout(function() {
        window.location.href = "index.php";
    }, 5000); // 5000 milliseconds = 5 seconds
</script>

</body>
</html>
