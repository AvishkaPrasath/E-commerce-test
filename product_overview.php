<?php
include('config.php');
include('navbar.php');

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$productResult = $stmt->get_result();
$product = $productResult->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

$isOwner = ($userId && $userId == $product['user_id']);

$stmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$imagesResult = $stmt->get_result();
$images = [];
while ($image = $imagesResult->fetch_assoc()) {
    $images[] = $image['image'];
}
$stmt->close();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="uploads/<?= htmlspecialchars($image); ?>" class="d-block w-100" alt="Product Image">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>

        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['title']); ?></h2>
            <p><?= htmlspecialchars($product['description']); ?></p>
            <p><strong>Price:</strong> $<?= number_format($product['price'], 2); ?></p>

            <?php if ($isOwner): ?>
                <hr>
                <h3>Edit Product</h3>
                <form method="POST" enctype="multipart/form-data" id="editProductForm" action="update_product.php">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">

                    <div class="mb-3">
                        <label>Title:</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Description:</label>
                        <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Price:</label>
                        <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label>Upload Product Images</label>
                        <input type="file" name='uploadimages' id="imageUpload" class="form-control" accept="image/*" style="display: none;">
                        <div id="imagePreview" class="mb-3 d-flex flex-wrap"></div>
                        <button type="button" id="addMoreImages" class="btn btn-secondary mb-3">+ Add More Images</button>
                        <input type="hidden" name="uploadedImages" id="uploadedImages">
                    </div>
                    <button type="submit" id="saveChangesBtn" class="btn btn-primary">Save Changes & Send for Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>