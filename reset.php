<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        echo "<script>alert('Signup successful! You can now login.');window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Signup</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
<style>
body {
    background: linear-gradient(135deg, #e0f7fa, #d0f0c0);
    font-family: 'Poppins', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    width: 350px;
    text-align: center;
}
input {
    margin: 15px 0;
    border-radius: 6px;
    padding: 10px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    border-radius: 8px;
    padding: 10px;
    background: #2C3E50;
    color: white;
    font-weight: bold;
    transition: 0.3s;
}
button:hover {
    background: #1a252f;
    transform: scale(1.05);
}
a {
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
    <h2>Create an Account</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required class="form-control"><br>
        <input type="email" name="email" placeholder="Email" required class="form-control"><br>
        <input type="password" name="password" placeholder="Password" required class="form-control"><br>
        <button type="submit">Signup</button>
    </form>
    <p class="mt-3">Already have an account? <a href="index.php">Login</a></p>
</div>

</body>
</html>
