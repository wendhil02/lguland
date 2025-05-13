<?php
header("Content-Type: application/json");

// ✅ Secure CORS Configuration (Allow only subdomains of smartbarangayconnect.com)
$allowed_origin = "https://smartbarangayconnect.com";
if (isset($_SERVER['HTTP_ORIGIN']) && preg_match('/^https:\/\/([a-z0-9-]+\.)?smartbarangayconnect\.com$/', $_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(["status" => "error", "message" => "CORS policy does not allow this origin."]);
    exit();
}

// ✅ Allow necessary HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight requests (OPTIONS method)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

include 'connectiondb/connection.php';  // Database connection

// ✅ Get JSON input and validate
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id']) || !isset($data['session_token'])) {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
    exit();
}

$user_id = intval($data['id']); // Ensure it's an integer
$session_token = $conn->real_escape_string($data['session_token']); // Sanitize input

// ✅ Validate user session
$stmt = $conn->prepare("
    SELECT id, email, first_name, middle_name, last_name, suffix, birth_date, sex, mobile, working, 
           occupation, house, street, barangay, city, picture_pic 
    FROM registerlanding 
    WHERE id = ? AND session_token = ?
");
$stmt->bind_param("is", $user_id, $session_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // ✅ Ensure `picture_pic` has a valid URL (default if empty)
    if (!empty($row['picture_pic'])) {
        if (!filter_var($row['picture_pic'], FILTER_VALIDATE_URL)) {
            $row['picture_pic'] = "https://smartbarangayconnect.com" . $row['picture_pic'];
        }
    } else {
        $row['picture_pic'] = "https://smartbarangayconnect.com/uploads/default-profile.png";
    }

    // ✅ Return JSON response
    echo json_encode([
        "status" => "success",
        "id" => $row['id'],
        "email" => $row['email'],
        "first_name" => $row['first_name'],
        "middle_name" => $row['middle_name'] ?? null, // Handle missing values
        "last_name" => $row['last_name'],
        "suffix" => $row['suffix'] ?? null,
        "birth_date" => $row['birth_date'] ?? null,
        "sex" => $row['sex'] ?? null,
        "mobile" => $row['mobile'] ?? null,
        "working" => $row['working'] ?? null,
        "occupation" => $row['occupation'] ?? null,
        "house" => $row['house'] ?? null,
        "street" => $row['street'] ?? null,
        "barangay" => $row['barangay'] ?? null,
        "city" => $row['city'] ?? null,
        "picture_pic" => $row['picture_pic'],
        "session_token" => $session_token
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid session credentials"]);
}

$stmt->close();
$conn->close();
?>



