<?php
session_start();
require 'connectiondb/connection.php'; // Database connection file

if (!isset($_SESSION['id'])) {
    die("Unauthorized access.");
}

$id = $_SESSION['id']; // Retrieve user's ID from session

// Fetch existing user data
$sql = "SELECT *, COALESCE(verified, 0) AS verified FROM registerlanding WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$verified = (bool) $user['verified'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function sanitize($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $middle_name = sanitize($_POST['middle_name'] ?? '');
    $suffix = sanitize($_POST['suffix']);
    $birth_date = $_POST['birth_date'];
    $sex = sanitize($_POST['sex']);
    $mobile = sanitize($_POST['mobile']);
    $working = sanitize($_POST['working']);
    $occupation = sanitize($_POST['occupation']);
    $house = sanitize($_POST['house']);
    $street = sanitize($_POST['street']);
    $barangay = sanitize($_POST['barangay']);
    $city = sanitize($_POST['city']);

    // **Server-side validation**
    if (empty($first_name) || empty($last_name) || empty($birth_date) || empty($sex) || empty($mobile) || empty($working) || empty($occupation) || empty($house) || empty($street) || empty($barangay) || empty($city)) {
        die("All required fields must be filled.");
    }

    if (!preg_match("/^[0-9]{11}$/", $mobile)) {
        die("Invalid mobile number. It should be exactly 11 digits.");
    }

    if (strtotime($birth_date) > time()) {
        die("Birth date cannot be in the future.");
    }

    $verified = 1;

    $sql = "UPDATE registerlanding SET 
                first_name = ?, last_name = ?, middle_name = ?, suffix = ?, 
                birth_date = ?, sex = ?, mobile = ?, working = ?, occupation = ?, 
                house = ?, street = ?, barangay = ?, city = ?, verified = ? 
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssssii",
            $first_name,
            $last_name,
            $middle_name,
            $suffix,
            $birth_date,
            $sex,
            $mobile,
            $working,
            $occupation,
            $house,
            $street,
            $barangay,
            $city,
            $verified,
            $id
        );

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
        } else {
            die("Error updating record: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error in preparing statement: " . $conn->error);
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: url('assets/img/lgupic.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
            color: #040505;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-exit {
            background-color: #6c757d;
            color: white;
        }

        .btn-exit:hover {
            background-color: #5a6268;
        }

        .btn-update {
            background-color: #007bff;
            color: white;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .btn-deactivate {
            background-color: #dc3545;
            color: white;
        }

        .btn-deactivate:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <header class="bg-blue-900 shadow-md p-1 text-center text-black">
        <div class="container mx-auto">
            <div class="text-sm">
                <p class="opacity-90"><?= date("l, F j Y, h:i:s A") ?></p>
                <div class="mt-2 flex justify-center items-center">
                    <img src="./assets/img/logo.jpg" alt="QCe Logo" class="w-12 h-12 mr-2">
                    <span>Logged in as:
                        <a href="profile.php" class="text-blue-600 font-semibold hover:underline">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </a>
                    </span>
                </div>
            </div>
        </div>

        <!-- Custom Alert Modal -->
        <div id="customAlert" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
                <h2 class="text-lg font-semibold">Notification</h2>
                <p id="alertMessage" class="mt-2 text-gray-700"></p>
                <button onclick="closeAlert()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg">OK</button>
            </div>
        </div>

    </header>

    <div class="container">
        <div class="flex justify-center items-center mb-4">
            <span class="px-4 py-2 text-lg font-semibold rounded-full shadow-md 
        <?= $verified ? 'bg-green-500 text-white animate-pulse' : 'bg-red-500 text-white' ?>">
                <i class="fas <?= $verified ? 'fa-check-circle' : 'fa-times-circle' ?> mr-2"></i>
                <?= $verified ? "Verified Profile" : "Unverified Profile" ?>
            </span>
        </div>

        <form action="profile.php" method="POST">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="font-semibold text-gray-700">* First Name:</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full p-2 border rounded-md">
                </div>
                <div>
                    <label class="font-semibold text-gray-700">* Last Name:</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full p-2 border rounded-md">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="font-semibold text-gray-700">Middle Name (Optional):</label>
                    <input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" class="w-full p-2 border rounded-md">
                </div>
                <div>
                    <label class="font-semibold text-gray-700">Suffix:</label>
                    <select name="suffix" class="w-full p-2 border rounded-md">
                        <option value="">Select</option>
                        <option value="PhD">none</option>
                        <option value="Jr.">Jr.</option>
                        <option value="Sr.">Sr.</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                        <option value="PhD">PhD</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Birth Date:</label>
                <input type="date" name="birth_date" value="<?= $user['birth_date'] ?? '' ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Sex:</label>
                <select name="sex" class="w-full p-2 border rounded-md">
                    <option value="MALE" <?= $user['sex'] == 'MALE' ? 'selected' : '' ?>>Male</option>
                    <option value="FEMALE" <?= $user['sex'] == 'FEMALE' ? 'selected' : '' ?>>Female</option>
                    <option value="OTHER" <?= $user['sex'] == 'OTHER' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Mobile Number:</label>
                <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>


            <div class="mb-4">
                <label class="font-semibold text-gray-700">* House Number:</label>
                <input type="text" name="house" value="<?= htmlspecialchars($user['house'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Street:</label>
                <input type="text" name="street" value="<?= htmlspecialchars($user['street'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Barangay:</label>
                <input type="text" name="barangay" value="<?= htmlspecialchars($user['barangay'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* City:</label>
                <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Are you working in Quezon City?</label>
                <div class="flex space-x-4">
                    <label><input type="radio" name="working" value="yes" <?= $user['working'] == 'yes' ? 'checked' : '' ?>> Yes</label>
                    <label><input type="radio" name="working" value="no" <?= $user['working'] == 'no' ? 'checked' : '' ?>> No</label>
                </div>
            </div>

            <div class="mb-4">
                <label class="font-semibold text-gray-700">* Occupation:</label>
                <input type="text" name="occupation" value="<?= htmlspecialchars($user['occupation'] ?? '') ?>" class="w-full p-2 border rounded-md">
            </div>

            <div class="text-center mt-6">
                <button type="button" class="btn btn-exit" onclick="window.location.href='index.php'">Exit</button>
                <button type="submit" class="btn btn-update"><i class="fas fa-save"></i> Update</button>
                <button type="button" class="btn btn-deactivate">Deactivate Account</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector("form").addEventListener("submit", function(event) {
                let errors = [];

                function validateField(field, message) {
                    if (!field.value.trim()) {
                        errors.push(message);
                        field.classList.add("border-red-500");
                    } else {
                        field.classList.remove("border-red-500");
                    }
                }

                const firstName = document.querySelector("[name='first_name']");
                const lastName = document.querySelector("[name='last_name']");
                const birthDate = document.querySelector("[name='birth_date']");
                const mobile = document.querySelector("[name='mobile']");
                const house = document.querySelector("[name='house']");
                const street = document.querySelector("[name='street']");
                const barangay = document.querySelector("[name='barangay']");
                const city = document.querySelector("[name='city']");
                const occupation = document.querySelector("[name='occupation']");
                const working = document.querySelector("[name='working']:checked");

                validateField(firstName, "First Name is required.");
                validateField(lastName, "Last Name is required.");
                validateField(birthDate, "Birth Date is required.");
                validateField(mobile, "Mobile Number is required.");
                validateField(house, "House Number is required.");
                validateField(street, "Street is required.");
                validateField(barangay, "Barangay is required.");
                validateField(city, "City is required.");
                validateField(occupation, "Occupation is required.");

                // Validate Mobile Number Format
                const mobilePattern = /^[0-9]{11}$/;
                if (!mobilePattern.test(mobile.value.trim())) {
                    errors.push("Mobile number must be exactly 11 digits.");
                    mobile.classList.add("border-red-500");
                }

                // Validate Birth Date (Should not be in the future)
                if (new Date(birthDate.value) > new Date()) {
                    errors.push("Birth date cannot be in the future.");
                    birthDate.classList.add("border-red-500");
                }

                // Ensure 'Working' radio button is selected
                if (!working) {
                    errors.push("Please select whether you are working in Quezon City.");
                }

                if (errors.length > 0) {
                    showAlert(errors.join("<br>")); // Tawagin ang custom alert
                    event.preventDefault();
                }
            });
        });

        // Custom Alert Function
        function showAlert(message) {
            document.getElementById("alertMessage").innerHTML = message;
            document.getElementById("customAlert").classList.remove("hidden");
        }

        // Close Alert Function
        function closeAlert() {
            document.getElementById("customAlert").classList.add("hidden");
        }
    </script>

</body>

</html>