<?php
include 'connectiondb/connection.php';

header('Content-Type: application/json');

// ✅ Get all records from registerlanding
$sql = "SELECT * FROM registerlanding";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// ✅ Output JSON data
echo json_encode($data);
?>
