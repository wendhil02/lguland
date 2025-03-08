<?php
session_start();
include("connectiondb/connection.php"); // Include database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // List of common weak passwords
    $weak_passwords = ["123456", "password", "123456789", "qwerty", "abc123", "12345678", "111111", "123123"];

    // Validate input fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
    } elseif (in_array($password, $weak_passwords)) {
        $_SESSION['error'] = "Your password is too weak. Please choose a stronger password!";
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long!";
    } else {
        // Check if email already exists
        $query = "SELECT * FROM registerlanding WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = "Email already exists. Try another!";
        } else {
            // Hash password before storing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into database
            $query = "INSERT INTO registerlanding (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! You can now log in.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Something went wrong. Please try again!";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <title>Register</title>
</head>

<body class="relative flex flex-col justify-center items-center  p-4 min-h-screen  bg-cover bg-center bg-fixed relative" 
    style="background-image: url('./assets/img/lgupic.jpg');">
    
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <div class="relative z-10 text-center mb-4">
        <img src="assets/img/logo.jpg" alt="LGU Logo" class="w-20 h-20 mx-auto mb-2">
        <h1 class="text-2xl font-bold text-white">LGU E-SERVICES</h1>
    </div>

    <section class="relative z-10 bg-white bg-opacity-90 p-6 rounded-lg shadow-lg w-11/12 max-w-md">

    
        <div class="text-center">
            <h1 class="text-2xl font-semibold text-gray-800 mb-4">Sign Up</h1>

            <!-- Error & Success Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="post" class="space-y-4" onsubmit="return validateForm()">
            <div>
                <input type="text" name="first_name" id="first_name" placeholder="First Name"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p id="fnameError" class="text-red-600 text-sm"></p>
            </div>

            <div>
                <input type="text" name="last_name" id="last_name" placeholder="Last Name"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p id="lnameError" class="text-red-600 text-sm"></p>
            </div>

            <div>
                <input type="email" name="email" id="email" placeholder="Email"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p id="emailError" class="text-red-600 text-sm"></p>
            </div>

            <div class="relative">
                <input type="password" name="password" id="password" placeholder="Password"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="bx bx-hide absolute right-3 top-3 text-gray-500 cursor-pointer" id="togglePassword"></i>
                <p id="passwordError" class="text-red-600 text-sm"></p>
            </div>

            <button type="submit" name="register"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                Sign Up
            </button>

            <p class="text-center text-gray-600">
                Already have an account? <a href="index.php" class="text-blue-600 hover:underline">Log In</a>
            </p>
        </form>
    </section>

    <footer class="absolute bottom-5 text-center text-white text-sm">
        &copy; 2025 Your Company. All Rights Reserved.
    </footer>

    <script>
        function validateForm() {
            let valid = true;
            let firstName = document.getElementById("first_name").value.trim();
            let lastName = document.getElementById("last_name").value.trim();
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();
            let weakPasswords = ["123456", "password", "123456789", "qwerty", "abc123", "12345678", "111111", "123123"];

            // Reset error messages
            document.getElementById("fnameError").innerText = "";
            document.getElementById("lnameError").innerText = "";
            document.getElementById("emailError").innerText = "";
            document.getElementById("passwordError").innerText = "";

            if (!firstName) {
                document.getElementById("fnameError").innerText = "*First name is required.";
                valid = false;
            }
            if (!lastName) {
                document.getElementById("lnameError").innerText = "Last name is required.";
                valid = false;
            }
            if (!email.includes("@")) {
                document.getElementById("emailError").innerText = "Invalid email address.";
                valid = false;
            }
            if (weakPasswords.includes(password) || password.length < 8) {
                document.getElementById("passwordError").innerText = "Weak or short password 6 character above.";
                valid = false;
            }

            return valid;
        }
    </script>

</body>
</html>
