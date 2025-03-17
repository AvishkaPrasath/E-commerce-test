<?php
include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = intval($_POST['product_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $uploadedImages = json_decode($_POST['uploadedImages'], true);

    if (!empty($title) && !empty($description) && !empty($price)) {
        // Update product details and set status to 'pending'
        $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, status = 'pending' WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssdii", $title, $description, $price, $productId, $userId);

        if ($stmt->execute()) {
            // Save new uploaded images
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
            echo "<div class='alert alert-success'>Product updated successfully! Waiting for admin approval.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}
?>
