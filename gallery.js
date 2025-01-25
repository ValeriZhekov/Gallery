function deleteImage(imageId) {
    if (!confirm("Are you sure you want to delete this image?")) return;

    // send request to delete image
    fetch('delete_image.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ imageId }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // remove image from the DOM
                document.querySelector(`.image-container[data-id="${imageId}"]`).remove();
                alert("Image deleted successfully.");
            } else {
                alert("Failed to delete image: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error deleting image:", error);
            alert("An error occurred.");
        });
}
function showLightbox(imageSrc) {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    lightboxImage.src = imageSrc;
    lightbox.style.display = 'flex';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.style.display = 'none';
}