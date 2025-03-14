<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="bg-dark text-white p-3">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container-fluid">
                    <!-- Site Title -->
                    <a class="navbar-brand" href="index.php">My Shop</a>

                    <!-- Hamburger Menu (for mobile view) -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Navbar Links -->
                    <div class="collapse navbar-collapse" id="navbarContent">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item"><a class="nav-link text-white" href="index.php">Home</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="products.php">Products</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="cart.php">Cart</a></li>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- If User Is Logged In -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?= htmlspecialchars($_SESSION['username']); ?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="user_account.php">My Account</a></li>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                                            <li><a class="dropdown-item" href="submit_product.php">Submit Product</a></li>
                                        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                            <li><a class="dropdown-item" href="admin.php">Admin Panel</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <!-- If User Is Logged Out -->
                                <li class="nav-item"><a class="nav-link text-white" href="login.php">Login</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="signup.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>
