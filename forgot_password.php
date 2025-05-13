<?php 
session_start();
include 'connectiondb/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | LGU E-Services</title>
    
    <!-- Icons & Tailwind CSS -->
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: url('assets/img/lgupic.jpg') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>

<body class="relative flex items-center justify-center min-h-screen p-4">
    
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-65"></div>

    <!-- Forgot Password Card -->
    <div class="relative z-10 w-full max-w-md bg-white bg-opacity-90 p-6 rounded-lg shadow-lg backdrop-blur-md">
        
        <!-- LGU Logo -->
        <div class="text-center mb-3">
            <img src="assets/img/logo.jpg" alt="LGU Logo" class="w-16 h-16 mx-auto">
        </div>

        <h2 class="text-2xl font-semibold text-center text-blue-700 mb-2">Forgot Password</h2>
        <p class="text-gray-700 text-sm text-center mb-4">
            Enter your email to receive a password reset code.
        </p>
        
        <form action="send_reset_code.php" method="post" class="space-y-4">
            <div class="relative">
                <input type="email" name="email" id="email" 
                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Email Address" required>
            </div>
            
            <button type="submit" name="send_code"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                Send Reset Code
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="index.php" class="text-blue-600 text-sm hover:underline">Back to Login</a>
        </div>
    </div>

</body>
</html>
