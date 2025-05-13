<?php
session_start();
include 'connectiondb/connection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Query to get the user data, including the session_id
    $stmt = $conn->prepare("SELECT id, password, role, session_id FROM registerlanding WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_id = $row['id'];
        $db_password = $row['password'];
        $db_role = trim($row['role']);
        $db_session_id = $row['session_id']; // Get current session ID stored in the database

        // Check if password is correct
        if (password_verify($password, $db_password)) {
            if ($db_role === "Super Admin") {
                // Generate a new session ID for the current session
                $session_id = session_id(); // Current session ID

                // If the user is already logged in from another tab, log them out
                if ($db_session_id && $db_session_id !== $session_id) {
                    // Invalidate the previous session by removing it from the database (optional)
                    $stmt = $conn->prepare("UPDATE registerlanding SET session_id = NULL WHERE session_id = ?");
                    $stmt->bind_param("s", $db_session_id);
                    $stmt->execute();
                }

                // Store email, role, and new session ID in session variables
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $db_role;
                $_SESSION['session_id'] = $session_id;  // Store current session ID

                // Update the session ID in the database
                $stmt = $conn->prepare("UPDATE registerlanding SET session_id = ? WHERE id = ?");
                $stmt->bind_param("si", $session_id, $db_id);
                $stmt->execute();

                // Redirect to the admin page after login
                header("Location: admin/usermng.php"); // Redirect to Super Admin page
                exit();
            } else {
                echo "<script>alert('Access Denied: Not a Super Admin!'); window.location.href='secretpageindex.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid Password!'); window.location.href='secretpageindex.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found!'); window.location.href='secretpageindex.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /*  Fullscreen Background */
        body {
            background: url('assets/img/lgupic.jpg') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* 🔹 Dark Overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10; /*  Mas mababa para di matakpan ang title at logo */
        }
    </style>
</head>
<body>

    <!-- 🔹 Main Container -->
    <div class="relative z-20 flex flex-col md:flex-row items-center bg-transparent bg-opacity-95 shadow-lg rounded-lg border border-gray-100 p-7 md:p-8 max-w-md w-full mx-2">
<!-- 🔹 Logo & Title (Centered) -->
<div class="relative z-20 flex flex-col items-center text-center">
    <img src="assets/img/logo.jpg" alt="LGU Logo" class="w-16 h-16 mb-2">
    <h4 class="text-white text-xs font-medium text-gray-600 uppercase tracking-wider">LGU</h4>
    <h1 class="text-white text-lg font-bold text-gray-800 uppercase tracking-wide">Super Admin</h1>
</div>

        <!-- 🔹 Login Form -->
        <div class="w-full">
            <form id="loginForm" action="secretpageindex.php" method="POST" class="w-full space-y-3">
                
                <div>
                    <label class="text-white text-gray-600 font-medium text-xs">Email:</label>
                    <input type="email" name="email" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100">
                </div>

                <div>
                    <label class=" text-white text-gray-600 font-medium text-xs">Password:</label>
                    <input type="password" name="password" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100">
                </div>

                <!--  Login Button with Spinner -->
                <button id="loginButton" type="submit"
                    class="w-full bg-blue-700 text-white py-2 rounded-lg font-medium text-sm flex justify-center items-center gap-2 hover:bg-blue-800 transition shadow-md">
                    <span id="btnText">Login</span>
                    <div id="loadingSpinner" class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
            </form>

        </div>
    </div>

    <!-- JavaScript for Button Loading Effect -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            let button = document.getElementById('loginButton');
            let text = document.getElementById('btnText');
            let spinner = document.getElementById('loadingSpinner');

            // Disable button and show spinner
            button.disabled = true;
            text.textContent = "Logging in...";
            spinner.classList.remove('hidden');
        });
    </script>

</body>
</html>

