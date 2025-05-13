<?php
session_start();
include 'connectiondb/connection.php';

// ✅ Set timezone
date_default_timezone_set('Asia/Manila');

// ✅ Prevent Infinite Redirects
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    header("Location: landingmainpage.php");
    exit();
}

// ✅ Ensure session variables exist before continuing
if (!isset($_SESSION['email']) || !isset($_SESSION['session_token']) || !isset($_SESSION['id'])) {
    $_SESSION['error_message'] = "Session expired. Please log in again.";
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$session_token = $_SESSION['session_token'];

// ✅ Debug: Check if session token exists
if (empty($session_token)) {
    $_SESSION['error_message'] = "Session token missing. Please log in again.";
    header("Location: index.php");
    exit();
}

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

        // ✅ Debug: Display values before checking OTP
        echo "Stored OTP: " . $stored_otp . "<br>";
        echo "Entered OTP: " . $user_otp . "<br>";
        echo "Current Time: " . date("Y-m-d H:i:s") . "<br>";
        echo "OTP Expiry: " . $otp_expiry . "<br>";
        echo "Time Difference: " . (strtotime($otp_expiry) - time());
        exit();

        if ($stored_otp === $user_otp && strtotime($otp_expiry) >= time()) {
            $_SESSION['otp_verified'] = true;

            //  Clear OTP after successful verification
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

// ✅ If OTP fails, reload page
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
        <p class="text-sm text-gray-700">Enter the OTP sent to your email & Check your Spam.</p>

        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="text-red-500 text-sm mt-2"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <!-- OTP Input Form -->
        <form action="verify_otp.php" method="POST" class="mt-4">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
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

<script>
    //  Disable back button
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
</script>

</body>
</html>
