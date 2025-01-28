<?php
include 'db_config.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cameraModel = $_POST['camera_model'] ?? '';
    $yearFrom = $_POST['year_from'] ?? null;
    $yearTo = $_POST['year_to'] ?? null;

    // Fetch all images for the current user's galleries
    $stmt = $conn->prepare("
        SELECT Images.file_path 
        FROM Images 
        INNER JOIN Galleries ON Images.gallery_id = Galleries.id 
        WHERE Galleries.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Create a ZIP archive
        $zip = new ZipArchive();
        $zipFileName = 'exported_images_' . time() . '.zip';
        $zipFilePath = 'exports/' . $zipFileName;

        // Ensure the exports directory exists
        if (!is_dir('exports')) {
            mkdir('exports', 0777, true);
        }

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            $addedFiles = 0;

            while ($row = $result->fetch_assoc()) {
                $filePath = $row['file_path'];

                if (file_exists($filePath)) {
                    // Extract EXIF metadata from the file
                    $exif = @exif_read_data($filePath);

                    // Skip if metadata is missing
                    if (empty($exif['Model']) || empty($exif['DateTimeOriginal'])) {
                        continue;
                    }

                    $imageCameraModel = $exif['Model'];
                    $imageDateTaken = DateTime::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);

                    // Filter by camera model and year range
                    $matchesCameraModel = empty($cameraModel) || stripos($imageCameraModel, $cameraModel) !== false;
                    $matchesYearRange = true;

                    if ($yearFrom && $imageDateTaken) {
                        $matchesYearRange = $matchesYearRange && ($imageDateTaken->format('Y') >= $yearFrom);
                    }

                    if ($yearTo && $imageDateTaken) {
                        $matchesYearRange = $matchesYearRange && ($imageDateTaken->format('Y') <= $yearTo);
                    }

                    // Add the file to the ZIP if it matches
                    if ($matchesCameraModel && $matchesYearRange) {
                        $zip->addFile($filePath, basename($filePath));
                        $addedFiles++;
                    }
                }
            }

            $zip->close();

            if ($addedFiles > 0) {
                // Offer the ZIP file for download
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipFilePath) . '"');
                header('Content-Length: ' . filesize($zipFilePath));
                readfile($zipFilePath);

                // Cleanup the ZIP file after download
                unlink($zipFilePath);
                exit;
            } else {
                unlink($zipFilePath); // Cleanup empty ZIP
                die("No images match the given criteria.");
            }
        } else {
            die("Failed to create ZIP archive.");
        }
    } else {
        die("No images found in your galleries.");
    }
} else {
    die("Invalid request.");
}

$conn->close();
?>