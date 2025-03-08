<?php
include("../admin/assets/inc/header.php");
include("../admin/assets/inc/sidebar.php");
include("../admin/assets/inc/navbar.php");
include("../admin/assets/inc/notif_header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<style>
    /* Enhanced UI with animated section separators and hover effects */

    /* Section Container with hover effect */
    .section-container {
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        background-color: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .section-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    /* Section Title with hover underline animation */
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        text-align: center;
        margin-bottom: 20px;
    }

    h4 {
        font-size: 1.2rem;
        color: var(--primary);
        font-weight: 600;
        position: relative;
        padding-bottom: 8px;
    }

    h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--pink);
        transform: scaleX(0);
        transform-origin: bottom right;
        transition: transform 0.3s ease;
    }

    h4:hover::after {
        transform: scaleX(1);
        transform-origin: bottom left;
    }

    /* Section Separator (animated) */
    .section-separator {
        width: 50%;
        border-top: 4px solid var(--primary);
        margin: 20px auto;
        animation: slideIn 1s ease-in-out;
    }

    @keyframes slideIn {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(0);
        }
    }

    /* Form and Button Styles */
    .form-control {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ddd;
        background-color: #fff;
    }

    .btn {
        padding: 10px 20px;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: var(--indigo);
    }

    .btn-danger {
        background-color: var(--danger);
    }

    .btn-danger:hover {
        background-color: var(--red);
    }

    .btn-primary {
        background-color: var(--success);
    }

    .btn-primary:hover {
        background-color: var(--teal);
    }

    .btn-remove-carousel,
    .btn-remove-service {
        margin-top: 10px;
    }

    /* Image Preview Styles */
    .image-preview img {
        width: 100%;
        height: auto;
    }

    /* Hover Effect for Section Titles */
    h4:hover {
        cursor: pointer;
        color: var(--indigo);
    }

    /* Center file input button inside input field */
    input[type="file"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 15px;
        /* Space for top and bottom */
        border-radius: 5px;
        border: 1px solid #ddd;
        background-color: white;
        width: 100%;
        /* Ensure the file input takes up the full width */
        height: auto;
    }

    /* Make the button look like the input field */
    input[type="file"]::-webkit-file-upload-button {
        padding: 8px 15px;
        /* Space inside the button */
        border-radius: 5px;
        border: none;
        background-color: var(--primary);
        color: white;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
    }

    /* When hovering over the button, change the background color */
    input[type="file"]::-webkit-file-upload-button:hover {
        background-color: var(--indigo);
    }

    /* For non-webkit browsers (like Firefox), ensure button appearance */
    input[type="file"]::-moz-file-upload-button {
        padding: 8px 15px;
        /* Space inside the button */
        border-radius: 5px;
        border: none;
        background-color: var(--primary);
        color: white;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
    }

    input[type="file"]::-moz-file-upload-button:hover {
        background-color: var(--indigo);
    }

    /* Small Image Preview Style */
    .image-preview-small {
        margin-top: 10px;
        margin-bottom: 10px;
        max-width: 150px;
        max-height: 100px;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-preview-small img {
        width: 100%;
        height: auto;
    }

    /* Optional: Style the input field for better alignment */
    input[type="file"] {
        margin-bottom: 10px;
    }
</style>
</head>
<div class="loader-mask">
    <div class="loader">
        <div></div>
        <div></div>
    </div>
</div>


<body class="vertical  light">
    <div class="wrapper">
        <!-- Main content container -->
        <div class="main-content">
            <!-- Landing Page Management Section -->
            <div id="landing-page-management" class="section">
                <h3 class="section-title">Landing Page Content Management</h3>

                <!-- Welcome Section Management -->
                <div class="form-group section-container">
                    <h4>Welcome Section Management</h4>
                    <label for="welcome-bg">Change Welcome Section Background:</label>
                    <input type="file" id="welcome-bg" accept="image/*" onchange="previewWelcomeImage(event)" class="form-control">
                    <div id="welcome-image-preview" class="image-preview mt-2">
                        <img src="#" alt="Welcome Section Background Preview" style="display: none; width: 100%; height: auto;">
                    </div>
                    <div class="form-group mt-4">
                        <label for="welcome-text">Update Welcome Text:</label>
                        <input type="text" id="welcome-text" class="form-control" placeholder="Enter Welcome Text Here" value="WELCOME TO">
                        <input type="text" id="welcome-subtext" class="form-control mt-2" placeholder="Enter Subtext Here" value="LGU E-SERVICES">
                    </div>
                </div>

                <!-- Animated Separator -->
                <hr class="section-separator">

                <!-- Carousel Management -->
                <div class="form-group section-container">
                    <h4>Carousel Management</h4>
                    <label for="carousel-upload">Change Carousel Images and Text:</label>
                    <div id="carousel-items">
                        <!-- Carousel items will be dynamically added here -->
                        <div class="carousel-item-group" id="carousel-item-1-group">
                            <label for="carousel-item-1-image">Carousel Item 1 Image:</label>
                            <input type="file" id="carousel-item-1-image" accept="image/*" class="form-control carousel-image-input" data-item-id="1" onchange="previewCarouselImage(this)">
                            <div class="image-preview-small" id="carousel-item-1-preview" style="display: none;">
                                <img src="#" alt="Carousel Item 1 Preview" class="img-thumbnail">
                            </div>
                            <input type="text" id="carousel-item-1-title" class="form-control mt-2" placeholder="Enter Title" value="QC VAX EASY">
                            <input type="text" id="carousel-item-1-desc" class="form-control mt-2" placeholder="Enter Description" value="Get vaccinated easily.">
                            <button type="button" class="btn btn-danger mt-2" onclick="removeCarouselItem(1)">Remove Item</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="addCarouselItem()">Add New Carousel Item</button>
                </div>

                <!-- Animated Separator -->
                <hr class="section-separator">

                <!-- Service Management -->
                <div class="form-group section-container">
                    <h4>Service Management</h4>
                    <label for="service-section">Manage Services:</label>
                    <div id="services-list">
                        <!-- Service items will be dynamically added here -->
                        <div class="service-item-group" id="service-item-1-group">
                            <label for="service-1-image">Service 1 Image:</label>
                            <input type="file" id="service-1-image" class="form-control mt-2 service-image-input" data-service-id="1" accept="image/*" onchange="previewServiceImage(this)">
                            <div class="image-preview-small" id="service-1-preview" style="display: none;">
                                <img src="#" alt="Service 1 Preview" class="img-thumbnail">
                            </div>
                            <input type="text" id="service-1-title" placeholder="Service Title" class="form-control" value="QC VAX EASY">
                            <input type="text" id="service-1-desc" placeholder="Service Description" class="form-control mt-2" value="Vaccination made easy and accessible.">
                            <input type="url" id="service-1-link" placeholder="Service Link" class="form-control mt-2" value="#">
                            <button type="button" class="btn btn-danger mt-2" onclick="removeService(1)">Remove Service</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="addServiceItem()">Add New Service</button>
                </div>

                <!-- Animated Separator -->
                <hr class="section-separator">

                <!-- Save Changes Button -->
                <button type="submit" class="btn btn-success mt-4">Save Changes</button>
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
            let carouselItemCount = 1;
            let serviceCount = 1;

            // Function to preview the welcome image
            function previewWelcomeImage(event) {
                const file = event.target.files[0];
                const preview = document.querySelector('#welcome-image-preview img');

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function() {
                        preview.src = reader.result;
                        preview.style.display = "block";
                    }

                    reader.readAsDataURL(file);
                } else {
                    preview.src = "#";
                    preview.style.display = "none";
                }
            }

            // Function to preview carousel images
            function previewCarouselImage(input) {
                const itemId = input.dataset.itemId;
                const previewId = `carousel-item-${itemId}-preview`;
                const previewContainer = document.getElementById(previewId);
                const imagePreview = previewContainer.querySelector('img');
                const file = input.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = '#';
                    previewContainer.style.display = 'none';
                }
            }

            // Function to preview service images
            function previewServiceImage(input) {
                const serviceId = input.dataset.serviceId;
                const previewId = `service-${serviceId}-preview`;
                const previewContainer = document.getElementById(previewId);
                const imagePreview = previewContainer.querySelector('img');
                const file = input.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = '#';
                    previewContainer.style.display = 'none';
                }
            }

            // Function to add new carousel item
            function addCarouselItem() {
                carouselItemCount++;
                const newCarouselItem = `
                <div class="carousel-item-group" id="carousel-item-${carouselItemCount}-group">
                    <label for="carousel-item-${carouselItemCount}-image">Carousel Item ${carouselItemCount} Image:</label>
                    <input type="file" id="carousel-item-${carouselItemCount}-image" accept="image/*" class="form-control carousel-image-input" data-item-id="${carouselItemCount}" onchange="previewCarouselImage(this)">
                    <div class="image-preview-small" id="carousel-item-${carouselItemCount}-preview" style="display: none;">
                        <img src="#" alt="Carousel Item ${carouselItemCount} Preview" class="img-thumbnail">
                    </div>
                    <input type="text" id="carousel-item-${carouselItemCount}-title" class="form-control mt-2" placeholder="Enter Title">
                    <input type="text" id="carousel-item-${carouselItemCount}-desc" class="form-control mt-2" placeholder="Enter Description">
                    <button type="button" class="btn btn-danger mt-2" onclick="removeCarouselItem(${carouselItemCount})">Remove Item</button>
                </div>
            `;
                document.getElementById('carousel-items').insertAdjacentHTML('beforeend', newCarouselItem);
            }

            // Function to add new service item
            function addServiceItem() {
                serviceCount++;
                const newServiceItem = `
                <div class="service-item-group" id="service-item-${serviceCount}-group">
                    <label for="service-${serviceCount}-image">Service ${serviceCount} Image:</label>
                    <input type="file" id="service-${serviceCount}-image" class="form-control mt-2 service-image-input" data-service-id="${serviceCount}" accept="image/*" onchange="previewServiceImage(this)">
                    <div class="image-preview-small" id="service-${serviceCount}-preview" style="display: none;">
                        <img src="#" alt="Service ${serviceCount} Preview" class="img-thumbnail">
                    </div>
                    <input type="text" id="service-${serviceCount}-title" placeholder="Service Title" class="form-control">
                    <input type="text" id="service-${serviceCount}-desc" placeholder="Service Description" class="form-control mt-2">
                    <input type="url" id="service-${serviceCount}-link" placeholder="Service Link" class="form-control mt-2">
                    <button type="button" class="btn btn-danger mt-2" onclick="removeService(${serviceCount})">Remove Service</button>
                </div>
            `;
                document.getElementById('services-list').insertAdjacentHTML('beforeend', newServiceItem);
            }

            // Function to remove carousel item
            function removeCarouselItem(itemId) {
                const itemToRemove = document.getElementById(`carousel-item-${itemId}-group`);
                if (itemToRemove) {
                    itemToRemove.remove();
                }
            }

            // Function to remove service item
            function removeService(serviceId) {
                const serviceToRemove = document.getElementById(`service-item-${serviceId}-group`);
                if (serviceToRemove) {
                    serviceToRemove.remove();
                }
            }

            // Form submission
            document.querySelector('#landing-page-management').addEventListener('submit', function(event) {
                event.preventDefault();
                // Collect all the data from the form
                const welcomeBg = document.querySelector('#welcome-bg').files[0];
                const welcomeText = document.querySelector('#welcome-text').value;
                const welcomeSubtext = document.querySelector('#welcome-subtext').value;

                const carouselItems = [];
                document.querySelectorAll('.carousel-item-group').forEach(item => {
                    const itemId = item.id.split('-')[2];
                    carouselItems.push({
                        image: document.querySelector(`#carousel-item-${itemId}-image`).files[0],
                        title: document.querySelector(`#carousel-item-${itemId}-title`).value,
                        desc: document.querySelector(`#carousel-item-${itemId}-desc`).value
                    });
                });

                const serviceItems = [];
                document.querySelectorAll('.service-item-group').forEach(item => {
                    const serviceId = item.id.split('-')[2];
                    serviceItems.push({
                        image: document.querySelector(`#service-${serviceId}-image`).files[0],
                        title: document.querySelector(`#service-${serviceId}-title`).value,
                        desc: document.querySelector(`#service-${serviceId}-desc`).value,
                        link: document.querySelector(`#service-${serviceId}-link`).value
                    });
                });


                const formData = new FormData();
                formData.append('welcomeBg', welcomeBg);
                formData.append('welcomeText', welcomeText);
                formData.append('welcomeSubtext', welcomeSubtext);
                formData.append('carouselItems', JSON.stringify(carouselItems));
                formData.append('serviceItems', JSON.stringify(serviceItems));

                fetch('your-api-endpoint', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert("Landing Page Content Updated!");
                        console.log('Success:', data);
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                alert("Landing Page Content Updated!");
            });
        </script>
</body>

</html>