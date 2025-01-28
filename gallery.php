<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_config.php';

// Fetch user galleries
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM Galleries WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$galleries = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="gallery_styles.css">
</head>
<body>
    <div class="gallery-container">
        <h1>Your Galleries</h1>

        <!-- Create new gallery form -->
        <div class="form-section">
            <h3>Create a New Gallery</h3>
            <form action="create_gallery.php" method="POST">
                <input type="text" name="gallery_name" placeholder="Gallery Name" required>
                <button type="submit">Create Gallery</button>
            </form>
        </div>

        <!-- Image upload form -->
        <div class="form-section">
            <h3>Upload an Image</h3>
            <form action="upload_image.php" method="POST" enctype="multipart/form-data">
                <label for="gallery">Select a Gallery:</label>
                <select name="gallery_id" id="gallery" required>
                    <option value="" disabled selected>Select a gallery</option>
                    <?php while ($gallery = $galleries->fetch_assoc()): ?>
                        <option value="<?= $gallery['id'] ?>"><?= htmlspecialchars($gallery['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Upload Image</button>
            </form>
        </div>

        <!-- Merge galleries form -->
        <div class="form-section">
            <h3>Merge Galleries</h3>
            <form action="merge_galleries.php" method="POST">
                <label for="source_gallery">Source Gallery:</label>
                <select name="source_gallery" id="source_gallery" required>
                    <option value="" disabled selected>Select a source gallery</option>
                    <?php
                    $stmt->execute();
                    $galleries = $stmt->get_result();
                    while ($gallery = $galleries->fetch_assoc()): ?>
                        <option value="<?= $gallery['id'] ?>"><?= htmlspecialchars($gallery['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="destination_gallery">Destination Gallery:</label>
                <select name="destination_gallery" id="destination_gallery" required>
                    <option value="" disabled selected>Select a destination gallery</option>
                    <?php
                    $stmt->execute();
                    $galleries = $stmt->get_result();
                    while ($gallery = $galleries->fetch_assoc()): ?>
                        <option value="<?= $gallery['id'] ?>"><?= htmlspecialchars($gallery['name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Merge Galleries</button>
            </form>
        </div>
        <div class="form-section">
    <h3>Import Images from Archive</h3>
    <form action="import_archive.php" method="POST" enctype="multipart/form-data">
        <label for="archive">Upload Archive (ZIP):</label>
        <input type="file" name="archive" id="archive" accept=".zip" required>

        <label for="import-gallery">Select a Gallery:</label>
        <select name="gallery_id" id="import-gallery" required>
            <option value="" disabled selected>Select a gallery</option>
            <?php
            $stmt->execute(); // Re-fetch galleries for the dropdown
            $galleries = $stmt->get_result();
            while ($gallery = $galleries->fetch_assoc()): ?>
                <option value="<?= $gallery['id'] ?>"><?= htmlspecialchars($gallery['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Import Images</button>
    </form>
</div>
<div class="form-section">
    <h3>Export Images</h3>
    <form action="export_images.php" method="POST">
        <label for="camera-model">Camera Model:</label>
        <input type="text" name="camera_model" id="camera-model" placeholder="Optional">

        <label for="year-from">Year Taken (From):</label>
        <input type="number" name="year_from" id="year-from" placeholder="YYYY">

        <label for="year-to">Year Taken (To):</label>
        <input type="number" name="year_to" id="year-to" placeholder="YYYY">

        <button type="submit">Export Images</button>
    </form>
</div>


        <!-- Display galleries and their images -->
        <?php
$stmt->execute(); // Re-execute to refresh gallery list
$galleries = $stmt->get_result();
if ($galleries->num_rows > 0): 
?>
    <?php while ($gallery = $galleries->fetch_assoc()): ?>
        <div class="gallery">
            <h2><?= htmlspecialchars($gallery['name']) ?></h2>
            <div class="gallery-images">
                <?php
                $galleryId = $gallery['id'];
                $stmtImages = $conn->prepare("SELECT * FROM Images WHERE gallery_id = ?");
                $stmtImages->bind_param("i", $galleryId);
                $stmtImages->execute();
                $images = $stmtImages->get_result();

                if ($images->num_rows > 0):
                    while ($image = $images->fetch_assoc()):
                ?>
                    <div class="image-container" data-id="<?= $image['id'] ?>">
                        <img 
                            src="<?= htmlspecialchars($image['file_path']) ?>" 
                            alt="Gallery Image" 
                            onclick="showImageModal('<?= htmlspecialchars($image['file_path']) ?>', '<?= $image['id'] ?>')"
                        >
                        <button class="delete-btn" onclick="deleteImage(<?= $image['id'] ?>)">X</button>
                    </div>
                <?php
                    endwhile;
                else:
                ?>
                    <p>No images in this gallery.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No galleries found. Create a new gallery to get started!</p>
<?php endif; ?>

<!-- Modal for Fullscreen Image and Metadata -->
<div id="image-modal" class="modal">
    <div class="modal-content">
        <img id="modal-image" src="" alt="Full Image">
        <div class="metadata" id="image-metadata"></div>
        <button class="modal-close" onclick="closeImageModal()">Close</button>
    </div>
</div>

        <!-- Logout button -->
        <div class="logout-container">
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <script src="gallery.js"></script>
</body>
</html>