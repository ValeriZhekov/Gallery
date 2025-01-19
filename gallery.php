<?php
session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <style>
       
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .gallery-container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .gallery img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="gallery-container">
        <h1>Photo Gallery</h1>
        <div class="gallery">
            <!-- Gallery content goes here -->
        </div>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>