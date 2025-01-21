<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['imageId'])) {
    echo json_encode(['success' => false, 'message' => 'No image ID provided']);
    exit;
}

$imageId = $data['imageId'];

$query = "SELECT file_path FROM Images WHERE id = ?";
$params = [$imageId];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)]);
    exit;
}

$image = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$image) {
    echo json_encode(['success' => false, 'message' => 'Image not found or unauthorized']);
    exit;
}


$fileName = $image['file_path']; 


if (file_exists($fileName)) {
    if (!unlink($fileName)) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete image file']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Image file not found on the server']);
    exit;
}

$query = "DELETE FROM Images WHERE id = ?";
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete image record from database: ' . print_r(sqlsrv_errors(), true)]);
    exit;
}

sqlsrv_free_stmt($stmt);

echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
exit;