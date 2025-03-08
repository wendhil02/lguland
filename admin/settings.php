<?php
session_start();

include('./assets/inc/header.php');
include('../connectiondb/connection.php'); // Database connection file

// Ensure the user is logged in and is a Super Admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Super Admin') {
    header("Location: ../secretpageindex.php"); // Redirect to login page if not logged in or not a Super Admin
    exit();
}

// Get the session ID from the database for the logged-in user
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT session_id FROM registerlanding WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$db_session_id = $row['session_id'];

if ($_SESSION['session_id'] !== $db_session_id) {
    // If the session ID doesn't match, log the user out
    session_unset();
    session_destroy();
    header("Location: ../secretpageindex.php"); // Redirect to login page
    exit();
}


$message = ""; // Initialize message variable

// Handle Image Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $targetDir = "../uploads/"; // Folder for images
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $displayPath = "uploads/" . $fileName; // Path for display

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO imagesdashboard (image_path) VALUES (?)");
        $stmt->bind_param("s", $displayPath);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                                    class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg transition-opacity duration-500">
                                    ✅ Image uploaded successfully!
                                </div>';
    } else {
        $_SESSION['message'] = '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                                    class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg transition-opacity duration-500">
                                    ❌ Upload failed. Please try again.
                                </div>';
    }
    header("Location: ".$_SERVER['PHP_SELF']); // Redirect to prevent resubmission
    exit();
}

// Handle Image Removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_image"])) {
    $imageId = $_POST["image_id"];

    // Get image path from database
    $stmt = $conn->prepare("SELECT image_path FROM imagesdashboard WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    if ($imagePath) {
        $fileToDelete = "../" . $imagePath; // Full path to the image
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete); // Delete file from server
        }

        // Remove from database
        $stmt = $conn->prepare("DELETE FROM imagesdashboard WHERE id = ?");
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                                    class="mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg transition-opacity duration-500">
                                    ⚠️ Image removed successfully!
                                </div>';
    } else {
        $_SESSION['message'] = '<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                                    class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg transition-opacity duration-500">
                                    ❌ Error removing image.
                                </div>';
    }
    header("Location: ".$_SERVER['PHP_SELF']); // Redirect to prevent resubmission
    exit();
}

// Retrieve and clear session message
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
unset($_SESSION['message']);

// Fetch uploaded images from database
$result = $conn->query("SELECT id, image_path FROM imagesdashboard ORDER BY id DESC");
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Add smooth transition to sidebar */
        #sidebar {
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            /* Use a smooth cubic-bezier easing function */
            transform: translateX(0);
            z-index: 10;
            /* Ensure the sidebar is on top */
        }

        /* Sidebar hidden using translateX */
        #sidebar.hidden {
            transform: translateX(-100%);
        }

        /* Smooth transition for the toggle button */
        #toggleSidebar {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        /* Styling for success and error notifications */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #28a745;
            color: white;
        }

        .alert-error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>

<body class="relative bg-cover bg-center" style="background-image: url('../assets/img/lgupic.jpg'); background-size: cover; background-position: center;">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-65"></div>

    <!-- Content Wrapper -->
    <div class="relative z-10">
        <header class="bg-blue-900 text-white py-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center px-6">
                <!-- Date Section -->
                <div class="text-white text-sm hidden sm:block">
                    <?php echo date("l, F j Y , h:i:s A"); ?>
                </div>

                <!-- Logo and Title -->
                <div class="flex items-center space-x-3">
                    <img src="../assets/img/logo.jpg" alt="QCe Logo" class="h-12 rounded-full border-2 border-yellow-400">
                    <h1 class="text-lg font-bold text-white">LGU E-SERVICES</h1>
                </div>

                <!-- Logged in User and Dropdown -->
                <div class="relative flex items-center space-x-3">
                <a href="logoutsuperadmin.php" class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700">Logout</a>
                <span class="text-white text-sm hidden sm:block">
    Logged in as: 
    <a href="usermng.php" class="font-semibold underline">
        <?php 
            if (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) && isset($_SESSION['role'])) {
                // Check user role
                if ($_SESSION['role'] === 'Super Admin') {
                    echo 'Super Admin: ' . htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                } elseif ($_SESSION['role'] === 'admin') {
                    echo 'Admin: ' . htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                } else {
                    echo 'User: ' . htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                }
            } else {
                echo 'Guest'; // If session variables aren't set
            }
        ?>
    </a>
