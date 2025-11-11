<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
<style>
body {
    background: linear-gradient(135deg, #f0f4f8, #d9e4ec);
    font-family: 'Poppins', sans-serif;
}
.container {
    margin-top: 80px;
}
.card {
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    padding: 30px;
}
.card h2 {
    color: #2C3E50;
}
.btn-custom {
    width: 200px;
    margin: 10px;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s;
}
.btn-custom:hover {
    transform: scale(1.05);
}
</style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card text-center">
        <h2>Welcome, <?php echo ucfirst($_SESSION['username']); ?>!</h2>
        <p class="mb-4">Access your account or logout below:</p>
        <a href="accounts.php" class="btn btn-success btn-custom">Go to Account</a>
        <a href="logout.php" class="btn btn-secondary btn-custom">Logout</a>
    </div>
</div>

</body>
</html>
