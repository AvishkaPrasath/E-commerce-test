<?php
include('navbar.php');
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $uploadedImages = json_decode($_POST['uploadedImages'], true);

    if (!empty($title) && !empty($description) && !empty($price)) {
        $stmt = $conn->prepare("INSERT INTO products (user_id, title, description, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issd", $userId, $title, $description, $price);

        if ($stmt->execute()) {
            $productId = $stmt->insert_id;

            // Save cropped images to uploads folder and database
            if (!empty($uploadedImages)) {
                foreach ($uploadedImages as $imageData) {
                    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                    $imageData = str_replace(' ', '+', $imageData);
                    $imageContent = base64_decode($imageData);

                    $fileName = uniqid("IMG_", true) . '.jpg';
                    $filePath = "uploads/" . $fileName;

                    if (file_put_contents($filePath, $imageContent)) {
                        $stmtImage = $conn->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)");
                        $stmtImage->bind_param("is", $productId, $fileName);
                        $stmtImage->execute();
                        $stmtImage->close();
                    }
                }
            }

            echo "<div class='alert alert-success'>Product submitted successfully! Waiting for admin approval.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}
?>

<div class="container my-5">
    <h2>Submit Your Product</h2>
    <form method="POST" enctype="multipart/form-data" id="submitProductForm">
        <div class="mb-3">
            <label>Product Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Product Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label>Price ($)</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label>Upload Product Images</label>
            <input type="file" id="imageUpload" class="form-control" accept="image/*" style="display: none;">
            <div id="imagePreview" class="mb-3 d-flex flex-wrap"></div>
            <button type="button" id="addMoreImages" class="btn btn-secondary mb-3">+ Add More Images</button>
            <input type="hidden" name="uploadedImages" id="uploadedImages">
        </div>
        <button type="submit" class="btn btn-primary">Submit Product</button>
    </form>
</div>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex; justify-content: center; align-items: center;
    }
    #cropper-modal img {
        max-width: 80%;
        max-height: 80%;
    }
</style>

<div id="cropper-modal" class="modal-overlay" style="display: none;">
    <div>
        <img id="image-to-crop">
        <div class="text-center mt-3">
            <button id="confirm-crop" class="btn btn-success">Crop & Save</button>
            <button id="cancel-crop" class="btn btn-danger">Cancel</button>
        </div>
    </div>
</div>

<script>
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const addMoreImages = document.getElementById('addMoreImages');
    const cropperModal = document.getElementById('cropper-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const confirmCrop = document.getElementById('confirm-crop');
    const cancelCrop = document.getElementById('cancel-crop');

    let uploadedImages = [];
    let cropper;

    addMoreImages.addEventListener('click', () => {
        imageUpload.click();
    });

    imageUpload.addEventListener('change', () => {
        const file = imageUpload.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = () => {
                imageToCrop.src = reader.result;
                cropperModal.style.display = 'flex';
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1,
                    viewMode: 2,
                    autoCropArea: 1,
                    background: false,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    confirmCrop.addEventListener('click', () => {
        const croppedCanvas = cropper.getCroppedCanvas({ width: 800, height: 800 });
        const croppedImage = croppedCanvas.toDataURL('image/jpeg', 0.9);
        
        const imageContainer = document.createElement('div');
        imageContainer.innerHTML = `
            <div style="position: relative; display: inline-block; margin: 5px;">
                <img src="${croppedImage}" class="img-thumbnail" style="width: 150px; height: 150px;">
                <span style="position: absolute; top: 0; right: 0; cursor: pointer; color: red;" onclick="this.parentElement.remove()">🗑️</span>
            </div>
        `;
        imagePreview.appendChild(imageContainer);
        uploadedImages.push(croppedImage);
        
        document.getElementById('uploadedImages').value = JSON.stringify(uploadedImages);

        cropper.destroy();
        cropperModal.style.display = 'none';
        imageUpload.value = '';
    });

    cancelCrop.addEventListener('click', () => {
        cropper.destroy();
        cropperModal.style.display = 'none';
        imageUpload.value = '';
    });
</script>
