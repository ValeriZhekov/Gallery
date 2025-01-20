<?php
session_start();
require 'db_config.php';

// ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// get input data
$data = json_decode(file_get_contents('php://input'), true);
$imageId = $data['imageId'] ?? null;

if (!$imageId) {
    echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
    exit;
}

// fetch the image to ensure it belongs to the user
$query = "SELECT filename FROM Images WHERE id = ? AND username = ?";
$params = [$imageId, $_SESSION['username']];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false || !($image = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    echo json_encode(['success' => false, 'message' => 'Image not found or unauthorized']);
    exit;
}

sqlsrv_free_stmt($stmt);

// delete image from the filesystem
$filepath = 'uploads/' . $image['filename'];
if (file_exists($filepath)) {
    unlink($filepath);
}

// remove image from the database
$query = "DELETE FROM Images WHERE id = ?";
$params = [$imageId];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
    exit;
}

sqlsrv_free_stmt($stmt);

echo json_encode(['success' => true]);
exit;
?>