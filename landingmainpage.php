<?php
session_start();
include("connectiondb/connection.php");
include('./assets/inc/header.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

//  Debugging: Check session variables
error_log("📧 Session Email: " . ($_SESSION['email'] ?? 'Not Set'));
error_log(" Session Token: " . ($_SESSION['session_token'] ?? 'Not Set'));
error_log("🆔 Session ID: " . ($_SESSION['id'] ?? 'Not Set'));

// ✅ Prevent access if OTP is not verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
  $_SESSION['error_message'] = "You must verify OTP first.";
  header("Location: otp_verification.php");
  exit();
}

//  Redirect if session variables are missing
if (!isset($_SESSION['email'], $_SESSION['session_token'], $_SESSION['id'])) {
  error_log(" Session missing! Redirecting to index.php...");
  $_SESSION['error_message'] = "Session expired. Please log in again.";
  header("Location: index.php");
  exit();
}

//  Get user details from session
$email = $_SESSION['email'];
$session_token = $_SESSION['session_token'];
$user_id = $_SESSION['id'];

// ✅ Check session token validity in database
$stmt = $conn->prepare("SELECT session_token, verified, first_name, last_name FROM registerlanding WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

//  Ensure user data is retrieved
if (!$user) {
  error_log(" User not found in database! Logging out...");
  session_destroy();
  header("Location: index.php");
  exit();
}

//  Extract user details safely
$verified = (int) ($user['verified'] ?? 0); // Convert to integer
$first_name = $user['first_name'] ?? 'Guest';
$last_name = $user['last_name'] ?? '';

// ❌ Force logout if session token mismatches
if (!isset($user['session_token']) || $user['session_token'] !== $session_token) {
  error_log(" Session token mismatch! Logging out...");
  session_destroy();
  header("Location: index.php");
  exit();
}

// ❌ Redirect if OTP is not verified
// ✅ Allow unverified users to enter but show warning
if ($verified !== 1) {
  $_SESSION['warning_message'] = "Your account is not verified! Some features will be restricted until verification is complete.";
  error_log("⚠ User is not verified. Restricting access to certain features.");
  $restricted_access = true; // Flag para sa restricted access
} else {
  $restricted_access = false; // Walang restriction kung verified na
}

//  Update last_activity timestamp
$update_stmt = $conn->prepare("UPDATE registerlanding SET last_activity = NOW() WHERE email = ?");
$update_stmt->bind_param("s", $email);
$update_stmt->execute();
$update_stmt->close();

//  Session timeout (4 hours)
$session_timeout = 14400;  // 4 hours = 14400 seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
  error_log(" Session expired! Logging out...");
  session_unset();
  session_destroy();
  header("Location: logout.php");
  exit();
}

// ✅ Update session activity timestamp
$_SESSION['last_activity'] = time();

//  Fetch images for the dashboard
$images = [];
$image_query = $conn->query("SELECT image_path FROM imagesdashboard");

if ($image_query) {
  while ($row = $image_query->fetch_assoc()) {
    $images[] = $row['image_path'];
  }
}

//  Auto-login URL for subdomain
$subdomain_auto_login_url = "https://bpa.smartbarangayconnect.com/index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
$subdomain_auto_login_url = "https://businesspermit.unifiedlgu.com/index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
$subdomain_auto_login_url = "https://childdevelopment.smartbarangayconnect.com/index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
$subdomain_auto_login_url = "https://crms.unifiedlgu.com/index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
$subdomain_auto_login_url = "https://https://cyms.smartbarangayconnect.com/index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
$subdomain_auto_login_url = "https://votermanagement.smartbarangayconnect.com//index.php?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LGU E-Services</title>
  <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html,
    body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    .content {
      flex-grow: 1;
    }
  </style>
</head>

