<?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2>Our Products</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <img src="images/product1.jpg" class="card-img-top" alt="Product 1">
                    <div class="card-body">
                        <h5 class="card-title">Product 1</h5>
                        <p class="card-text">$20</p>
                        <button class="btn btn-success" onclick="addToCart('Product 1', 20)">Add to Cart</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <img src="images/product2.jpg" class="card-img-top" alt="Product 2">
                    <div class="card-body">
                        <h5 class="card-title">Product 2</h5>
                        <p class="card-text">$30</p>
                        <button class="btn btn-success" onclick="addToCart('Product 2', 30)">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 My Shop. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
