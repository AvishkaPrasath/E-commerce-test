let cart = [];

function addToCart(name, price) {
    cart.push({ name, price });
    localStorage.setItem("cart", JSON.stringify(cart));
    alert(name + " added to cart!");
}

function loadCart() {
    let cartItems = document.getElementById("cart-items");
    let totalPrice = document.getElementById("total-price");
    cart = JSON.parse(localStorage.getItem("cart")) || [];

    cartItems.innerHTML = "";
    let total = 0;
    cart.forEach((item) => {
        total += item.price;
        let li = document.createElement("li");
        li.className = "list-group-item";
        li.textContent = `${item.name} - $${item.price}`;
        cartItems.appendChild(li);
    });

    totalPrice.textContent = total;
}

if (window.location.pathname.includes("cart.html")) {
    loadCart();
}

// Store users in localStorage
let users = JSON.parse(localStorage.getItem("users")) || [];

// Register User
function registerUser(event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const role = document.getElementById("role").value;

    if (users.some(user => user.email === email)) {
        alert("Email already exists! Please use a different email.");
        return;
    }

    const newUser = { name, email, password, role };
    users.push(newUser);
    localStorage.setItem("users", JSON.stringify(users));

    alert("Registration successful! Please log in.");
    window.location.href = "login.php";
}

// User Login
function loginUser(event) {
    event.preventDefault();

    const email = document.getElementById("login-email").value;
    const password = document.getElementById("login-password").value;

    const user = users.find(user => user.email === email && user.password === password);

    if (user) {
        localStorage.setItem("currentUser", JSON.stringify(user));
        alert("Login successful!");

        if (user.role === "admin") {
            window.location.href = "admin.php";
        } else {
            window.location.href = "index.php";
        }
    } else {
        alert("Invalid email or password!");
    }
}
