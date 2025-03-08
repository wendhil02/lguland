<?php
session_start();
include 'connectiondb/connection.php'; // Database connection

// ✅ If the user is already logged in and OTP is verified, redirect them
if (isset($_SESSION['email']) && isset($_SESSION['session_token']) && isset($_SESSION['id']) && isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    header("Location: landingmainpage.php"); // Change this to your main page
    exit();
}

// ✅ Check if email parameter exists
if (!isset($_GET['email']) || empty($_GET['email'])) {
    $_SESSION['error_message'] = "Check your OTP code in spam or email within 1 minute.";
    header("Location: index.php");
    exit();
}

// ✅ Ensure the session is active
if (!isset($_SESSION['email']) || !isset($_SESSION['session_token']) || !isset($_SESSION['id'])) {
    header("Location: index.php"); // Redirect to login page
    exit();
}

// ✅ Set OTP verified
$_SESSION['otp_verified'] = true;

$email = urldecode($_GET['email']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification | LGU E-Services</title>
    
    <!-- Icons & Tailwind CSS -->
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        body {
            background: url('assets/img/lgupic.jpg') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>

<body class="relative flex flex-col justify-center items-center min-h-screen p-4">
    
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-65"></div>

    <!-- OTP Form Card -->
    <div class="relative z-10 bg-white bg-opacity-90 shadow-lg rounded-lg p-6 w-full max-w-md backdrop-blur-md text-center">
        
        <!-- LGU Logo -->
        <img src="assets/img/logo.jpg" alt="LGU Logo" class="w-16 h-16 mx-auto mb-2">
        
        <h2 class="text-xl font-semibold text-blue-700 mb-2">OTP Verification</h2>
        <p class="text-sm text-gray-700">Enter the OTP sent to your email</p>

        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="text-red-500 text-sm mt-2"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <!-- OTP Input Form -->
        <form action="verify_otp.php" method="POST" class="mt-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="text" name="otp" placeholder="Enter OTP" required class="w-full p-2 border border-gray-300 rounded-md text-center">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 mt-3 rounded-md hover:bg-blue-700 transition">
                Verify OTP
            </button>
        </form>

        <!-- Resend OTP -->
        <form action="resend_otp.php" method="POST" class="mt-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <button type="submit" class="text-blue-600 text-sm hover:underline">
                Resend OTP
            </button>
        </form>
    </div>

</body>
</html>


