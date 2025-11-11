<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hash);
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $id;
            header("Location: welcome.php");
            exit();
        } else {
            $msg = "Invalid password!";
        }
    } else {
        $msg = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Login</title>
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
.msg { color:red; font-weight:bold; margin-bottom:10px; }
a { text-decoration: none; }
</style>
</head>
<body>

<div class="card">
    <h2>User Login</h2>
    <?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required class="form-control"><br>
        <input type="password" name="password" placeholder="Password" required class="form-control"><br>
        <button type="submit">Login</button>
    </form>
    <p class="mt-3">Don't have an account? <a href="reset.php">Signup</a></p>
</div>

</body>
</html>
