<?php
header("Content-Type: application/json");
include 'connectiondb/connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $token = $data['token'] ?? '';

    $stmt = $conn->prepare("SELECT id, email FROM registerlanding WHERE session_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        echo json_encode(["status" => "success", "id" => $row['id'], "email" => $row['email']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid token"]);
    }
}
?>
