<?php
session_start();
$mysqli = require __DIR__ . "/database.php";

// Initialize the cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle "Add to Cart" action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;  // Default quantity is 1
    //add or update product quantity

    if(isset($_SESSION['cart'][$product_id])){
        $_SESSION['cart'][$product_id] += $quantity;
    } else{
        $_SESSION['cart'][$product_id] = $quantity;
      }
      Header("Location: cart.php");
    }
// Fetch product details for items in the cart
$cart_items = [];
if (!empty($_SESSION['cart'])) { 
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $sql = "SELECT product_id, name, price, image FROM product WHERE product_id IN ($placeholders)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($_SESSION['cart'])), ...array_keys($_SESSION['cart']));
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $_SESSION['cart'][$row['product_id']];
        $cart_items[$row['product_id']] = $row;
    }
}
// Handle update actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update quantity
    if (isset($_POST['update']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = max(0, (int)$_POST['quantity']);  // Ensure quantity is non-negative
        if ($quantity == 0) {
            unset($_SESSION['cart'][$product_id]);  // Remove item if quantity is zero
        } else {
            $_SESSION['cart'][$product_id] = $quantity;  // Update the quantity
        }
    }
    // Remove item
    elseif (isset($_POST['remove']) && isset($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        unset($_SESSION['cart'][$product_id]);  // Remove item from cart
    }
}

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/bootstrap.css"> 
    <link rel="stylesheet" href="css/style.css"> 

</head>
<body>

<div class="container mt-4">
    <h1>Your Shopping Cart</h1>
    <?php if (!empty($cart_items)): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Image</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><img src="<?= htmlspecialchars($item['image']) ?>" alt=""  class="cart-image"></td>
                        <td>
                            <form action="cart.php" method="post">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" class="form-control ">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" name="update" class="btn btn-info btn-sm mt-2">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <button type="submit" name="remove" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total Price</th>
                    <th>$<?= number_format($total_price, 2) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <div class="row">
            <div class="col">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
            <div class="col text-right">
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
        <a href="index.php" class="btn btn-primary">Start Shopping</a>
    <?php endif; ?>
</div>
<?php include'inc/footer.php'; ?>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>