<?php
// Session and database connection
session_start();
$mysqli = require __DIR__ . "/database.php";

// Fetch products
$category = isset($_GET["category"]) ? $_GET["category"] : null;
if ($category) {
    $sql = "SELECT product_id, name, description, price, image FROM product WHERE category = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $category);
} else {
    $sql = "SELECT product_id, name, description, price, image FROM product";
    $stmt = $mysqli->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// User authentication check
$user = null;
if (isset($_SESSION["user_id"])) {
    $sql = "SELECT * FROM user WHERE id = {$_SESSION["user_id"]}";
    $user_result = $mysqli->query($sql);
    $user = $user_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.css">  
    <link rel="stylesheet" href="css/style.css">  
    
    <title>Product Store</title>
</head>
<body>

<!-- Navigation Bar -->
 <div class="top">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <ul class="navigation">
                    <li><a href="index.php" class="Logo">Y/A Jewellery</a></li>
                    <li><a class="nav-link" href="index.php">All Products</a></li>
                    <li><a class="nav-link" href="index.php?category=Ring">Rings</a></li>
                    <li><a class="nav-link" href="index.php?category=Bracelet">Bracelets</a></li>
                    <li><a class="nav-link" href="index.php?category=Necklace">Necklaces</a></li>
                    <li><a class="nav-link" href="index.php?category=Earing">Earings</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="navleft">
                    <li><a class="nav-link" href="cart.php"><img src="uploads/cart.png" class="cart"alt=""> <span id="cart-count"><?= array_sum(array_values($_SESSION['cart'] ??[])) ?></span></a></li>
                    <?php if ($user): ?>
                        <li><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a class="nav-link" href="login.php">Login</a></li>
                        <li><a class="nav-link" href="signup.html">Signup</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
 </div>

<!-- Main Content -->
 <div class="container mt-4">
    <div class="row">
        <?php if ($user): ?>
            <h2>Welcome Back, <?= htmlspecialchars($user["name"]) ?>!</h2>
        <?php endif; ?>
        <h3>Products</h3>
    </div>

    <div class="products">
        <div class="row d-flex align-items-stretch">  
        <?php
        if ($result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                echo '<div class="col-md-3 d-flex">';  
                echo '<div class="card flex-fill">';  
                echo '<div class="card-body d-flex flex-column justify-content-between">';  
                echo '<div>';  
                echo '<h5 class="card-title"><a href="product.php?product_id='. htmlspecialchars($product["product_id"]) .'">' . htmlspecialchars($product["name"]) . '</a></h5>';
                echo '</div>';  
                if (!empty($product["image"])) {
                    echo '<img src="' . htmlspecialchars($product["image"]) . '" alt="Product Image"  class="img-fluid ProductIMG">';
            }
            echo '<div class="d-flex justify-content-between align-items-end">';  
            echo '<p class="card-text">Price: $' . number_format($product["price"], 2) . '</p>';
            
            // Add to Cart button with AJAX request
            echo '<button class="button-89 add-to-cart" data-product-id="' . htmlspecialchars($product["product_id"]) . '">Add to Cart</button>';
            
            echo '</div>';  
                echo '</div>';  
                echo '</div>';  
                echo '</div>';  
            }
            include 'inc/footer.php';
        } else {
            echo "<p>No products found in this category.</p>";
        }
       ?>
        </div>
    </div>
 </div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        // AJAX request to handle adding a product to the cart
        $(".add-to-cart").click(function() {
            var productId = $(this).data("product-id");
            $.post("add_to_cart.php", { product_id: productId }, function(data) {
                $("#cart-count").text(data);
            });
        });
    });
</script>
</body>
</html>