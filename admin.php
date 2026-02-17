<?php
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Database connection
$mysqli = require __DIR__ . "/database.php";

// Directory to save uploaded images
$image_dir = __DIR__ . "/uploads";

// Handle POST requests for product operations
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action === "add") {
        // Add a new product
        $name = $_POST["name"];
        $description = $_POST["description"];
        $price = $_POST["price"];
        $category = $_POST["category"];
        $image = null;

        // Handle file upload for product image
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $image_name = basename($_FILES["image"]["name"]);
            $target_path = $image_dir . $image_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_path);
            $image = "uploads/" . $image_name;
        }

        $sql = "INSERT INTO product (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssdss", $name, $description, $price, $category, $image);
        $stmt->execute();

    } elseif ($action === "delete" && isset($_POST["product_id"])) {
        // Delete a product
        $product_id = $_POST["product_id"];
        $sql = "DELETE FROM product WHERE product_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }
}

// Fetch all products to display in the admin panel
$sql = "SELECT product_id, name, description, price, category, image FROM product";
$result = $mysqli->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.css"> 
    <link rel="stylesheet" href="css/style.css">  
    <title>Admin - Product Management</title>
</head>
<body>

<!-- Admin Navigation -->
<div class="container">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Y/A Jewellery</a>
    <a class="nav-link" href="logout.php">Logout</a>
</nav>
</div>

<div class="container mt-4">
    <h1>Admin - Product Management</h1>

    <!-- to add new product -->
    <h2>Add New Product</h2>
    <form method="post" enctype="multipart/form-data">  <!-- to upload a picture -->
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="image">Image:</label>
            <input type="file" id="image" name="image" class="form-control">  
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>

    <h2>Existing Products</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($product = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($product["product_id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($product["name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($product["description"]) . "</td>";
                        echo "<td>$" . number_format($product["price"], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($product["category"]) . "</td>";
                        if (!empty($product["image"])) {
                            echo "<td><img src='" . htmlspecialchars($product["image"]) . "' style='width: 50px; height: 50px;' alt='Product Image'></td>";
                        } else {
                            echo "<td>No image</td>";
                        }
                        echo '<td>';
                        echo '<form method="post" style="display: inline;">';
                        echo '<input type="hidden" name="action" value="delete">';
                        echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($product["product_id"]) . '">';
                        echo '<button type="submit" class="btn btn-danger">Delete</button>';
                        echo '</form>';
                        echo '</td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No products found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

<!--for bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>