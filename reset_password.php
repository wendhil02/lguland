<?php
session_start();
include 'connectiondb/connection.php';

// ✅ Function to check if a password is weak
function is_weak_password($password) {
    $password = strtolower($password); // Convert to lowercase for case-insensitive check
    $weak_passwords_file = "passwords/weakpassword.txt"; // Path to weak passwords list

    if (file_exists($weak_passwords_file)) {
        $weak_passwords = file($weak_passwords_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($password, $weak_passwords)) {
            return true; // Password is weak
        }
    }
    return false; // Password is strong
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    // ✅ Password strength validation
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        $_SESSION['message'] = "❌ Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // ✅ Check for weak password
    if (is_weak_password($new_password)) {
        $_SESSION['message'] = "❌ Your password is too weak. Please choose a stronger password!";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Validate token in the database
    $stmt = $conn->prepare("SELECT id, email, reset_token, reset_token_expiry FROM registerlanding WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['message'] = "❌ Invalid or expired reset token.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    } 
    
    // Check if token is expired
    if (strtotime($user['reset_token_expiry']) < time()) {
        $_SESSION['message'] = "⏳ Token has expired. Please request a new password reset.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Update password and clear the token
    $stmt = $conn->prepare("UPDATE registerlanding SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "✅ Password successfully updated! You can now log in.";
        header("Location: index.php"); // Redirects to login page with success message
        exit();
    } else {
        $_SESSION['message'] = "❌ Failed to update password. Please try again.";
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
<body class="flex items-center justify-center min-h-screen h-screen w-screen bg-cover bg-center relative" style="background-image: url('assets/img/lgupic.jpg');">
    
    <!-- Background Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50 z-0"></div>

    <!-- Container -->
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-lg border border-gray-200 relative z-10">
        <!-- ✅ Success Notification -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="successAlert" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-center shadow-md transition-opacity duration-1000">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
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
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5 text-black">
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
    
     // 🔔 Auto-hide success message after 5 seconds
    setTimeout(() => {
        let successAlert = document.getElementById("successAlert");
        if (successAlert) {
            successAlert.style.opacity = "0";
            setTimeout(() => successAlert.remove(), 1000); // Smooth fade-out
        }
    }, 5000);
    
    
    
    
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
