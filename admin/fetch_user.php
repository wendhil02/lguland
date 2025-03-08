<?php
include('../connectiondb/connection.php'); // Database connection

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "User ID not provided."]);
    exit();
}

$id = intval($_GET['id']); // Sanitize input
$query = "SELECT * FROM registerlanding WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "User not found."]);
}

$stmt->close();
$conn->close();
?>