</span>
                    <span class="cursor-pointer text-white text-sm sm:text-base" id="dropdownIcon">&#9660;</span>
                    <div class="absolute right-0 mt-2 bg-white border rounded shadow-lg hidden" id="dropdownMenu">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </div>
        </header>


        <div class="flex">
            <!-- Sidebar -->
            <div id="sidebar" class="w-64 min-h-screen bg-red-900 text-white p-6 transition-transform duration-300 ease-in-out transform">
                <div class="flex items-center space-x-3">
                    <img src="../assets/img/logo.jpg" alt="QCe Logo" class="h-12 rounded-full border-2 border-yellow-400">
                    <h1 class="text-sm font-bold text-white">LGU E-SERVICES</h1>
                </div>

                <br>
                <hr>
                <br>
                <h2 class="text-xl font-semibold mb-6 text-white">Admin Management</h2>
                <ul>
                    <li>
                        <a href="servicesadmin.php" class="block py-2 px-4 hover:bg-yellow-500 rounded flex items-center space-x-2">
                            <i class="fas fa-tools"></i> <!-- Updated icon -->
                            <span>Services Management</span>
                        </a>

                    </li>
                    <li>
                        <a href="usermng.php" class="block py-2 px-4 hover:bg-yellow-500 rounded flex items-center space-x-2">
                            <i class="fas fa-users"></i> <!-- User Management icon -->
                            <span>User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="block py-2 px-4 hover:bg-yellow-500 rounded flex items-center space-x-2">
                            <i class="fas fa-cogs"></i> <!-- Settings icon -->
                            <span>Settings</span>
                        </a>
                    </li>
                    <!-- Add more menu items as needed -->
                </ul>

            </div>

            <!-- Main Content Area -->
            <div class="flex-1 p-6 space-y-6">
                <!-- Toggle Button for Sidebar -->
                <div class="flex justify-start">
                    <button id="toggleSidebar" class="bg-blue-600 text-white p-3 rounded hover:bg-blue-700 transition-all">
                        <i class="fas fa-bars"></i> <!-- Font Awesome bars icon -->
                    </button>
                </div>

                <!-- Add New Service Section -->
                <div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Upload Image</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 shadow rounded-lg">
        <input type="file" name="image" required class="block w-full border border-gray-300 rounded-lg p-2 mb-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">Upload</button>
    </form>

    <?= $message; ?> <!-- Show success/error message -->

    <!-- Display uploaded images with remove button -->
    <div class="mt-6">

    <h3 class="text-xl font-semibold text-white bg-blue-500 rounded-lg px-4 py-2 inline-block shadow-md">
    Uploaded Images
</h3>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white shadow rounded-lg p-4 flex flex-col items-center">
                    <img src="<?= '../' . $row['image_path']; ?>" alt="Uploaded Image" class="w-40 h-40 object-cover rounded-lg">
                    <form action="" method="POST" class="mt-2">
                        <input type="hidden" name="image_id" value="<?= $row['id']; ?>">
                        <button type="submit" name="remove_image" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-sm">
                            Remove
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
                <!-- Existing Services Section -->
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const editButtons = document.querySelectorAll(".edit-btn");
                const modal = document.getElementById("updateModal");
                const closeModal = document.getElementById("closeModal");

                editButtons.forEach(button => {
                    button.addEventListener("click", function() {
                        document.getElementById("service_id").value = this.getAttribute("data-id");
                        document.getElementById("service_name").value = this.getAttribute("data-name");
                        document.getElementById("service_description").value = this.getAttribute("data-description");
                        document.getElementById("service_link").value = this.getAttribute("data-link");

                        modal.classList.remove("hidden");
                    });
                });

                closeModal.addEventListener("click", function() {
                    modal.classList.add("hidden");
                });

                window.addEventListener("click", function(event) {
                    if (event.target === modal) {
                        modal.classList.add("hidden");
                    }
                });
            });

            // JavaScript for sidebar toggle functionality
            document.getElementById("toggleSidebar").addEventListener("click", function() {
                const sidebar = document.getElementById("sidebar");
                sidebar.classList.toggle("hidden"); // Toggles the sidebar's visibility with slide animation

                // Change the icon to a close (X) icon when sidebar is collapsed
                const icon = document.querySelector("#toggleSidebar i");
                if (sidebar.classList.contains("hidden")) {
                    icon.classList.remove("fa-bars");
                    icon.classList.add("fa-times");
                } else {
                    icon.classList.remove("fa-times");
                    icon.classList.add("fa-bars");
                }
            });
        </script>
</body>

</html>