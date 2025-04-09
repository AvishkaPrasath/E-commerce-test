<?php 
include('navbar.php');
include('config.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$productId = intval($_GET['id'] ?? 0);
if (!$productId) {
    echo "Product ID is missing!";
    exit;
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "Product not found!";
    exit;
}

// Fetch product images
$imagesResult = $conn->query("SELECT * FROM product_images WHERE product_id = $productId");
$productImages = $imagesResult->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $uploadedImages = json_decode($_POST['uploadedImages'], true);
    $imagesToDelete = json_decode($_POST['deleteImages'], true);

    if (!empty($title) && !empty($description) && !empty($price)) {
        $stmtUpdate = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, status = 'pending', updated_at = NOW() WHERE id = ?");
        $stmtUpdate->bind_param("ssdi", $title, $description, $price, $productId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        if (!empty($imagesToDelete)) {
            foreach ($imagesToDelete as $imageName) {
                $stmtDelete = $conn->prepare("DELETE FROM product_images WHERE product_id = ? AND image = ?");
                $stmtDelete->bind_param("is", $productId, $imageName);
                $stmtDelete->execute();
                $stmtDelete->close();

                $imagePath = "uploads/$imageName";
                if (file_exists($imagePath)) unlink($imagePath);
            }
        }

        if (!empty($uploadedImages)) {
            foreach ($uploadedImages as $imageData) {
                $imageContent = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

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

        echo "<div class='alert alert-success'>Product updated successfully! Waiting for admin approval.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <style>
        .image-thumbnail { width: 100px; height: 100px; object-fit: cover; margin: 5px; }
        #cropper-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }
        .cropper-body { background: white; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Edit Product</h1>

    <form method="POST">
        <div class="mb-3">
            <label>Product Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Product Description</label>
            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Price ($)</label>
            <input type="number" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Existing Images</label><br>
            <?php foreach ($productImages as $image): ?>
                <div style="display: inline-block;">
                    <img src="uploads/<?= htmlspecialchars($image['image']) ?>" class="image-thumbnail">
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteImage('<?= $image['image'] ?>')">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-3">
            <label>Upload New Images</label>
            <input type="file" id="imageUpload" accept="image/*" class="form-control">
            <div id="imagePreview"></div>
        </div>

        <input type="hidden" name="uploadedImages" id="uploadedImages">
        <input type="hidden" name="deleteImages" id="deleteImages">

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<div id="cropper-modal">
    <div class="cropper-body">
        <img id="image-to-crop">
        <button id="confirm-crop" class="btn btn-success mt-2">Crop & Save</button>
        <button id="cancel-crop" class="btn btn-secondary mt-2">Cancel</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
    let uploadedImages = [];
    let imagesToDelete = [];
    let cropper;

    function deleteImage(imageName) {
        imagesToDelete.push(imageName);
        document.getElementById('deleteImages').value = JSON.stringify(imagesToDelete);
    }

    document.getElementById('imageUpload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-to-crop').src = e.target.result;
                document.getElementById('cropper-modal').style.display = 'flex';

                cropper = new Cropper(document.getElementById('image-to-crop'), { aspectRatio: 1 });
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('confirm-crop').addEventListener('click', function() {
        const croppedImage = cropper.getCroppedCanvas().toDataURL('image/jpeg');
        uploadedImages.push(croppedImage);
        document.getElementById('uploadedImages').value = JSON.stringify(uploadedImages);

        cropper.destroy();
        document.getElementById('cropper-modal').style.display = 'none';
    });

    document.getElementById('cancel-crop').addEventListener('click', function() {
        cropper.destroy();
        document.getElementById('cropper-modal').style.display = 'none';
    });
</script>
</body>
</html>
