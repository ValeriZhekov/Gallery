<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'db_config.php';

$username = $_SESSION['username'];
//fetch user ID
$query = "SELECT id FROM Users WHERE username = ?";
$params = [$username];
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$user_id = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $user_id = $row['id'];
} else {
    die("User not found.");
}
sqlsrv_free_stmt($stmt);

// handle Create Gallery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_gallery'])) {
    $gallery_name = trim($_POST['gallery_name']);

    if (!empty($gallery_name)) {
        $query = "INSERT INTO Galleries (name, user_id) VALUES (?, ?)";
        $params = [$gallery_name, $user_id];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);
    }
}

// handle Upload Image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $gallery_id = $_POST['gallery_id'];
    $target_dir = "uploads/";
    $target_file = $target_dir . time() . basename($_FILES["image"]["name"]);
    $upload_ok = 1;

    // move the uploaded file to the server
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $query = "INSERT INTO Images (file_path, gallery_id) VALUES (?, ?)";
        $params = [$target_file, $gallery_id];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);
    } else {
        echo "Error uploading image.";
    }
}

// fetch galleries for the user after each post
$query = "SELECT id, name FROM Galleries WHERE user_id = ?";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $query, $params);

$galleries = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $galleries[] = $row;
    }
}
sqlsrv_free_stmt($stmt);

// handle Merge Galleries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['merge_galleries'])) {
    $source_gallery = $_POST['source_gallery'];
    $destination_gallery = $_POST['destination_gallery'];

    if (!empty($source_gallery) && !empty($destination_gallery) && $source_gallery !== $destination_gallery) {
        //add images to destination gallery
        $query = "UPDATE Images SET gallery_id = ? WHERE gallery_id = ?";
        $params = [$destination_gallery, $source_gallery];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);

        //remove the source gallery
        $query = "DELETE FROM Galleries WHERE id = ?";
        $params = [$source_gallery];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        sqlsrv_free_stmt($stmt);

        // remove the source gallery from the $galleries array
        foreach ($galleries as $key => $gallery) {
            if ($gallery['id'] == $source_gallery) {
                unset($galleries[$key]);
                break;
            }
        }
        //  reindex the array 
        $galleries = array_values($galleries);

        echo "<p style='color: green;'>Galleries merged successfully!</p>";
    } else {
        echo "<p style='color: red;'>Please select two different galleries to merge.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link rel="stylesheet" href="gallery_styles.css">
</head>

<body>
    <div class="gallery-container">
        <h1>Photo Gallery</h1>
        <div class="form-section">

            <form method="POST" action="">
                <h3>Create a New Gallery</h3>
                <input type="text" name="gallery_name" placeholder="Gallery Name" required>
                <button type="submit" name="create_gallery">Create Gallery</button>
            </form>

            <form method="POST" action="" enctype="multipart/form-data">
                <h3>Upload an Image</h3>
                <select name="gallery_id" required>
                    <option value="">Select a Gallery</option>
                    <?php foreach ($galleries as $gallery): ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="upload_image">Upload Image</button>
            </form>

            <form method="POST" action="">
                <h3>Merge Two Galleries</h3>
                <label for="source_gallery">Source Gallery:</label>
                <select name="source_gallery" id="source_gallery" required>
                    <option value="">Select Source Gallery</option>
                    <?php foreach ($galleries as $gallery): ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="destination_gallery">Destination Gallery:</label>
                <select name="destination_gallery" id="destination_gallery" required>
                    <option value="">Select Destination Gallery</option>
                    <?php foreach ($galleries as $gallery): ?>
                        <option value="<?php echo $gallery['id']; ?>"><?php echo htmlspecialchars($gallery['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="merge_galleries">Merge Galleries</button>
            </form>
        </div>
        <div class="gallery">
            <h2>Your Galleries</h2>
            <?php foreach ($galleries as $gallery): ?>
                <h3><?php echo htmlspecialchars($gallery['name']); ?></h3>
                <div class="gallery-images">
                    <?php
                    
                    $query = "SELECT id, file_path FROM Images WHERE gallery_id = ?";
                    $params = [$gallery['id']];
                    $stmt = sqlsrv_query($conn, $query, $params);

                    if ($stmt !== false):
                        while ($image = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                            ?>
                            <div class="image-container" data-id="<?php echo $image['id']; ?>">
                                <img src="<?php echo htmlspecialchars($image['file_path']); ?>" alt="Gallery Image"
                                    onclick="showLightbox('<?php echo htmlspecialchars($image['file_path']); ?>')">
                                <button class="delete-btn" onclick="deleteImage(<?php echo $image['id']; ?>)">X</button>
                            </div>
                            <?php
                        endwhile;
                        sqlsrv_free_stmt($stmt);
                    endif;
                    ?>
                </div>
            <?php endforeach; ?>
        </div>

       
        <div class="lightbox" id="lightbox">
            <button class="close-btn" onclick="closeLightbox()">X</button>
            <img id="lightbox-image" src="" alt="Enlarged Image">
        </div>
    </div>

        <script src="gallery.js"></script>


        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>

</html>
<?php
sqlsrv_close($conn);
?>