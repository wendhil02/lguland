<?php
include 'connectiondb/connection.php'; // Siguraduhing tama ang connection file

// Super Admin account details
$first_name = "Super";
$last_name = "Admin";
$email = "superadmin@gmail.com";
$password = "SuperAdmin123"; // Default password (iha-hash natin)
$hashed_password = password_hash($password, PASSWORD_BCRYPT); // Encrypt password
$role = "super admin";

// Check if super admin already exists
$checkStmt = $conn->prepare("SELECT id FROM registerlanding WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "❌ Super Admin account already exists!";
} else {
    // Insert new Super Admin account
    $stmt = $conn->prepare("INSERT INTO registerlanding (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "✅ Super Admin account successfully created!";
    } else {
        echo "❌ Error creating Super Admin account!";
    }

    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>
