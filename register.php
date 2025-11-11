<?php
include 'config.php';
session_start();
error_reporting(0);

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['passwords']);

    // Check if fields are not empty
    if ($email != "" && $password != "") {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['passwords'])) {
            $_SESSION['username'] = $user['username'];
            header("Location: welcome.php");
            exit();
        } else {
            echo "<script>alert('Invalid email or password!');</script>";
        }
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <form action="" method="POST" class="login-email p-4 shadow-sm bg-white rounded">
        <p class="login-text text-center" style="font-size: 2rem; font-weight: 800;">Login</p>

        <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="Email" name="email" required>
        </div>

        <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Password" name="passwords" required>
        </div>

        <div class="input-group">
            <button name="submit" class="btn btn-primary btn-block">Login</button>
        </div>

        <p class="login-register-text mt-3 text-center">
            Don't have an account? <a href="register.php">Register Here</a>.
        </p>
    </form>
</div>
</body>
</html>
