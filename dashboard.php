<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (isset($_COOKIE["user"])) {
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $_COOKIE["user"];
    } else {
        header("location: index.php");
        exit;
    }
}
setcookie("user", $_SESSION["username"], time() + (86400 * 30), "/"); // Renew for another 30 days

// Database connection
require 'db_config.php'; // Make sure this file contains your database connection logic

// Handle form submission for adding new products
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $productname = trim($_POST['productname']);
    $productcategory = trim($_POST['productcategory']);
    $quantity = (int)$_POST['quantity'];
    $availability = isset($_POST['availability']) ? 1 : 0; // Boolean value

    // Insert product into database
    $sql = "INSERT INTO products_tbl (productname, productcategory, quantity, availability) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssii", $productname, $productcategory, $quantity, $availability);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding product: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
    }
}

// Fetch products from the database
$products = [];
$sql = "SELECT * FROM products_tbl";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoe Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <img style="width: 100px; cursor: pointer;" src="Images/logo.jpg" class="logo">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a style="color: #CE1126;" class="nav-link active" aria-current="page" href="dashboard.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a style="color: #CE1126;" class="nav-link" href="#">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a style="color: #CE1126;" class="nav-link" href="#">Contact Us</a>
                    </li>
                </ul>
                <span class="navbar-text" style="margin-right: 20px;">
                    <a href="profile.php" style="color: #CE1126; text-decoration: none;">
                        <?php echo htmlspecialchars($_SESSION["username"]); ?>
                    </a>
                </span>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Shoe Dashboard</h2>

        <!-- Form to add new products -->
        <form method="POST" class="mb-4">
            <h4>Add New Product</h4>
            <div class="mb-3">
                <label for="productname" class="form-label">Product Name</label>
                <input type="text" class="form-control" name="productname" id="productname" required>
            </div>
            <div class="mb-3">
                <label for="productcategory" class="form-label">Product Category</label>
                <input type="text" class="form-control" name="productcategory" id="productcategory" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" name="quantity" id="quantity" required>
            </div>
            <div class="mb-3">
                <label for="availability" class="form-label">Available</label>
                <input type="checkbox" name="availability" id="availability" checked>
            </div>
            <button type="submit" name="add_product" class="btn btn-danger">Add Product</button>
        </form>

        <!-- Product list -->
        <h4>Product List</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Available</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($product['productname']); ?></td>
                    <td><?php echo htmlspecialchars($product['productcategory']); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td><?php echo $product['availability'] ? 'Yes' : 'No'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
