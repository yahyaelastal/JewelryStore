<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Fetch product details
$product_id = isset($_GET["product_id"]) ? $_GET["product_id"] : 0;

$product = null;
if ($product_id > 0) {
    $sql = "SELECT product_id, name, description, price, category, image FROM product WHERE product_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
}

// Fetch related products from the same category
$related_products = [];
if ($product) {
    $category = $product["category"];
    $sql = "SELECT product_id, name, description, price, image FROM product WHERE category = ? AND product_id != ? LIMIT 4";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("si", $category, $product_id);
    $stmt->execute();
    $related_products = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="css/bootstrap.css"> 
    <link rel="stylesheet" href="css/style.css">  
</head>
<body>

<!-- Navigation Bar -->
<div class="container">
    <div class="col-md-12">
        <div class="row">
        <ul class="navigation">
        <li><a class="navbar-brand" href="index.php">Y/A Jewellery</a></li>
        <li><a class="nav-link" href="cart.php"><img src="uploads/cart.png" class="cart"alt=""> <span id="cart-count"><?= array_sum(array_values($_SESSION['cart'] ??[])) ?></span></a></li>
        <li><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
        </div>
    </div>
</div>

   <div class="container">
    <div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
        <h1 class="prod-name"><?= htmlspecialchars($product["name"]) ?></h1>
        <?php if (!empty($product["image"])): ?>
            <img src="<?= htmlspecialchars($product["image"]) ?>" alt="Product Image" class="product-image">
        <?php endif; ?>
        <p class="product-description"><?= htmlspecialchars($product["description"]) ?></p>
        <p class="product-price">Price: $<?= number_format($product["price"], 2) ?></p>

       
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product["product_id"]) ?>">
            <button type="submit" class="button-89">Add to Cart</button>
        </form>
        </div>
        </div>
        </div>
        </div>
        
       
        <div class="container">
        <h2>Related Products</h2>
        <div class="row d-flex align-items-stretch">
         <?php if ($related_products->num_rows >0): ?>
             <?php while ($related_product = $related_products->fetch_assoc()): ?>
                <div class="col-md-3 d-flex">
                    <div class="card  flex-fill">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <h5 class="card-title">
                                <a href="product.php?product_id=<?= htmlspecialchars($related_product["product_id"]) ?>"><?= htmlspecialchars($related_product["name"]) ?></a>
                            </h5>
                            <?php if (!empty($related_product["image"])): ?>
                                <img src="<?= htmlspecialchars($related_product["image"]) ?>" alt="Related Product Image" class="img-fluid">
                            <?php endif; ?>
                            
                            <p>Price: $<?= number_format($related_product["price"], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
         <?php else: ?>
            <p class="no_prod" >No related products found.</p>
        <?php endif; ?>
        </div>
         </div>

         <?php include'inc/footer.php'; ?>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>