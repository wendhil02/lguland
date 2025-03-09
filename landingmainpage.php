<?php
session_start();
include("connectiondb/connection.php");
include('./assets/inc/header.php');

// ✅ Restrict guest users
if (!isset($_SESSION['email']) || !isset($_SESSION['session_token']) || !isset($_SESSION['id'])) {
    header("Location: index.php"); // Redirect to login page
    exit();
}

// ✅ Check if OTP is verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    $_SESSION['error_message'] = "You must complete OTP verification first!";
    header("Location: otp_verification.php?email=" . urlencode($_SESSION['email']));
    exit();
}

// ✅ Get email and session token from session
$email = $_SESSION['email'];
$session_token = $_SESSION['session_token'];

$user_id = $_SESSION['id'];

// ✅ Check session token validity and user verification status
$stmt = $conn->prepare("SELECT session_token, verified FROM registerlanding WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($db_session_token, $verified);
$stmt->fetch();
$stmt->close();

// ✅ If the session token does not match (user logged in elsewhere), log them out
if ($db_session_token !== $session_token) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// ✅ Fetch images for the dashboard
$images = [];
$image_query = $conn->query("SELECT image_path FROM imagesdashboard");

if ($image_query) {
    while ($row = $image_query->fetch_assoc()) {
        $images[] = $row['image_path'];
    }
}

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Flexbox to push footer to the bottom */
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content {
            flex-grow: 1;
        }
    </style>
</head>

<body class=" relative" style="background: url('./assets/img/lgupic.jpg') no-repeat center center/cover;">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>


    <header class="bg-blue-900 text-white py-4 shadow-md relative z-10">
    <div class="container mx-auto flex justify-between items-center px-6">
        <!-- Date and Time -->
        <div class="text-white text-sm">
            <?php echo date("l, F j Y, h:i:s A"); ?>
        </div>

        <!-- Logo and Title -->
        <div class="flex items-center space-x-3">
            <img src="./assets/img/logo.jpg" alt="QCe Logo" class="h-12 rounded-full border-2 border-yellow-400">
            <h1 class="text-lg font-bold">LGU E-SERVICES</h1>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative">
            <span class="text-white">Logged in as: 
                <a href="profile.php" class="font-semibold underline">
                    <?php
                    $first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Guest';
                    $last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
                    echo htmlspecialchars($first_name . ' ' . $last_name);
                    ?>
                </a>
            </span>

            <!-- Dropdown Toggle -->
            <span class="cursor-pointer ml-2 text-white" id="dropdownIcon">&#9660;</span>

            <!-- Dropdown Menu -->
            <div id="dropdownMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg hidden">
                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
        </div>
    </div>
</header>


    <div class="text-center py-3 text-white bg-blue-700 relative ">
        <h3 class="text-lg font-light">WELCOME TO</h3>
        <h1 class="text-4xl font-bold uppercase">LGU E-SERVICES</h1>
    </div>

    <div class="container mx-auto px-6 mt-8 relative z-10">
        <!-- Search Input -->
        <div class="mb-6 z-20">
            <div class="relative w-full">
                <input type="text" class="w-full p-3 pl-10 border rounded focus:ring focus:ring-yellow-400" placeholder="Search for a service...">
                <button class="absolute right-3 top-2 bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">Search</button>
            </div>
        </div>

        <h2 class="text-center text-3xl font-bold text-white mb-6 relative z-20">Choose service</h2>

        <div class="flex items-center justify-center min-h-screen relative z-10">
            <div class="relative w-full max-w-[1200px] h-[600px] overflow-hidden rounded-lg shadow-lg">
                <!-- Slider Container -->
                <div id="slider" class="flex h-full transition-transform duration-700 ease-in-out">
                    <?php foreach ($images as $image): ?>
                        <img src="<?php echo $image; ?>" class="w-full h-full object-cover flex-shrink-0" />
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Buttons -->
                <button id="prev" class="absolute top-1/2 left-2 -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700">
                    &#10094;
                </button>
                <button id="next" class="absolute top-1/2 right-2 -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700">
                    &#10095;
                </button>
            </div>
        </div>

        <br>

        <hr>

        <!-- Service Grid -->
        <div class="container mx-auto py-8 px-6 mt-5 relative z-10" style="background: url('./assets/img/lgupic.jpg') no-repeat center center/cover;">
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>

            <!-- All Services Title -->
            <h2 class="text-center text-4xl font-semibold text-white mb-6 relative z-20">All Services</h2>

            <!-- Service Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-20">
                <?php
                $sql = "SELECT image_url, name, description, service_link FROM services";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='bg-white p-6 rounded-lg shadow-lg transform hover:scale-105 transition duration-300 ease-in-out'>";
                        echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Service Image' class='w-full h-40 object-cover rounded-lg mb-4'>";
                        echo "<h3 class='text-lg font-semibold text-gray-900 mb-2'>" . htmlspecialchars($row['name']) . "</h3>";
                        echo "<p class='text-gray-600 mb-4'>" . htmlspecialchars($row['description']) . "</p>";

                        if (!empty($row['service_link'])) {
                            if ($verified) {
                                echo "<a href='" . htmlspecialchars($row['service_link']) . "' class='text-white font-semibold bg-blue-800 hover:underline 
                focus:outline-none focus:ring-2 focus:ring-blue-600 transition duration-300 
                mt-4 inline-block px-4 py-2 rounded-lg'>Learn More</a>";
                            } else {
                                echo "<a href='profile.php' class='text-white font-semibold bg-red-600 hover:underline 
                focus:outline-none focus:ring-2 focus:ring-red-400 transition duration-300 
                mt-4 inline-block px-4 py-2 rounded-lg'>Verify Account</a>";
                            }
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-gray-300 text-center'>No services found.</p>"; // Made text white for visibility
                }

                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <footer class="bg-blue-900 text-white text-center py-6 mt-10 relative z-10">
        <p class="text-lg font-semibold">LGU E-Services</p>
        <p class="text-sm">
            For inquiries, call <span class="text-yellow-400">122</span> or email
            <span class="text-yellow-400">helpdesk@quezoncity.gov.ph</span>
        </p>
        <div class="mt-4 text-sm">
            <a href="/terms-of-service.php" class="text-yellow-400 underline hover:text-yellow-300">Terms of Service</a>
        </div>
        <div class="mt-4 text-sm">
            <p>&copy; <?php echo date("Y"); ?> LGU E-Services. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const slider = document.getElementById("slider");
        const images = slider.children;
        let index = 0;
        const totalSlides = images.length;

        function updateSlider() {
            slider.style.transform = `translateX(-${index * 100}%)`;
        }

        function nextSlide() {
            index = (index + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            index = (index - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        document.getElementById("next").addEventListener("click", nextSlide);
        document.getElementById("prev").addEventListener("click", prevSlide);

        setInterval(nextSlide, 3000); // Auto-slide every 3 seconds

        document.addEventListener("DOMContentLoaded", function() {
            const dropdownIcon = document.getElementById("dropdownIcon");
            const dropdownMenu = document.getElementById("dropdownMenu");

            dropdownIcon.addEventListener("click", function(event) {
                event.stopPropagation();
                dropdownMenu.classList.toggle("hidden");
            });

            document.addEventListener("click", function(event) {
                if (!event.target.closest(".relative")) {
                    dropdownMenu.classList.add("hidden");
                }
            });
        });
    </script>
</body>

</html>