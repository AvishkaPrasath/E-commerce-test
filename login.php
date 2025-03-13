<?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2>Login</h2>
        <form onsubmit="loginUser(event)">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" id="login-email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" id="login-password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
