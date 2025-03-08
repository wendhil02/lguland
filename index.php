<?php
session_start();
include 'connectiondb/connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Check if user is already logged in
if (isset($_SESSION['email']) && isset($_SESSION['session_token'])) {
    $email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT session_token FROM registerlanding WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_session_token);
    $stmt->fetch();
    $stmt->close();

    if ($db_session_token !== $_SESSION['session_token']) {
        session_unset();
        session_destroy();
        echo "<script>alert('You have been logged out due to a new login. Please log in again.'); window.location.href='index.php';</script>";
        exit();
    }

    header("Location: landingmainpage.php");
    exit();
}

// ✅ CSRF Token (Security)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Process Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format!";
        header("Location: index.php");
        exit();
    }

    // ✅ Check If User Exists and Get Role
    $stmt = $conn->prepare("SELECT id, first_name, last_name, password, session_token, role FROM registerlanding WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (!empty($row['session_token'])) {
            echo "<script>alert('You are already logged in on another tab! Please logout first.'); window.location.href='index.php';</script>";
            exit();
        }

        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id'];
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $row['role']; // ✅ Store user role

            // ✅ Generate session token
            $session_token = bin2hex(random_bytes(32));
            $_SESSION['session_token'] = $session_token;

            $update_stmt = $conn->prepare("UPDATE registerlanding SET session_token=? WHERE email=?");
            $update_stmt->bind_param("ss", $session_token, $email);
            $update_stmt->execute();

            // ✅ Generate OTP
            $otp = rand(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            // ✅ Save OTP in Database
            $update_stmt = $conn->prepare("UPDATE registerlanding SET otp=?, otp_expiry=? WHERE email=?");
            $update_stmt->bind_param("sss", $otp, $otp_expiry, $email);
            $update_stmt->execute();

            // ✅ Send OTP via Email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'wendhil10@gmail.com'; // Replace with your email
                $mail->Password = 'qfcz ekjf bfte zptm'; // Use App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your-email@gmail.com', 'Your Website');
                $mail->addAddress($email);

                $mail->Subject = "Your Login OTP Code";
                $mail->Body = "Your OTP Code is: <b>" . $otp . "</b>";
                $mail->isHTML(true);

                if ($mail->send()) {
                    echo "<script>alert('OTP code sent to your Gmail. Please check your Spam folder if not received.'); window.location.href='otp_verification.php';</script>";
                    exit();
                } else {
                    $_SESSION['error_message'] = "OTP email not sent.";
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error sending OTP: " . $mail->ErrorInfo;
            }

            // ✅ Redirect Based on Role AFTER OTP verification
            $_SESSION['redirect_after_otp'] = ($row['role'] === 'Super Admin') ? 'admin/usermng.php' : (($row['role'] === 'Admin') ? 'admin/servicesadmin.php' : 'landingmainpage.php');
        } else {
            $_SESSION['error_message'] = "Wrong password!";
        }
    } else {
        $_SESSION['error_message'] = "User not found!";
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Icons & Tailwind CSS -->
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: url('assets/img/lgupic.jpg') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>

<body class="relative flex flex-col justify-center items-center  p-4 min-h-screen ">

    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-65"></div>

    <!-- Logo & Title -->
    <div class="relative z-10 text-center mb-4">
        <img src="assets/img/logo.jpg" alt="LGU Logo" class="w-20 h-20 mx-auto mb-2">
        <h3 class="text-2xl font-bold text-white">Welcome to</h3>
        <h1 class="text-2xl  p-1  font-bold text-white">LGU E-SERVICES</h1>
    </div>


    <!-- Login Form -->
    <section class="relative z-10 bg-white bg-opacity-90 shadow-lg rounded-lg p-4 w-full max-w-sm backdrop-blur-md">
        <header class="text-xl font-semibold text-center mb-3 text-blue-700">Login</header>


        <?php if (isset($_SESSION['lockout_remaining']) && $_SESSION['lockout_remaining'] > 0): ?>
            <div id="lockoutMessage" class="text-red-500 text-sm text-center mt-2">
                Too many failed attempts! Try again in <span id="countdown"><?php echo $_SESSION['lockout_remaining']; ?></span> seconds.
            </div>
            <?php unset($_SESSION['lockout_remaining']); ?>
        <?php endif; ?>


        <form action="index.php" method="post" onsubmit="return validateForm()">

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- ✅ Error Message Here -->
            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<p class="text-red-500 text-sm text-center mb-3">' . $_SESSION['error_message'] . '</p>';
                unset($_SESSION['error_message']);
            }
            ?>

            <div class="mb-3">
                <input type="email" name="email" id="email" placeholder="Email" class="w-full p-1.5 text-sm border rounded-md">
                <p id="emailError" class="text-red-500 text-xs mt-1 hidden">Please enter a valid email.</p>
            </div>

            <div class="mb-3 relative">
                <input type="password" name="password" id="password" placeholder="Password" class="w-full p-1.5 text-sm border rounded-md">
                <i id="togglePassword" class='bx bx-hide absolute right-2 top-2.5 text-xs cursor-pointer'></i>
                <p id="passwordError" class="text-red-500 text-xs mt-1 hidden">Please enter your password.</p>
            </div>

            <div class="text-right mb-3">
                <a href="forgot_password.php" class="text-blue-500 text-xs hover:underline">Forgot Password?</a>
            </div>

            <button id="loginButton" type="submit" class="w-full bg-blue-500 text-white py-2 text-sm rounded-md hover:bg-blue-600 flex justify-center items-center">
                <span id="spinner" class="hidden mr-2">
                    <i class='bx bx-loader-circle animate-spin'></i>
                </span>
                Login
            </button>
        </form>


        <div class="text-center mt-3">
            <span class="text-xs">Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Sign Up</a></span>
        </div>
        <p class="text-center text-red-600 text-xs mt-3">&copy; 2025 LGU E-Services. All rights reserved.</p>
    </section>


    <!-- Error Modal -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white p-4 rounded-lg shadow-lg text-center w-64 backdrop-blur-md">
                <h3 class="text-sm font-semibold mb-2 text-red-600">Error</h3>
                <p class="text-xs mb-3"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                <button onclick="closeModal()" class="bg-red-500 text-white px-3 py-1 text-xs rounded-md hover:bg-red-600">Close</button>
            </div>
        </div>

        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- JavaScript for UI Enhancements -->
    <script>
        function validateForm() {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const emailError = document.getElementById("emailError");
            const passwordError = document.getElementById("passwordError");
            let isValid = true;

            // Email validation
            if (email === "" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                emailError.classList.remove("hidden");
                isValid = false;
            } else {
                emailError.classList.add("hidden");
            }

            // Password validation
            if (password === "" || password.length < 6) {
                passwordError.classList.remove("hidden");
                isValid = false;
            } else {
                passwordError.classList.add("hidden");
            }

            // Show spinner if valid
            if (isValid) {
                document.getElementById("spinner").classList.remove("hidden");
                document.getElementById("loginButton").disabled = true;
            }

            return isValid;
        }

        function startCountdown() {
            let countdownElement = document.getElementById("countdown");
            if (!countdownElement) return;

            let timeLeft = parseInt(countdownElement.innerText);
            let interval = setInterval(() => {
                if (timeLeft <= 1) {
                    clearInterval(interval);
                    location.reload(); // Refresh page when countdown ends
                } else {
                    timeLeft--;
                    countdownElement.innerText = timeLeft;
                }
            }, 1000);
        }

        window.onload = startCountdown;

        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }
        document.addEventListener("click", function(event) {
            if (event.target.id === "errorModal") closeModal();
        });

        function showLoading() {
            let loginButton = document.getElementById("loginButton");
            let spinner = document.getElementById("spinner");

            loginButton.disabled = true;
            spinner.classList.remove("hidden");
        }

        // Toggle password visibility
        document.getElementById("togglePassword").addEventListener("click", function() {
            let passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                this.classList.replace("bx-hide", "bx-show");
            } else {
                passwordInput.type = "password";
                this.classList.replace("bx-show", "bx-hide");
            }
        });
    </script>

</body>

</html>