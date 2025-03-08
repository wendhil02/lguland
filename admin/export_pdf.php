<?php
require('libs/fpdf186/fpdf.php');
include('../connectiondb/connection.php');

if (!isset($_POST['selectedIds'])) {
    die("User IDs are required.");
}

$selectedIds = json_decode($_POST['selectedIds'], true);

if (!is_array($selectedIds) || empty($selectedIds)) {
    die("No valid user IDs received.");
}

// Convert IDs to integers
$ids = array_map('intval', $selectedIds);

// Prepare SQL with placeholders
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = "SELECT first_name, middle_name, last_name, suffix, email, birth_date, sex, mobile, city, house, street, barangay, working, occupation, created_at 
          FROM registerlanding 
          WHERE id IN ($placeholders)";

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'User Details', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);

while ($user = $result->fetch_assoc()) {
    $fields = [
        'First Name' => $user['first_name'],
        'Middle Name' => $user['middle_name'],
        'Last Name' => $user['last_name'],
        'Suffix' => $user['suffix'],
        'Email' => $user['email'],
        'Birth Date' => $user['birth_date'],
        'Sex' => $user['sex'],
        'Mobile' => $user['mobile'],
        'City' => $user['city'],
        'House No.' => $user['house'],
        'Street' => $user['street'],
        'Barangay' => $user['barangay'],
        'Working' => $user['working'],
        'Occupation' => $user['occupation'],
        'Created At' => $user['created_at']
    ];

    foreach ($fields as $label => $value) {
        $pdf->Cell(0, 10, "$label: $value", 0, 1);
    }

    $pdf->Ln(5); // Space between users
}

$pdf->Output('D', 'User_Details.pdf'); // 'D' for download
?>
