<?php
session_start();
include 'connectiondb/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Validate token in the database
    $stmt = $conn->prepare("SELECT id, email, reset_token, reset_token_expiry FROM registerlanding WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['message'] = "Invalid or expired reset token.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    } 
    
    // Get the current time separately
    $current_time = date("Y-m-d H:i:s");

    // Check if token is expired
    if (strtotime($user['reset_token_expiry']) < strtotime($current_time)) {
        $_SESSION['message'] = "Token has expired. Please request a new password reset.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Update password and clear the token
    $stmt = $conn->prepare("UPDATE registerlanding SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "Password successfully updated! You can now log in.";
        header("Location: index.php"); // Redirects to login page
        exit();
    } else {
        $_SESSION['message'] = "Failed to update password. Please try again.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <title>Reset Password | LGU E-Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

    <!-- Container -->
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-lg border border-gray-200">
        
        <!-- Header with LGU Branding -->
        <div class="flex flex-col items-center mb-6">
            <img src="assets/img/logo.jpg" alt="LGU Logo" class="h-16 rounded-full border-4 border-yellow-400">
            <h2 class="text-xl font-bold text-blue-900 mt-2">LGU E-Services</h2>
            <p class="text-gray-600 text-sm">Secure Password Reset</p>
        </div>

        <!-- Notification Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-4 p-3 bg-blue-100 text-blue-700 rounded-md text-center">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Reset Password Form -->
        <form action="reset_password.php" method="post" class="space-y-4">
            <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">

            <!-- New Password -->
            <div class="relative">
                <label for="new_password" class="block text-gray-700 font-medium">New Password</label>
                <div class="relative">
                    <input type="password" id="new_password" name="new_password" required
                        class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-400 outline-none pr-10"
                        placeholder="Enter your new password">
                    
                    <!-- Toggle Password Visibility -->
                    <span id="togglePassword" class="absolute inset-y-0 right-3 flex items-center cursor-pointer">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-blue-900 text-white py-2 rounded-md hover:bg-yellow-500 hover:text-blue-900 transition font-semibold">
                Update Password
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center mt-4">
            <a href="index.php" class="text-blue-700 hover:underline text-sm">Back to Login</a>
        </div>
    </div>

    <!-- JavaScript for Password Toggle -->
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            let passwordField = document.getElementById("new_password");
            let eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.97 10.97 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.978 10.978 0 011.992-4.693M9.88 9.88a3 3 0 014.24 4.24m1.544-7.153A10.971 10.971 0 0112 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-1.071 0-2.107-.152-3.104-.436"/>';
            } else {
                passwordField.type = "password";
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        });
    </script>

</body>
</html>