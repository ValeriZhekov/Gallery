<?php
include 'db_config.php';

if (isset($_GET['id'])) {
    $imageId = $_GET['id'];

    $stmt = $conn->prepare("
        SELECT 
            Images.file_path AS file_name, 
            Images.uploaded_at AS upload_date, 
            Galleries.name AS gallery_name
        FROM Images
        INNER JOIN Galleries ON Images.gallery_id = Galleries.id
        WHERE Images.id = ?
    ");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $metadata = $result->fetch_assoc();

        // Remove 'uploads/' from the file name
        $filePath = $metadata['file_name'];
        $metadata['file_name'] = str_replace('uploads/', '', $filePath);

        // Attempt to extract EXIF metadata
        $exif = @exif_read_data($filePath);

        // Extract EXIF data fields
        $imageCreatedDate = isset($exif['DateTimeOriginal']) ? $exif['DateTimeOriginal'] : 'Unknown';
        $cameraMake = isset($exif['Make']) ? $exif['Make'] : 'Unknown';
        $cameraModel = isset($exif['Model']) ? $exif['Model'] : 'Unknown';
        $imageWidth = isset($exif['COMPUTED']['Width']) ? $exif['COMPUTED']['Width'] : 'Unknown';
        $imageHeight = isset($exif['COMPUTED']['Height']) ? $exif['COMPUTED']['Height'] : 'Unknown';

        echo json_encode([
            "success" => true,
            "file_name" => $metadata['file_name'],
            "upload_date" => $metadata['upload_date'],
            "gallery_name" => $metadata['gallery_name'],
            "image_created_date" => $imageCreatedDate,
            "camera_make" => $cameraMake,
            "camera_model" => $cameraModel,
            "image_dimensions" => $imageWidth . ' x ' . $imageHeight
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Metadata not found."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>