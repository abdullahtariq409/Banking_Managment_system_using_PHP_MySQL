<?php
include 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}
$user_id = $user['id'];

if (isset($_POST['submit'])) {
    $account_name = trim($_POST['account_name']);
    $initial_balance = (float) $_POST['initial_balance'];

    if ($account_name == '') {
        $msg = "Enter an account name.";
    } else {
        $stmt = $conn->prepare("INSERT INTO accounts (user_id, account_name, balance) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $user_id, $account_name, $initial_balance);
        if ($stmt->execute()) {
            $acc_id = $stmt->insert_id;
            if ($initial_balance > 0) {
                $t = $conn->prepare("INSERT INTO transactions (account_id, type, amount, description) VALUES (?, 'credit', ?, ?)");
                $desc = "Initial deposit";
                $t->bind_param("ids", $acc_id, $initial_balance, $desc);
                $t->execute();
                $t->close();
            }
            $msg = "Account created successfully.";
        } else {
            $msg = "Error creating account: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account</title>
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
    width: 400px;
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
.msg {
    color: green;
    font-weight: bold;
    margin-bottom: 15px;
}
a { text-decoration: none; display: inline-block; margin-top: 15px; color: #2C3E50; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="card">
    <h2>Create New Account</h2>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="account_name" placeholder="Account Name" required class="form-control">
        <input type="number" step="0.01" name="initial_balance" value="0.00" placeholder="Initial Balance (PKR)" required class="form-control">
        <button type="submit" name="submit">Create Account</button>
    </form>
    <a href="welcome.php">Back to Dashboard</a>
</div>

</body>
</html>
