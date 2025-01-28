<?php
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sourceGalleryId = $_POST['source_gallery'];
    $destinationGalleryId = $_POST['destination_gallery'];

    if ($sourceGalleryId === $destinationGalleryId) {
        echo "Source and destination galleries cannot be the same.";
        exit;
    }

    // Update images to the destination gallery
    $stmt = $conn->prepare("UPDATE Images SET gallery_id = ? WHERE gallery_id = ?");
    $stmt->bind_param("ii", $destinationGalleryId, $sourceGalleryId);
    if ($stmt->execute()) {
        // Delete the source gallery
        $stmtDelete = $conn->prepare("DELETE FROM Galleries WHERE id = ?");
        $stmtDelete->bind_param("i", $sourceGalleryId);
        if ($stmtDelete->execute()) {
            header("Location: gallery.php");
            exit;
        } else {
            echo "Error deleting source gallery: " . $stmtDelete->error;
        }
        $stmtDelete->close();
    } else {
        echo "Error updating images: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>