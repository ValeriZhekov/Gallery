<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galleryName = $_POST['gallery_name'];
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO Galleries (name, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $galleryName, $userId);

    if ($stmt->execute()) {
        header("Location: gallery.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>