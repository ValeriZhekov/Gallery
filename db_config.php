<?php
$host = 'localhost'; 
$dbname = 'gallery_project'; 
$username = 'root'; 
$password = '11223344vV'; // Your MySQL password (replace with actual password if set)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>