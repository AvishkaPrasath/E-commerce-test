<?php include('navbar.php'); ?>
    
    <div class="container mt-5">
        <h2>Sign Up</h2>
        <form onsubmit="registerUser(event)">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Select Role:</label>
                <select id="role" class="form-control">
                    <option value="user">User</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
