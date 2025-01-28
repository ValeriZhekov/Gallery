<?php
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galleryId = $_POST['gallery_id'];
    $image = $_FILES['image'];

    // Save the uploaded file
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($image["name"]);

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO Images (file_path, gallery_id) VALUES (?, ?)");
        $stmt->bind_param("si", $targetFile, $galleryId);

        if ($stmt->execute()) {
            header("Location: gallery.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading the file.";
    }

    $conn->close();
}
?>