<?php
session_start();
include('../connectiondb/connection.php'); // Ensure correct path

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['service_id'];
    $name = trim($_POST['service_name']);
    $description = trim($_POST['service_description']);
    $service_link = trim($_POST['service_link']);

    // Get current image from database
    $query = "SELECT image_url FROM services WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($old_image_url);
    $stmt->fetch();
    $stmt->close();

    // Check if a new image is uploaded
    if (!empty($_FILES["service_image"]["name"])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["service_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                $image_url = "uploads/" . $file_name;

                // Delete old image (optional)
                if (!empty($old_image_url) && file_exists($old_image_url)) {
                    unlink($old_image_url);
                }
            } else {
                $_SESSION['notification'] = "Error uploading new image.";
                $_SESSION['notification_type'] = "error";
                header("Location: ../landingmainpage.php");
                exit();
            }
        } else {
            $_SESSION['notification'] = "Invalid file type.";
            $_SESSION['notification_type'] = "error";
            header("Location: ../landingmainpage.php");
            exit();
        }
    } else {
        // No new image uploaded, keep the old one
        $image_url = $old_image_url;
    }

    // Update the service details
    $update_sql = "UPDATE services SET name = ?, description = ?, image_url = ?, service_link = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $name, $description, $image_url, $service_link, $id);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Service updated successfully!";
        $_SESSION['notification_type'] = "success";
    } else {
        $_SESSION['notification'] = "Error updating service: " . $stmt->error;
        $_SESSION['notification_type'] = "error";
    }

    $stmt->close();
    header("Location: ../landingmainpage.php");
    exit();
}
