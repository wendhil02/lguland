<?php


if (!isset($_POST['selectedIds'])) {
    die("User IDs are required.");
}

$selectedIds = json_decode($_POST['selectedIds'], true);

if (empty($selectedIds)) {
    die("No valid user IDs received.");
}

include('../connectiondb/connection.php');

$ids = json_decode($_POST['selectedIds'], true);
if (!is_array($ids) || empty($ids)) {
    die("Invalid user selection.");
}

// Convert IDs to integers
$ids = array_map('intval', $ids);

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT first_name, middle_name, last_name, suffix, email, birth_date, sex, mobile, city, house, street, barangay, working, occupation, created_at FROM registerlanding WHERE id IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Generate CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="User_Details.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['First Name', 'Middle Name', 'Last Name', 'Suffix', 'Email', 'Birth Date', 'Sex', 'Mobile', 'City', 'House No.', 'Street', 'Barangay', 'Working', 'Occupation', 'Created At']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>

