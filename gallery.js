function deleteImage(imageId) {
    if (!confirm("Are you sure you want to delete this image?")) return;

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
function showImageModal(imageSrc, imageId) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    const metadataDiv = document.getElementById('image-metadata');

    // Set the image source
    modalImage.src = imageSrc;

    // Fetch metadata
    fetch(`get_image_metadata.php?id=${imageId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            metadataDiv.innerHTML = `
                <p><strong>File Name:</strong> ${data.file_name}</p>
                <p><strong>Upload Date:</strong> ${data.upload_date}</p>
                <p><strong>Gallery:</strong> ${data.gallery_name}</p>
                <p><strong>Image Taken On:</strong> ${data.image_created_date}</p>
                <p><strong>Camera Make:</strong> ${data.camera_make}</p>
                <p><strong>Camera Model:</strong> ${data.camera_model}</p>
                <p><strong>Image Dimensions:</strong> ${data.image_dimensions}</p>
            `;
        } else {
            metadataDiv.innerHTML = `<p>Error: ${data.message}</p>`;
        }
    })
    .catch(error => {
        console.error("Error fetching metadata:", error);
        metadataDiv.innerHTML = `<p>Error fetching metadata.</p>`;
    });

    // Show the modal
    modal.style.display = 'flex';
}

function closeImageModal() {
    const modal = document.getElementById('image-modal');
    modal.style.display = 'none';
}