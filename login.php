<?php
$is_invalid = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Connect to the database
    $mysqli = require __DIR__ . "/database.php";

    // Fetch user based on email
    $sql = sprintf(
        "SELECT * FROM user WHERE email = '%s'",
        $mysqli->real_escape_string($_POST["email"])
    );

    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();

    if ($user) {
        // Verify password
        if (password_verify($_POST["password"], $user["password_hash"])) {
            // Start session
            session_start();
            session_regenerate_id(true);

            // Store user information in session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_role"] = $user["role"];  

            // Redirect based on user role
            if ($user["role"] === 'admin') {  // Redirect to admin if role is admin
                header("Location: admin.php");
            } else {
                header("Location: index.php");  // Redirect to index for regular users
            }
            exit;
        }
    }

    // If login fails
    $is_invalid = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css"> 
    <link rel="stylesheet" href="css/registration.css"> 
</head>
<body>
<div class="registartion">
    <h1>Login</h1>

    <?php if ($is_invalid): ?>
        <em>Invalid login, please try again.</em>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="post">
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    Dont have an acount yet?<a href="signup.html">Signup</a>
    </div>
    </div>

</body>
</html>