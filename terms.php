<?php
session_start();
include('connectiondb/connection.php');
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service</title>
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Fullscreen background */
        .bg-cover-full {
            background: url('assets/img/lgupic.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>

<body class="bg-cover-full min-h-screen flex items-center justify-center relative">
    <!-- Background Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <!-- Content -->
    <div class="relative z-10 bg-white bg-opacity-90 max-w-full md:max-w-4xl w-full p-6 md:p-10 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-gray-900 text-center mb-6">Terms of Service</h1>
        <p class="text-gray-700 text-sm md:text-base text-center italic mb-4">Effective Date: March 11, 2025</p>

        <p class="text-gray-700 mb-4">Welcome to <strong>LGU E-SERVICES</strong>'s official website <strong>unifiedlgu.com</strong>. By accessing or using the Website, you agree to comply with and be bound by these Terms of Service.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">1. Acceptance of Terms</h2>
        <p class="text-gray-600">By using this Website, you agree to these Terms. If you do not agree, discontinue using the Website immediately.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">2. Eligibility</h2>
        <p class="text-gray-600">You must be a resident of [LGU Name] or a legal representative thereof to access certain services.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">3. Online Services</h2>
        <ul class="list-disc list-inside text-gray-600">
            <li>Requesting public documents</li>
            <li>Paying local taxes and fees</li>
            <li>Accessing community notices</li>
            <li>Submitting service requests</li>
            <li>Participating in public consultations</li>
        </ul>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">4. User Accounts and Security</h2>
        <p class="text-gray-600">Creating an account requires accurate and up-to-date information.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">5. Prohibited Conduct</h2>
        <ul class="list-disc list-inside text-gray-600">
            <li>No unlawful use</li>
            <li>No distribution of malware</li>
            <li>No unauthorized access attempts</li>
            <li>No activities disrupting the Website’s functionality</li>
        </ul>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">6. Privacy and Data Protection</h2>
        <p class="text-gray-600">Your use of the Website implies consent to our Privacy Policy.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">7. Intellectual Property</h2>
        <p class="text-gray-600">All content on this Website is the property of Unified LGU or its licensors.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">8. Limitation of Liability</h2>
        <p class="text-gray-600">We do not guarantee uninterrupted or error-free services.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">9. Dispute Resolution</h2>
        <p class="text-gray-600">Any dispute shall be resolved through negotiations or submitted to the courts.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">10. Changes to Terms</h2>
        <p class="text-gray-600">We reserve the right to update these Terms at any time.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">11. Termination</h2>
        <p class="text-gray-600">We may suspend or terminate your access to the Website for any violations of these Terms.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">12. Governing Law</h2>
        <p class="text-gray-600">These Terms are governed by the laws of the Philippines.</p>

        <h2 class="text-lg font-semibold text-gray-800 mt-6">13. Contact Information</h2>
        <p class="text-gray-600"><strong>Unified LGU</strong><br>Quezon City, Philippines<br>Phone: 122<br>Email: <a href="mailto:unifiedlgu@gmail.com" class="text-blue-600 underline">unifiedlgu@gmail.com</a></p>

        <div class="text-center mt-6">
        <button onclick="window.location.href='landingmainpage.php'" class="bg-blue-700 text-white px-6 py-2 rounded-md hover:bg-blue-800 transition">
    Back
</button>

        </div>
    </div>
</body>
</html>