<body class="relative" style="background: url('./assets/img/lgupic.jpg') no-repeat center center/cover;">
  <div class="absolute inset-0 bg-black bg-opacity-50"></div>

  <!-- Header -->
  <header class="bg-blue-900 text-white py-4 shadow-md relative z-10">
    <div class="container mx-auto flex flex-col md:flex-row justify-between items-center px-4 md:px-6 space-y-2 md:space-y-0">
      <!--  Date and Time (Hidden in Mobile) -->
      <div class="text-white text-xs sm:text-sm hidden md:block" id="realTimeClock">
        <?php echo date("l, F j Y, h:i:s A"); ?>
      </div>

      <!--  Logo and Title (Centered on Mobile) -->
      <div class="flex flex-col items-center md:flex-row md:space-x-3 md:mr-2">
        <img src="./assets/img/logo.jpg" alt="LGU Logo" class="relative  h-[85px] sm:h-28 md:h-[50px]  md:left-[30px] rounded-full border-2 border-yellow-400">
      </div>



      <!--  Profile Status Badge & Dropdown -->
      <div class="flex flex-col md:flex-row md:items-center md:space-x-4 text-center md:text-left">
        <!--  Profile Status Badge -->
        <span class="flex items-center justify-center px-3 py-2 text-xs sm:text-sm font-semibold rounded-full shadow-md 
            <?= $verified ? 'bg-green-600 text-white animate-pulse' : 'bg-red-600 text-white' ?>">
          <i class="fas <?= $verified ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-sm sm:text-lg"></i>
        </span>

        <!--  Profile Dropdown -->
        <div class="relative mt-2 md:mt-0">
          <span class="text-white text-xs sm:text-sm">
            Logged in as:
            <a href="profile.php" class="font-semibold underline">
              <?php echo htmlspecialchars(trim($first_name . ' ' . $last_name)); ?>
            </a>
          </span>
          <span class="cursor-pointer ml-1 md:ml-2 text-white" id="dropdownIcon">&#9660;</span>

          <!-- 📜 Dropdown Menu -->
          <div id="dropdownMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg hidden">
            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>



  <div class="text-center py-3 text-white bg-blue-700 relative">
    <h3 class="text-lg font-light">WELCOME TO</h3>
    <h1 class="text-4xl font-bold uppercase">LGU E-SERVICES</h1>
  </div>

  <div class="container mx-auto px-4 mt-8 relative z-10">
    <!-- Search Input -->
    <div class="mb-6">
      <div class="relative w-full">
        <input type="text" id="searchInput" class="w-full p-3 pl-10 border rounded focus:ring focus:ring-blue-400" placeholder="Search for a service...">
        <button id="searchButton" class="absolute right-3 top-2 bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">Search</button>
      </div>
      <div id="notification" class="hidden fixed top-5 left-1/2 transform -translate-x-1/2 bg-red-500 text-white text-sm px-4 py-2 rounded-lg shadow-md transition-opacity duration-300">
        No matching services found.
      </div>

    </div>

    <h2 class="text-center text-3xl font-bold text-white mb-6">Choose Service</h2>
    <div class="flex items-center justify-center min-h-[30vh] sm:min-h-[75vh] md:min-h-[80vh] lg:min-h-screen relative z-10">
      <div class="relative w-full max-w-[960px] h-[300px] sm:h-[400px] md:h-[500px] lg:h-[600px] overflow-hidden rounded-lg shadow-lg">
        <!-- Slider Container -->
        <div id="slider" class="flex h-full transition-transform duration-700 ease-in-out">
          <?php foreach ($images as $image): ?>
            <img src="<?php echo $image; ?>"
              class="w-full h-auto aspect-[16/9] sm:aspect-[16/8] md:aspect-[16/7] lg:aspect-[16/6] xl:aspect-[16/5] object-cover rounded-lg shadow-md" />
          <?php endforeach; ?>
        </div>

        <!-- Navigation Dots -->
        <div id="dots" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-3">
          <?php foreach ($images as $index => $image): ?>
            <button class="dot w-4 h-4 rounded-full bg-gray-400 hover:bg-gray-600 transition duration-300 focus:outline-none" data-index="<?php echo $index; ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- JavaScript for Auto-Sliding & Dots Navigation -->
    <script>
      const slider = document.getElementById("slider");
      const dots = document.querySelectorAll(".dot");
      let currentIndex = 0;
      let totalSlides = dots.length;
      let autoSlideInterval;

      function goToSlide(index) {
        currentIndex = index;
        const offset = -index * 100;
        slider.style.transform = `translateX(${offset}%)`;

        // Update active dot
        dots.forEach(dot => dot.classList.remove("bg-gray-800"));
        dots[index].classList.add("bg-gray-800");
      }

      function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        goToSlide(currentIndex);
      }

      function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 3000); // Auto-slide every 3 seconds
      }

      // Click event for dots
      dots.forEach((dot, index) => {
        dot.addEventListener("click", () => {
          goToSlide(index);
          clearInterval(autoSlideInterval); // Stop auto-slide when user clicks
          startAutoSlide(); // Restart auto-slide after manual selection
        });
      });

      // Initialize first dot as active
      dots[currentIndex].classList.add("bg-gray-800");

      // Start auto-slide on page load
      startAutoSlide();
    </script>



    <div class="container mx-auto px-6 py-10 relative rounded-lg shadow-lg overflow-hidden"
      style="background: url('./assets/img/lgupic.jpg') no-repeat center center/cover;">

      <!-- Dark Overlay -->
      <div class="absolute inset-0 bg-black bg-opacity-50"></div>

      <!-- Content -->
      <div class="relative z-10 text-center text-white">
        <h2 class="text-4xl font-bold mb-4">All LGU Services</h2>
        <p class="text-lg">Explore our government services with ease.</p>
      </div>

      <br>
      <!-- Services Grid -->

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 sm:gap-6 px-4 sm:px-6 md:px-10 lg:px-12 xl:px-16" id="servicesGrid">

        <?php
        // ✅ Fetch verified status from the database
        $stmt = $conn->prepare("SELECT verified FROM registerlanding WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($verified);
        $stmt->fetch();
        $stmt->close();

        // ✅ Fetch services
        $sql = "SELECT image_url, name, description, service_link FROM services";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<div class='service-item relative group bg-white p-5 rounded-xl shadow-md transition-all duration-500 ease-in-out hover:shadow-xl hover:scale-105 hover:rounded-3xl w-full flex flex-col h-full' 
                data-name='" . htmlspecialchars($row['name']) . "'>"; // ✅ Ensures uniform card height

            // 📌 Service Image
            echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='Service Image' class='w-full h-40 sm:h-48 md:h-52 object-cover rounded-lg transition-all duration-500 ease-in-out group-hover:rounded-3xl group-hover:scale-110 mb-4'>";

            // 📌 Service Title
            echo "<h3 class='text-lg sm:text-xl font-semibold text-gray-900 mb-2 transition-all duration-500 ease-in-out group-hover:text-blue-900'>" . htmlspecialchars($row['name']) . "</h3>";

            // 📌 Description with auto-scroll (Ensures uniform height)
            echo "<p class='text-gray-600 text-sm sm:text-base lg:text-lg mb-4 max-h-32 overflow-y-auto whitespace-normal break-words scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 flex-grow'>" . htmlspecialchars($row['description']) . "</p>";

            //  Button container (Ensures button is at the bottom)
            echo "<div class='mt-auto'>";

            if (!empty($row['service_link'])) {
              $auto_login_url = htmlspecialchars($row['service_link']) . "?email=" . urlencode($email) . "&session_token=" . urlencode($session_token);

              if ($verified == 1) {
                echo "<a href='" . $auto_login_url . "' target='_blank' rel='noopener noreferrer' 
                        class='block text-center text-white font-semibold bg-blue-800 hover:bg-blue-900 
                        focus:outline-none focus:ring-2 focus:ring-blue-600 transition duration-300 
                        px-4 py-2 rounded-lg w-full'>Learn More</a>"; //  Full-width button
              } else {
                echo "<a href='profile.php' class='block text-center text-white font-semibold bg-red-600 hover:bg-red-700 
                        focus:outline-none focus:ring-2 focus:ring-red-400 transition duration-300 
                        px-4 py-2 rounded-lg w-full'>Verify Account</a>";
              }
            }

            echo "</div>"; //  Close button container
            echo "</div>"; // ✅ Close service-item div
          }
        } else {
          echo "<p class='text-gray-300 text-center col-span-full'>No services found.</p>";
        }
        ?>
      </div>




      <!--  Auto-Hide Scrollbar Custom CSS -->
      <style>
        .custom-scrollbar::-webkit-scrollbar {
          width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
          background: rgba(0, 0, 0, 0.2);
          border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
          background: transparent;
        }

        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
          background: rgba(0, 0, 0, 0.4);
        }
      </style>

    </div>
  </div>
  <!-- Footer -->
  <footer class="bg-blue-900 text-white text-center py-6 mt-10 relative z-10">
    <p class="text-lg font-semibold">LGU E-Services</p>
    <p class="text-sm">For inquiries, call <span class="text-yellow-400">122</span> or email <span class="text-yellow-400">unifiedlgu@gmail.com</span></p>
    <div class="mt-4 text-sm"><a href="terms.php" class="text-yellow-400 underline">Terms of Service</a></div>
    <div class="mt-4 text-sm">
      <p>&copy; <?php echo date("Y"); ?> LGU E-Services. All rights reserved.</p>
    </div>
  </footer>

  <script>
    function updateClock() {
      const now = new Date();
      const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      };
      document.getElementById('realTimeClock').textContent = now.toLocaleString('en-US', options);
    }

    // Update every second
    setInterval(updateClock, 1000);

    // Initialize clock immediately
    updateClock();



    let idleTime = 0;
    const maxIdleTime = 7200; // 2 hour in seconds

    // ✅ Reset idle time when user interacts
    document.onmousemove = document.onkeypress = function() {
      idleTime = 0;
    };

    // ✅ Check idle time every minute
    setInterval(() => {
      idleTime++;
      if (idleTime >= maxIdleTime) {
        alert("You have been logged out due to inactivity.");
        window.location.href = 'logout.php';
      }
    }, 1000); // Check every second (1,000 ms)


    document.getElementById("searchButton").addEventListener("click", function() {
      let filter = document.getElementById("searchInput").value.toLowerCase().trim();
      let services = document.querySelectorAll(".service-item");
      let found = false;

      services.forEach(service => {
        let serviceName = service.getAttribute("data-name").toLowerCase();
        if (serviceName.includes(filter)) {
          service.style.display = "block";
          if (!found) {
            service.scrollIntoView({
              behavior: "smooth",
              block: "center"
            });
            found = true;
          }
        } else {
          service.style.display = "none";
        }
      });

      // 📌 Show notification if no services match
      let notification = document.getElementById("notification");
      if (!found && filter !== "") {
        notification.textContent = "No matching services found.";
        notification.classList.remove("hidden");
        setTimeout(() => {
          notification.classList.add("hidden");
        }, 3000);
      }
    });

    // 📌 Allow "Enter" key to trigger search
    document.getElementById("searchInput").addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("searchButton").click();
      }
    });

    //  Dropdown menu handling (unchanged)
    document.addEventListener("DOMContentLoaded", function() {
      const dropdownIcon = document.getElementById("dropdownIcon");
      const dropdownMenu = document.getElementById("dropdownMenu");

      // Toggle dropdown visibility on click
      dropdownIcon.addEventListener("click", function(event) {
        event.stopPropagation(); // Prevent accidental closing
        dropdownMenu.classList.toggle("hidden");
      });

      // Close dropdown when clicking outside
      document.addEventListener("click", function(event) {
        if (!dropdownIcon.contains(event.target) && !dropdownMenu.contains(event.target)) {
          dropdownMenu.classList.add("hidden");
        }
      });

      // Close dropdown on ESC key press
      document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
          dropdownMenu.classList.add("hidden");
        }
      });
    });
  </script>
</body>

</html>