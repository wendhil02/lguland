<?php
session_start();
include '../connectiondb/connection.php';
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

include('./assets/inc/header.php');
include('../connectiondb/connection.php'); // Database connection file

$sql = "SELECT * FROM services";
$result = $conn->query($sql);

// Delete service if delete button is clicked
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM services WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        $_SESSION['notification'] = "Service deleted successfully!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Error deleting service: " . $conn->error;
        $_SESSION['notification_type'] = "error";
    }

    // Redirect to the same page to avoid form resubmission after refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Make sure no further code runs
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $service_link = isset($_POST['service_link']) ? trim($_POST['service_link']) : ''; // Get the link (if any)

    // Handle File Upload
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = array("jpg", "jpeg", "png", "gif");

    if (in_array($imageFileType, $allowed_types)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "uploads/" . $file_name;

            // Insert into database including service link
            $stmt = $conn->prepare("INSERT INTO services (name, description, image_url, service_link) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $description, $image_url, $service_link);  // Adding service_link parameter

            if ($stmt->execute()) {
                echo "<script>alert('Service added successfully!'); window.location.href = 'servicesadmin.php';</script>";
            } else {
                echo "<script>alert('Database error: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error uploading the file.');</script>";
        }
    } else {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                <div class="bg-gray-200 p-6 rounded-lg shadow-md max-w-4xl mx-auto">
                    <h2 class="text-center text-2xl font-semibold text-blue-800 mb-6">Add New Service</h2>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block mb-2 text-gray-700 font-semibold">Service Name:</label>
                            <input type="text" name="name" required class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block mb-2 text-gray-700 font-semibold">Description:</label>
                            <textarea name="description" required class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block mb-2 text-gray-700 font-semibold">Upload Image:</label>
                            <input type="file" name="image" accept="image/*" required class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block mb-2 text-gray-700 font-semibold">Service Link (Optional):</label>
                            <input type="url" name="service_link" class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="http://example.com">
                        </div>

                        <div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 w-full transition-all">
                                Add Service
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Existing Services Section -->
                <div class="bg-gray-200 p-6 rounded-lg shadow-md max-w-4xl mx-auto">
                    <h2 class="text-center text-2xl font-semibold text-blue-800 mb-6">Existing Services</h2>

                    <?php if (isset($_SESSION['notification'])): ?>
                        <div class="alert <?php echo $_SESSION['notification_type']; ?> mb-4" id="notification">
                            <?php echo $_SESSION['notification']; ?>
                            <?php unset($_SESSION['notification'], $_SESSION['notification_type']); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    if ($result->num_rows > 0) {
                        echo '<table class="w-full table-auto border-collapse border border-gray-100">';
                        echo '<thead class="bg-gray-400 text-white">
                <tr>
                    <th class="border p-3 text-left text-black">Service Name</th>
                    <th class="border p-3 text-center text-black">Actions</th>
                </tr>
              </thead><tbody>';
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td class="border p-3">' . htmlspecialchars($row['name']) . '</td>';
                            echo '<td class="border p-3 text-center flex justify-center space-x-2">';

                            // Edit Button (Triggers Modal)
                            echo '<button class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-800 transition edit-btn" 
                   data-id="' . $row['id'] . '" 
                   data-name="' . htmlspecialchars($row['name']) . '" 
                   data-description="' . htmlspecialchars($row['description']) . '"
                   data-image="' . htmlspecialchars($row['image_url']) . '"
                   data-link="' . htmlspecialchars($row['service_link']) . '">
                    <i class="bx bx-edit text-xl"></i>
                  </button>';

                            // Delete Button
                            echo '<a href="?delete_id=' . $row['id'] . '" 
                   class="bg-red-500 text-white p-2 rounded-full hover:bg-red-700 transition">
                    <i class="bx bx-trash text-xl"></i>
                  </a>';

                            echo '</td></tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p class="text-center text-gray-500">No services found.</p>';
                    }
                    ?>
                </div>




                <!-- Update Modal -->
                <div id="updateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Service</h3>

                        <form id="updateForm" method="POST" action="updateservices.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_id" id="service_id">

                            <!-- Service Name -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Service Name</label>
                                <input type="text" name="service_name" id="service_name" class="w-full p-2 border rounded-md">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="service_description" id="service_description" class="w-full p-2 border rounded-md"></textarea>
                            </div>

                            <!-- Service Link -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Service Link</label>
                                <input type="text" name="service_link" id="service_link" class="w-full p-2 border rounded-md">
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700">Upload New Image</label>
                                <input type="file" name="service_image" class="w-full p-2 border rounded-md">
                                <p class="text-sm text-gray-500">Leave blank to keep the current image.</p>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
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

            // Automatically hide the notification after 5 seconds
            setTimeout(function() {
                const notification = document.getElementById("notification");
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 5000); // 5000 milliseconds (5 seconds)
        </script>
</body>
</html>