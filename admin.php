<?php
include 'config.php';
session_start();

// Admin credentials
$admin_email = "admin@bank.com";
$admin_pass = "12345";

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Login
if (isset($_POST['admin_login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === $admin_email && $password === $admin_pass) {
        $_SESSION['admin'] = $email;
    } else {
        $msg = "Invalid admin credentials!";
    }
}

// Show login if not logged in
if (!isset($_SESSION['admin'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin:0;
}
form {
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    width: 300px;
}
input {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 15px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}
button {
    width: 100%;
    padding: 12px;
    background: #2C3E50;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}
button:hover {
    background: #1a252f;
}
h2 {
    text-align:center;
    color: #2C3E50;
}
.msg {
    color: red;
    font-weight: bold;
    text-align:center;
}
</style>
</head>
<body>
<form method="POST">
    <h2>Admin Login</h2>
    <?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="admin_login">Login</button>
</form>
</body>
</html>
<?php
exit();
endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f0f2f5;
    margin:0;
    padding:0;
}
nav {
    background: #2C3E50;
    color: #fff;
    padding: 15px 30px;
    display:flex;
    justify-content: space-between;
    align-items:center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
nav h1 { margin:0; font-size:22px; }
nav a { color:#fff; text-decoration:none; margin-left:15px; padding:6px 12px; border-radius:6px; transition:0.3s; background:red; }
nav a:hover { background:darkred; }

.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
}

.user-card {
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    margin-bottom:30px;
}

.user-card h3 {
    margin:0 0 15px 0;
    color:#2C3E50;
    font-size:18px;
}

table {
    width:100%;
    border-collapse: collapse;
    margin-top:10px;
}

th, td {
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
    font-size:14px;
}

th {
    background:#2C3E50;
    color:white;
}

tr:nth-child(even){ background:#f8f9fa; }
tr:hover{ background:#e8f5e9; }

.credit{ color:green; font-weight:bold; }
.debit{ color:red; font-weight:bold; }

.subtable {
    background:#fafafa;
    margin-top:10px;
    border-radius:6px;
    overflow:hidden;
    box-shadow:0 2px 6px rgba(0,0,0,0.05);
}

.logout {
    transition:0.3s;
}
</style>
</head>
<body>

<nav>
    <h1>Admin Dashboard</h1>
    <div>
        <a href="?logout=1" class="logout">Logout</a>
    </div>
</nav>

<div class="container">
<?php
$users = $conn->query("SELECT * FROM users");
if ($users->num_rows > 0):
    while ($u = $users->fetch_assoc()):
?>
    <div class="user-card">
        <h3>User: <?= htmlspecialchars($u['username']) ?> (<?= $u['email'] ?>)</h3>
        <?php
        $accounts = $conn->query("SELECT * FROM accounts WHERE user_id = {$u['id']}");
        if ($accounts->num_rows > 0):
        ?>
            <table>
                <tr><th>Account ID</th><th>Account Name</th><th>Balance</th></tr>
                <?php while($acc = $accounts->fetch_assoc()): ?>
                    <tr>
                        <td><?= $acc['id'] ?></td>
                        <td><?= htmlspecialchars($acc['account_name']) ?></td>
                        <td><?= number_format($acc['balance'],2) ?></td>
                    </tr>
                    <?php
                    $transactions = $conn->query("SELECT * FROM transactions WHERE account_id = {$acc['id']} ORDER BY id DESC");
                    if ($transactions->num_rows > 0):
                    ?>
                        <tr><td colspan="3">
                            <table class="subtable">
                                <tr><th>ID</th><th>Type</th><th>Amount</th><th>Description</th><th>Balance After</th><th>Date</th></tr>
                                <?php while($tx = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $tx['id'] ?></td>
                                        <td class="<?= $tx['type']=='credit'?'credit':'debit' ?>"><?= ucfirst($tx['type']) ?></td>
                                        <td><?= number_format($tx['amount'],2) ?></td>
                                        <td><?= htmlspecialchars($tx['description']) ?></td>
                                        <td><?= number_format($tx['balance_after'],2) ?></td>
                                        <td><?= $tx['created_at'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </table>
                        </td></tr>
                    <?php endif; ?>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No accounts found.</p>
        <?php endif; ?>
    </div>
<?php
    endwhile;
else:
    echo "<p>No users found.</p>";
endif;
?>
</div>
</body>
</html>
