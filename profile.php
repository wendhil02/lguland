<?php
session_start();
require 'connectiondb/connection.php';

// ✅ Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: landingmainpage.php");
    exit();
}

$id = $_SESSION['id'];

// ✅ Fetch user data
$sql = "SELECT *, COALESCE(verified, 0) AS verified FROM registerlanding WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$verified = (bool) $user['verified'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function sanitize($data) {
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

    $errors = [];

    // ✅ Server-side validation
    if (empty($first_name) || empty($last_name) || empty($birth_date) || empty($sex) || empty($mobile) || empty($working) || empty($occupation) || empty($house) || empty($street) || empty($barangay) || empty($city)) {
        $errors[] = "All required fields must be filled.";
    }

    if (!preg_match("/^[a-zA-ZñÑ\s-]+$/", $first_name) || !preg_match("/^[a-zA-ZñÑ\s-]+$/", $last_name)) {
        $errors[] = "Invalid name format. Only letters, spaces, and hyphens are allowed.";
    }

    if (!preg_match("/^[0-9]{11}$/", $mobile)) {
        $errors[] = "Invalid mobile number. It should be exactly 11 digits.";
    }

    if (strtotime($birth_date) > time()) {
        $errors[] = "Birth date cannot be in the future.";
    }

    // ✅ Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['profile_pic']['type'];
        $file_size = $_FILES['profile_pic']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
        }

        if ($file_size > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "File size must be less than 2MB.";
        }

        if (empty($errors)) {
            $upload_dir = "uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $new_file_name = "profile_" . $id . "." . $file_extension;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $profile_pic = $target_file;
            } else {
                $errors[] = "Error uploading profile picture.";
            }
        }
    } else {
        $profile_pic = $user['profile_pic'] ?? null;
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: profile.php");
        exit();
    }

    $verified = 1;

    $sql = "UPDATE registerlanding SET 
                first_name = ?, last_name = ?, middle_name = ?, suffix = ?, 
                birth_date = ?, sex = ?, mobile = ?, working = ?, occupation = ?, 
                house = ?, street = ?, barangay = ?, city = ?, verified = ?, profile_pic = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssssisi",
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
            $profile_pic,
            $id
        );

        if ($stmt->execute()) {
            // ✅ Sync updated data to the subdomain
            $postData = [
                'id' => $id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'middle_name' => $middle_name,
                'suffix' => $suffix,
                'birth_date' => $birth_date,
                'sex' => $sex,
                'mobile' => $mobile,
                'working' => $working,
                'occupation' => $occupation,
                'house' => $house,
                'street' => $street,
                'barangay' => $barangay,
                'city' => $city,
                'verified' => $verified,
                'profile_pic' => $profile_pic
            ];

            $ch = curl_init("https://bpa.smartbarangayconnect.com/sync.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            $response = curl_exec($ch);
            curl_close($ch);

            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['errors'] = ["Error updating record: " . $stmt->error];
            header("Location: profile.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['errors'] = ["Error in preparing statement: " . $conn->error];
        header("Location: profile.php");
        exit();
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
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

<?php if (isset($_SESSION['errors'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: '<?= implode("<br>", $_SESSION["errors"]) ?>',
            confirmButtonColor: '#d33'
        });
    </script>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= $_SESSION["success"] ?>',
            confirmButtonColor: '#28a745'
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

    <div class="container">
        <div class="flex justify-center items-center mb-4">
            <span class="px-4 py-2 text-lg font-semibold rounded-full shadow-md 
        <?= $verified ? 'bg-green-500 text-white animate-pulse' : 'bg-red-500 text-white' ?>">
                <i class="fas <?= $verified ? 'fa-check-circle' : 'fa-times-circle' ?> mr-2"></i>
                <?= $verified ? "Verified Profile" : "Unverified Profile" ?>
            </span>
        </div>

        <form action="profile.php" method="POST" enctype="multipart/form-data"> <!-- Added enctype for file uploads -->

    <!-- Profile Picture Upload -->
    <div class="mb-4 text-center">
        <label class="font-semibold text-gray-700 block">Profile Picture:</label>
        <div class="flex justify-center items-center">
            <img id="profilePreview" src="<?= $user['profile_pic'] ?? 'default-profile.png' ?>" class="w-32 h-32 object-cover rounded-full border shadow-md" alt="Profile Preview">
        </div>
        <input type="file" name="profile_pic" id="profilePicInput" class="mt-2 w-full p-2 border rounded-md">
    </div>

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

    <!-- Gender Selection -->
    <div class="mb-4">
        <label class="font-semibold text-gray-700">* Gender:</label>
        <div class="flex space-x-4">
            <label class="flex items-center">
                <input type="radio" name="sex" value="MALE" <?= $user['sex'] == 'MALE' ? 'checked' : '' ?> class="mr-2"> Male
            </label>
            <label class="flex items-center">
                <input type="radio" name="sex" value="FEMALE" <?= $user['sex'] == 'FEMALE' ? 'checked' : '' ?> class="mr-2"> Female
            </label>
            <label class="flex items-center">
                <input type="radio" name="sex" value="OTHER" <?= $user['sex'] == 'OTHER' ? 'checked' : '' ?> class="mr-2"> Other
            </label>
        </div>
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
        <button type="button" class="btn btn-exit" onclick="window.location.href='landingmainpage.php'">Exit</button>
        <button type="submit" class="btn btn-update"><i class="fas fa-save"></i> Update</button>
    </div>
</form>

<!-- JavaScript for Image Preview -->
<script>
document.getElementById('profilePicInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

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