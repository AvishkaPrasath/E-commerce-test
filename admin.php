<?php 
include('navbar.php');
include('config.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle Product Approval, Rejection, Deletion, and Search
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = intval($_POST['product_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
    } elseif ($action === 'approve_as_before') {
        $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $stmtDelete = $conn->prepare("DELETE FROM product_images WHERE product_id = ? AND created_at > (SELECT updated_at FROM products WHERE id = ?)");
        $stmtDelete->bind_param("ii", $productId, $productId);
        $stmtDelete->execute();
        $stmtDelete->close();
    } elseif ($action === 'delete') {
        $stmtDeleteImages = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
        $stmtDeleteImages->bind_param("i", $productId);
        $stmtDeleteImages->execute();
        $imagesResult = $stmtDeleteImages->get_result();

        while ($image = $imagesResult->fetch_assoc()) {
            $imagePath = 'uploads/' . $image['image'];
            if (file_exists($imagePath)) unlink($imagePath);
        }
        $stmtDeleteImages->close();

        $conn->query("DELETE FROM product_images WHERE product_id = $productId");
        $conn->query("DELETE FROM products WHERE id = $productId");
    }
}

// Search Feature
$searchQuery = "";
$approvedResult = null;

if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE (id = ? OR title LIKE ?) AND status = 'approved'");
    $searchLike = '%' . $searchQuery . '%';
    $stmt->bind_param("is", $searchQuery, $searchLike);
    $stmt->execute();
    $approvedResult = $stmt->get_result();
} else {
    $approvedResult = $conn->query("SELECT * FROM products WHERE status = 'approved'");
}

// Fetch pending products
$pendingResult = $conn->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count,
           IF(p.updated_at > p.created_at, 'edited', 'new') as submission_type
    FROM products p
    WHERE p.status = 'pending'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .image-thumbnail { width: 100px; height: 100px; object-fit: cover; margin: 5px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Admin Dashboard</h1>
    
    <!-- Section for Pending Products -->
    <h2>Pending Products</h2>
    <?php if ($pendingResult->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Price</th>
                <th>Images</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td>
                        <?php
                        $productId = $row['id'];
                        $imagesResult = $conn->query("SELECT * FROM product_images WHERE product_id = $productId");
                        while ($image = $imagesResult->fetch_assoc()):
                        ?>
                            <img src="uploads/<?= htmlspecialchars($image['image']) ?>" class="image-thumbnail">
                        <?php endwhile; ?>
                    </td>
                    <td>
                        <?= $row['submission_type'] === 'edited' ? "<span class='badge bg-warning text-dark'>Edited Post</span>" : "<span class='badge bg-primary'>New Post</span>" ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            <?php if ($row['submission_type'] === 'edited'): ?>
                                <button name="action" value="approve_as_before" class="btn btn-secondary btn-sm mt-1">Approve As Before</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No pending products for review.</div>
    <?php endif; ?>
    
    <!-- Section for Approved Products -->
    <h2>Published Products (Manage/Delete/Edit)</h2>
    <form method="GET" class="mb-3">
        <input type="text" name="search" class="form-control" placeholder="Search by Product ID or Name" value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit" class="btn btn-primary mt-2">Search</button>
    </form>

    <?php if ($approvedResult->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $approvedResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <button name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No published products available.</div>
    <?php endif; ?>
</div>
</body>
</html>
