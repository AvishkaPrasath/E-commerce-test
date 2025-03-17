<?php
include('navbar.php');
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $role, $created_at);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Account</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($email); ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($role); ?></p>
    <p><strong>Joined On:</strong> <?= htmlspecialchars($created_at); ?></p>

    <a href="logout.php">Logout</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
