<?php
include 'db_config.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galleryId = $_POST['gallery_id'];

    // Ensure a gallery ID is provided
    if (empty($galleryId)) {
        die("No gallery selected.");
    }

    // Check if a file was uploaded
    if (isset($_FILES['archive']) && $_FILES['archive']['error'] === UPLOAD_ERR_OK) {
        $archivePath = $_FILES['archive']['tmp_name'];
        $uploadsDir = 'uploads/'; // Directory to store extracted images

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Extract ZIP file
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === TRUE) {
            $extractedDir = $uploadsDir . 'import_' . time() . '/';
            mkdir($extractedDir); // Create a directory for the extracted files
            $zip->extractTo($extractedDir);
            $zip->close();

            // Process each file in the extracted directory
            $files = scandir($extractedDir);
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Supported image formats
            $importedFiles = 0;

            foreach ($files as $file) {
                $filePath = $extractedDir . $file;
                $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (is_file($filePath) && in_array($fileExtension, $validExtensions)) {
                    // Generate a unique name for the image
                    $uniqueName = uniqid('img_', true) . '.' . $fileExtension;
                    $destinationPath = $uploadsDir . $uniqueName;

                    // Move the file to the uploads directory
                    if (rename($filePath, $destinationPath)) {
                        // Insert image record into the database
                        $stmt = $conn->prepare("INSERT INTO Images (file_path, gallery_id) VALUES (?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("si", $destinationPath, $galleryId);
                            $stmt->execute();
                            $stmt->close();
                            $importedFiles++;
                        } else {
                            error_log("Database insert error: " . $conn->error);
                        }
                    } else {
                        error_log("Failed to move file: $filePath");
                    }
                } else {
                    error_log("Invalid file or unsupported format: $filePath");
                }
            }

            // Cleanup: Remove the extracted directory
            array_map('unlink', glob("$extractedDir/*.*"));
            rmdir($extractedDir);

            // Redirect with success message
            header("Location: gallery.php?imported=$importedFiles");
            exit;
        } else {
            die("Failed to open the ZIP archive.");
        }
    } else {
        die("No file uploaded or an error occurred.");
    }
} else {
    die("Invalid request.");
}

$conn->close();
?>