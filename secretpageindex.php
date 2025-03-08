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

<!-- Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
</head>
<body>
    <h2>Super Admin Login</h2>
    <form action="secretpageindex.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required><br><br>
        
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>
