<?php
session_start();
include '../connectiondb/connection.php'; // Database connection

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="relative">

    <div class="content">
        <header class="bg-blue-800 text-white py-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center px-6">
                <div class="text-white text-sm hidden sm:block">
                    <?php echo date("l, F j Y , h:i:s A"); ?>
                </div>
                <div class="flex items-center space-x-3">
                    <img src="../assets/img/logo.jpg" alt="QCe Logo" class="h-12 rounded-full border-2 border-yellow-400">
                    <h1 class="text-lg font-bold text-white">LGU E-SERVICES</h1>
                </div>


                <div class="relative flex items-center space-x-3">
                <a href="logoutsuperadmin.php" class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700">Logout</a>

                    <span class="text-white text-sm hidden sm:block">
                        Logged in as:
                        <a href="usermng.php" class="font-semibold underline">
                            <?php
                            if (isset($_SESSION['first_name']) && isset($_SESSION['last_name']) && isset($_SESSION['role'])) {
                                // Check if the user is a super admin
                                if ($_SESSION['role'] === 'super admin') {
                                    echo 'Super Admin: ' . htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
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
                            <i class="fas fa-tools"></i>
                            <span>Services Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="usermng.php" class="block py-2 px-4 hover:bg-yellow-500 rounded flex items-center space-x-2">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="block py-2 px-4 hover:bg-yellow-500 rounded flex items-center space-x-2">
                            <i class="fas fa-cogs"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-6 space-y-6">
                <!-- Sidebar Toggle Button -->
                <div class="flex justify-start">
                    <button id="toggleSidebar" class="bg-blue-600 text-white p-3 rounded hover:bg-blue-700 transition-all">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <!-- User Credentials Table -->
                <div class="bg-white shadow-lg rounded-lg p-6 border">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">User Credentials</h2>

                    <!-- 🔍 Search Bar -->
                    <div class="mb-3 flex items-center space-x-2 bg-gray-100 p-1.5 rounded-md shadow-sm w-72">
                        <i class="fas fa-search text-gray-500 text-sm"></i>
                        <input type="text" id="searchInput" placeholder="Search email..."
                            class="w-full px-3 py-1 text-gray-700 border-none focus:outline-none focus:ring-0 placeholder-gray-500 text-sm">
                    </div>

                    <!-- ✅ Filter for Verified/Not Verified -->
                    <div class="mb-3">
                        <label class="text-gray-700 text-sm font-semibold">Filter by Verification:</label>
                        <select id="verificationFilter" class="ml-2 p-1 border rounded-md text-gray-700 text-sm">
                            <option value="all">All</option>
                            <option value="verified">Verified</option>
                            <option value="not_verified">Not Verified</option>
                        </select>
                    </div>
                    <!-- 🖨 Export Buttons -->
                    <div class="mb-3 flex space-x-2">
                        <button id="exportPDF" class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700">Export PDF</button>
                        <button id="exportCSV" class="px-4 py-2 bg-green-600 text-white rounded-md shadow hover:bg-green-700">Export CSV</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                            <thead class="bg-blue-800 text-white uppercase text-sm leading-normal">
                                <tr>
                                    <th class="py-3 px-6 text-center">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th class="py-3 px-6 text-left">Email</th>
                                    <th class="py-3 px-6 text-left">Password</th>
                                    <th class="py-3 px-6 text-left">Role</th> <!-- Added Role column -->
                                    <th class="py-3 px-6 text-center">Verified</th>
                                    <th class="py-3 px-6 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTable" class="text-gray-700 text-sm font-medium">
                                <?php
                                // Updated query to include role
                                $query = "SELECT id, email, password, role, verified FROM registerlanding";
                                $result = $conn->query($query);

                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                                        <td class="py-3 px-6 text-center">
                                            <input type="checkbox" class="rowCheckbox" data-id="<?php echo $row['id']; ?>">
                                        </td>
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="py-3 px-6">
                                            <span class="password" data-password="<?php echo htmlspecialchars($row['password']); ?>">••••••••</span>
                                        </td>
                                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['role']); ?></td> <!-- Displaying the role -->
                                        <td class="py-3 px-6 text-center">
                                            <?php echo $row['verified'] ? '✅ Verified' : '❌ Not Verified'; ?>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <button class="view-user text-blue-600 hover:underline mr-3" data-id="<?php echo $row['id']; ?>">View</button>
                                            <a href="update_user.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:underline">Update</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- ✅ JavaScript for Selection & Export -->
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const searchInput = document.getElementById("searchInput");
                        const verificationFilter = document.getElementById("verificationFilter");
                        const userTableRows = document.querySelectorAll("#userTable tr");

                        function filterTable() {
                            const searchValue = searchInput.value.toLowerCase();
                            const filterValue = verificationFilter.value;

                            userTableRows.forEach(row => {
                                const email = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
                                const verified = row.querySelector("td:nth-child(4)").textContent.includes("✅");

                                let matchesSearch = email.includes(searchValue);
                                let matchesFilter =
                                    (filterValue === "all") ||
                                    (filterValue === "verified" && verified) ||
                                    (filterValue === "not_verified" && !verified);

                                row.style.display = (matchesSearch && matchesFilter) ? "" : "none";
                            });
                        }

                        searchInput.addEventListener("input", filterTable);
                        verificationFilter.addEventListener("change", filterTable);
                    });

                    function exportData(type) {
                        let selectedIds = [];

                        // Kunin ang lahat ng naka-check na row
                        document.querySelectorAll(".rowCheckbox:checked").forEach(checkbox => {
                            selectedIds.push(checkbox.getAttribute("data-id"));
                        });

                        // Debugging: Tingnan kung may laman ang selected IDs
                        console.log("Selected IDs:", selectedIds); // ⬅ DITO MO ILAGAY

                        if (selectedIds.length === 0) {
                            alert("Please select at least one user to export.");
                            return;
                        }

                        let form = document.createElement("form");
                        form.method = "POST";
                        form.action = type === "pdf" ? "export_pdf.php" : "export_csv.php";

                        let input = document.createElement("input");
                        input.type = "hidden";
                        input.name = "selectedIds";
                        input.value = JSON.stringify(selectedIds);

                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    }


                    document.addEventListener("DOMContentLoaded", function() {
                        const selectAllCheckbox = document.getElementById("selectAll");
                        const rowCheckboxes = document.querySelectorAll(".rowCheckbox");
                        const exportPDFButton = document.getElementById("exportPDF");
                        const exportCSVButton = document.getElementById("exportCSV");

                        // ✅ Select All Functionality
                        selectAllCheckbox.addEventListener("change", function() {
                            rowCheckboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                                toggleRowHighlight(checkbox);
                            });
                        });

                        // ✅ Individual Row Selection
                        rowCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener("change", function() {
                                toggleRowHighlight(this);
                                selectAllCheckbox.checked = [...rowCheckboxes].every(cb => cb.checked);
                            });
                        });

                        // ✅ Function to Highlight Selected Rows
                        function toggleRowHighlight(checkbox) {
                            const row = checkbox.closest("tr");
                            row.style.backgroundColor = checkbox.checked ? "#D1FAE5" : "";
                        }

                        // ✅ Export Functionality
                        function exportData(type) {
                            let selectedIds = [];

                            document.querySelectorAll(".rowCheckbox:checked").forEach(checkbox => {
                                selectedIds.push(checkbox.getAttribute("data-id"));
                            });

                            console.log("Selected IDs:", selectedIds); // ✅ Debugging

                            if (selectedIds.length === 0) {
                                alert("Please select at least one user to export.");
                                return;
                            }

                            // ✅ Send IDs via Form POST
                            let form = document.createElement("form");
                            form.method = "POST";
                            form.action = type === "pdf" ? "export_pdf.php" : "export_csv.php";

                            let input = document.createElement("input");
                            input.type = "hidden";
                            input.name = "selectedIds";
                            input.value = JSON.stringify(selectedIds); // ✅ Convert to JSON string

                            form.appendChild(input);
                            document.body.appendChild(form);
                            form.submit();
                        }

                        // ✅ Attach event listeners
                        document.getElementById("exportPDF").addEventListener("click", () => exportData("pdf"));
                        document.getElementById("exportCSV").addEventListener("click", () => exportData("csv"));


                    });
                </script>

                <!-- View User Modal -->
                <div id="viewUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">User Details</h2>
                        <div id="userDetails" class="space-y-2 text-gray-700"></div>

                        <!-- Export Buttons -->
                        <div class="mt-4 flex justify-between">
                            <button id="exportPdf" class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Export PDF</button>
                            <button id="exportCsv" class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">Export CSV</button>
                        </div>

                        <button id="closeModal" class="mt-4 w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">Close</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("#userTable tr");

            rows.forEach(row => {
                let emailCell = row.cells[1]; // ✅ Email column (index 1, dahil may checkbox sa index 0)
                if (emailCell) {
                    let email = emailCell.textContent.toLowerCase();
                    row.style.display = email.includes(filter) ? "" : "none";
                }
            });
        });


        document.addEventListener("DOMContentLoaded", function() {
            const selectAllCheckbox = document.getElementById("selectAll");
            const rowCheckboxes = document.querySelectorAll(".rowCheckbox");
            const exportPDFButton = document.getElementById("exportPDF");
            const exportCSVButton = document.getElementById("exportCSV");
            const exportPdfModal = document.getElementById("exportPdf");
            const exportCsvModal = document.getElementById("exportCsv");
            const viewUserModal = document.getElementById("viewUserModal");
            const closeModal = document.getElementById("closeModal");

            let selectedIds = []; // Para sa multiple user selection
            let currentUserId = null; // Para sa single user export (view modal)

            // ✅ Handle "Select All" Functionality
            selectAllCheckbox.addEventListener("change", function() {
                selectedIds = []; // Clear previous selections
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    toggleRowHighlight(checkbox);
                    if (checkbox.checked) {
                        selectedIds.push(checkbox.getAttribute("data-id"));
                    }
                });
                console.log("Selected IDs:", selectedIds); // Debug
            });

            // ✅ Handle Individual Checkbox Selection
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    toggleRowHighlight(this);
                    if (this.checked) {
                        selectedIds.push(this.getAttribute("data-id"));
                    } else {
                        selectedIds = selectedIds.filter(id => id !== this.getAttribute("data-id"));
                    }
                    selectAllCheckbox.checked = [...rowCheckboxes].every(cb => cb.checked);
                    console.log("Selected IDs:", selectedIds); // Debug
                });
            });

            // ✅ Function to Highlight Selected Rows
            function toggleRowHighlight(checkbox) {
                const row = checkbox.closest("tr");
                row.style.backgroundColor = checkbox.checked ? "#D1FAE5" : "";
            }

            // ✅ Handle "View User" Click
            document.querySelectorAll(".view-user").forEach(button => {
                button.addEventListener("click", function() {
                    currentUserId = this.getAttribute("data-id"); // Get selected user ID

                    fetch(`fetch_user.php?id=${currentUserId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                            } else {
                                document.getElementById("userDetails").innerHTML = `
                            <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Mobile:</strong> ${data.mobile}</p>
                            <p><strong>City:</strong> ${data.city}</p>
                            <p><strong>Occupation:</strong> ${data.occupation}</p>
                        `;
                                viewUserModal.classList.remove("hidden");
                            }
                        });
                });
            });

            // ✅ Handle Export Functionality
            function exportData(type, isSingleUser = false) {
                let exportIds = isSingleUser ? [currentUserId] : selectedIds;

                if (exportIds.length === 0 || !exportIds[0]) {
                    alert("Please select at least one user.");
                    return;
                }

                let form = document.createElement("form");
                form.method = "POST";
                form.action = type === "pdf" ? "export_pdf.php" : "export_csv.php";

                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "selectedIds";
                input.value = JSON.stringify(exportIds);

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }

            // ✅ Export for Selected Users (Checkbox)
            exportPDFButton.addEventListener("click", () => exportData("pdf"));
            exportCSVButton.addEventListener("click", () => exportData("csv"));

            // ✅ Export for Single User (View Modal)
            exportPdfModal.addEventListener("click", () => exportData("pdf", true));
            exportCsvModal.addEventListener("click", () => exportData("csv", true));

            // ✅ Close Modal
            closeModal.addEventListener("click", () => {
                viewUserModal.classList.add("hidden");
                currentUserId = null;
            });
        });

        // Export to PDF
        document.getElementById("exportPdf").addEventListener("click", function() {
            const userId = this.getAttribute("data-id");
            window.location.href = `export_pdf.php?id=${userId}`;
        });

        // Export to CSV
        document.getElementById("exportCsv").addEventListener("click", function() {
            const userId = this.getAttribute("data-id");
            window.location.href = `export_csv.php?id=${userId}`;
        });


        // Toggle password visibility
        document.querySelectorAll('.view-password').forEach(button => {
            button.addEventListener('click', function() {
                let passwordSpan = this.parentElement.previousElementSibling.querySelector('.password');
                if (passwordSpan.textContent === '••••••••') {
                    passwordSpan.textContent = passwordSpan.getAttribute('data-password');
                    this.textContent = "Hide";
                } else {
                    passwordSpan.textContent = '••••••••';
                    this.textContent = "View";
                }
            });
        });

        // Sidebar Toggle
        document.getElementById("toggleSidebar").addEventListener("click", function() {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("hidden");

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

    <?php $conn->close(); ?> <!-- ✅ Connection closed properly -->
</body>

</html>