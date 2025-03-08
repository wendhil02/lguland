<?php
include("../admin/assets/inc/header.php");
include("../admin/assets/inc/sidebar.php");
include("../admin/assets/inc/navbar.php");
include("../admin/assets/inc/notif_header.php");
?>

<style>
  /* Base styles for the body and container */
  body {
    font-family: "Inter", sans-serif;
    background-color: #f4f7fc;
    color: #040505;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  h1 {
    color: var(--primary);
    text-align: center;
    margin-bottom: 20px;
  }

  /* Section Containers */
  .section-container {
    margin-top: 30px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .section-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 20px;
  }

  /* Form Group Styling */
  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
  }

  /* Input Styling */
  .form-control {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    outline: none;
    border-color: var(--primary);
    background-color: #fff;
  }

  /* Submit Button */
  .btn-submit {
    background-color: var(--primary);
    color: white;
    padding: 12px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
  }

  .btn-submit:hover {
    background-color: var(--indigo);
    color: white;
  }

  /* Filter Section */
  .filter-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .filter-field {
    margin-bottom: 15px;
    flex: 1 0 22%;
  }

  .filter-input {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
  }

  .filter-btn {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .filter-btn:hover {
    background-color: var(--indigo);
  }

  /* Buttons Styling */
  .btn {
    padding: 0.75rem 1.25rem;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-exit {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
  }

  .btn-exit:hover {
    background-color: #5a6268;
    border-color: #545b62;
    color: white;
  }

  .btn-edit {
    background-color: var(--primary);
    border-color: var(--primary);
    color: white;
  }

  .btn-edit:hover {
    background-color: var(--blue);
    border-color: var(--blue);
    color: white;
  }

  .btn-deactivate {
    background-color: var(--danger);
    border-color: var(--danger);
    color: white;
  }

  .btn-deactivate:hover {
    background-color: var(--red);
    border-color: var(--red);
    color: white;
  }

  /* Table Styles */
  .user-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  .user-table th,
  .user-table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
  }

  .user-table th {
    background-color: var(--primary);
    color: white;
  }

  .user-table tr:hover {
    background-color: #f5f5f5;
  }

  /* Mobile Responsiveness */
  @media (max-width: 768px) {
    .form-group input {
      font-size: 0.95rem;
    }

    .btn-submit {
      font-size: 1.1rem;
    }

    .filter-field {
      flex: 1 0 48%; /* Adjust for smaller screen */
    }

    .btn-submit {
      font-size: 1rem;
    }

    .filter-field {
      margin-bottom: 10px;
    }
  }

  @media (max-width: 480px) {
    .filter-field {
      flex: 1 0 100%; /* Stack inputs vertically on very small screens */
    }

    .user-table td,
    .user-table th {
      font-size: 0.9rem; /* Adjust font size for better readability */
    }
  }

  /* Flex-based layout for form fields */
  label {
    flex: 1 0 30%;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    text-align: right;
    padding-right: 10px;
  }

  input[type="text"],
  input[type="email"],
  select {
    flex: 1 0 60%;
    padding: 0.75rem;
    margin-bottom: 0;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
    color: #555;
  }

  input[type="text"]:read-only,
  input[type="email"]:read-only,
  select:disabled {
    background-color: #eee;
    cursor: not-allowed;
  }
</style>


<div class="loader-mask">
  <div class="loader">
    <div></div>
    <div></div>
  </div>
</div>

<body class="vertical light">
  <div class="wrapper">
    <div class="main-content">
      <!-- User Management Section -->
      <div id="user-management" class="section">
        <h3 class="section-title">User Management</h3>

        <!-- Add User Section -->
        <div class="form-group section-container">
          <h4>Add New User</h4>
          <div class="form-group mt-4">
            <label for="first_name">* First Name:</label>
            <input type="text" id="first_name" class="filter-input" placeholder="Enter First Name">
          </div>
          <div class="form-group mt-4">
            <label for="last_name">* Last Name:</label>
            <input type="text" id="last_name" class="filter-input" placeholder="Enter Last Name">
          </div>
          <div class="form-group mt-4">
            <label for="email">* Email:</label>
            <input type="email" id="email" class="filter-input" placeholder="Enter Email">
          </div>
          <div class="form-group mt-4">
            <label for="mobile">* Mobile Number:</label>
            <input type="text" id="mobile" class="filter-input" placeholder="Enter Mobile Number">
          </div>
          <div class="form-group mt-4">
            <button type="button" class="btn btn-submit" onclick="addUser()">Add User</button>
          </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
          <!-- Filter by First Name -->
          <div class="filter-field">
            <label for="filter-first-name">First Name:</label>
            <input type="text" id="filter-first-name" class="filter-input" placeholder="Filter by First Name">
          </div>

          <!-- Filter by Last Name -->
          <div class="filter-field">
            <label for="filter-last-name">Last Name:</label>
            <input type="text" id="filter-last-name" class="filter-input" placeholder="Filter by Last Name">
          </div>

          <!-- Filter by Email -->
          <div class="filter-field">
            <label for="filter-email">Email:</label>
            <input type="text" id="filter-email" class="filter-input" placeholder="Filter by Email">
          </div>

          <!-- Filter by Mobile Number -->
          <div class="filter-field">
            <label for="filter-mobile">Mobile Number:</label>
            <input type="text" id="filter-mobile" class="filter-input" placeholder="Filter by Mobile Number">
          </div>

          <!-- Filter Button -->
          <div>
            <button type="button" class="filter-btn" onclick="filterUsers()">Filter</button>
          </div>
        </div>

        <!-- User List Section -->
        <div class="section-container">
          <h4>User List</h4>
          <table class="user-table" id="user-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="user-list">
              <!-- Dynamic rows will be added here -->
            </tbody>
          </table>
        </div>


      </div>
    </div>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/moment.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/simplebar.min.js"></script>
    <script src='js/daterangepicker.js'></script>
    <script src='js/jquery.stickOnScroll.js'></script>
    <script src="js/tinycolor-min.js"></script>
    <script src="js/d3.min.js"></script>
    <script src="js/topojson.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/gauge.min.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/apexcharts.min.js"></script>
    <script src="js/apexcharts.custom.js"></script>
    <script src='js/jquery.mask.min.js'></script>
    <script src='js/select2.min.js'></script>
    <script src='js/jquery.steps.min.js'></script>
    <script src='js/jquery.validate.min.js'></script>
    <script src='js/jquery.timepicker.js'></script>
    <script src='js/dropzone.min.js'></script>
    <script src='js/uppy.min.js'></script>
    <script src='js/quill.min.js'></script>
    <script src="js/apps.js"></script>
    <script src="js/preloader.js"></script>
    <script src="js/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src='js/jquery.dataTables.min.js'></script>
    <script src='js/dataTables.bootstrap4.min.js'></script>

    <script>
      let userCount = 0;

      // Function to add a new user
      function addUser() {
        const firstName = document.querySelector('#first_name').value;
        const lastName = document.querySelector('#last_name').value;
        const email = document.querySelector('#email').value;
        const mobile = document.querySelector('#mobile').value;

        if (firstName && lastName && email && mobile) {
          userCount++;
          const userRow = `
                    <tr id="user-${userCount}">
                        <td>${userCount}</td>
                        <td>${firstName}</td>
                        <td>${lastName}</td>
                        <td>${email}</td>
                        <td>${mobile}</td>
                        <td>
                            <button type="button" class="btn btn-danger" onclick="removeUser(${userCount})">Remove</button>
                        </td>
                    </tr>
                    `;
          document.querySelector('#user-list').insertAdjacentHTML('beforeend', userRow);

          // Clear form fields
          document.querySelector('#first_name').value = '';
          document.querySelector('#last_name').value = '';
          document.querySelector('#email').value = '';
          document.querySelector('#mobile').value = '';
        } else {
          alert('Please fill in all fields!');
        }
      }

      // Function to remove a user
      function removeUser(userId) {
        const userRow = document.getElementById(`user-${userId}`);
        if (userRow) {
          userRow.remove();
        }
      }

      // Function to filter users based on multiple fields
    function filterUsers() {
        const firstNameFilter = document.querySelector('#filter-first-name').value.toLowerCase();
        const lastNameFilter = document.querySelector('#filter-last-name').value.toLowerCase();
        const emailFilter = document.querySelector('#filter-email').value.toLowerCase();
        const mobileFilter = document.querySelector('#filter-mobile').value.toLowerCase();

        const rows = document.querySelectorAll('#user-list tr');

        rows.forEach(row => {
            const firstName = row.cells[1].innerText.toLowerCase();
            const lastName = row.cells[2].innerText.toLowerCase();
            const email = row.cells[3].innerText.toLowerCase();
            const mobile = row.cells[4].innerText.toLowerCase();

            // Check if the row matches all filter criteria
            if (
                (firstName.includes(firstNameFilter) || firstNameFilter === '') &&
                (lastName.includes(lastNameFilter) || lastNameFilter === '') &&
                (email.includes(emailFilter) || emailFilter === '') &&
                (mobile.includes(mobileFilter) || mobileFilter === '')
            ) {
                row.style.display = ''; // Show row if it matches filter criteria
            } else {
                row.style.display = 'none'; // Hide row if it doesn't match
            }
        });
    }
    </script>
</body>

</html>