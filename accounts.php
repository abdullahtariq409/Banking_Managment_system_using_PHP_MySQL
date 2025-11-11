<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's account
$account_stmt = $conn->prepare("SELECT id, account_name, balance FROM accounts WHERE user_id=? LIMIT 1");
$account_stmt->bind_param("i", $user_id);
$account_stmt->execute();
$account_result = $account_stmt->get_result();

if ($account_result->num_rows == 0) {
    if (isset($_POST['create_account'])) {
        $account_name = trim($_POST['account_name']);
        $initial_balance = (float)$_POST['initial_balance'];

        $insert = $conn->prepare("INSERT INTO accounts (user_id, account_name, balance) VALUES (?, ?, ?)");
        $insert->bind_param("isd", $user_id, $account_name, $initial_balance);
        if ($insert->execute()) {
            if ($initial_balance > 0) {
                $account_id = $insert->insert_id;
                $desc = "Initial deposit";
                $tstmt = $conn->prepare("INSERT INTO transactions (account_id, type, amount, description, balance_after) VALUES (?, 'credit', ?, ?, ?)");
                $tstmt->bind_param("idsd", $account_id, $initial_balance, $desc, $initial_balance);
                $tstmt->execute();
                $tstmt->close();
            }
            header("Refresh:0");
        } else {
            $msg = "Error creating account: " . $insert->error;
        }
    }
    ?>
    <h2>Create Your Bank Account</h2>
    <?php if(isset($msg)) echo "<p style='color:red;'>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="account_name" placeholder="Account Name" required><br><br>
        <input type="number" step="0.01" name="initial_balance" value="0.00" required><br><br>
        <button type="submit" name="create_account">Create Account</button>
    </form>
    <?php
    exit();
}

$account = $account_result->fetch_assoc();
$account_id = $account['id'];
$current_balance = $account['balance'];

// Handle credit/debit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_tx'])) {
    $type = $_POST['type'];
    $amount = (float)$_POST['amount'];
    $description = trim($_POST['description']);

    if ($amount <= 0) {
        $msg = "Enter valid amount.";
    } elseif ($type === 'debit' && $amount > $current_balance) {
        $msg = "Insufficient balance!";
    } else {
        $new_balance = ($type === 'credit') ? $current_balance + $amount : $current_balance - $amount;

        $stmt = $conn->prepare("INSERT INTO transactions (account_id, type, amount, description, balance_after) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsd", $account_id, $type, $amount, $description, $new_balance);
        $stmt->execute();
        $stmt->close();

        $update = $conn->prepare("UPDATE accounts SET balance=? WHERE id=?");
        $update->bind_param("di", $new_balance, $account_id);
        $update->execute();
        $update->close();
        header("Refresh:0");
    }
}

// Fetch transactions
$tx_stmt = $conn->prepare("SELECT * FROM transactions WHERE account_id=? ORDER BY id DESC");
$tx_stmt->bind_param("i", $account_id);
$tx_stmt->execute();
$transactions = $tx_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banking Dashboard</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f0f4f8, #d9e4ec);
        margin: 0;
        padding: 0;
    }

    /* Navbar */
    nav {
        background: #2C3E50;
        color: #fff;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    nav h1 { margin: 0; font-size: 22px; }
    nav a {
        color: white;
        text-decoration: none;
        margin-left: 15px;
        font-size: 15px;
        transition: color 0.3s ease;
    }
    nav a:hover { color: #00b894; }

    /* Container */
    .container {
        max-width: 900px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    h2, h3 {
        text-align: center;
        color: #2C3E50;
    }

    p {
        font-size: 16px;
        text-align: center;
    }

    /* Form */
    form {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 30px;
    }

    input, select, button {
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    input, select {
        width: 200px;
    }

    button {
        background: #00b894;
        color: white;
        font-weight: bold;
        border: none;
        transition: 0.3s;
    }
    button:hover {
        background: #019874;
        transform: scale(1.05);
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    th, td {
        padding: 12px;
        text-align: center;
    }

    th {
        background: #2C3E50;
        color: white;
        font-weight: 500;
    }

    tr:nth-child(even) { background-color: #f8f9fa; }
    tr:hover { background-color: #e8f5e9; }

    .credit { color: green; font-weight: bold; }
    .debit { color: red; font-weight: bold; }

    .msg {
        text-align: center;
        color: red;
        font-weight: bold;
    }

    .balance-box {
        text-align: center;
        background: #ecf0f1;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 18px;
    }

</style>
</head>
<body>

<nav>
    <h1>My Banking System</h1>
    <div>
        <a href="welcome.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Welcome, <?php echo ucfirst($_SESSION['username']); ?></h2>
    <div class="balance-box">
        <p><strong>Account:</strong> <?php echo htmlspecialchars($account['account_name']); ?></p>
        <p><strong>Balance:</strong> <?php echo number_format($current_balance,2); ?> PKR</p>
    </div>

    <?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <form method="POST">
        <input type="number" step="0.01" name="amount" placeholder="Amount" required>
        <select name="type">
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
        </select>
        <input type="text" name="description" placeholder="Description">
        <button type="submit" name="submit_tx">Submit</button>
    </form>

    <h3>Transaction History</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Balance After</th>
            <th>Date</th>
        </tr>
        <?php while($r = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td class="<?php echo $r['type'] === 'credit' ? 'credit' : 'debit'; ?>">
                    <?php echo ucfirst($r['type']); ?>
                </td>
                <td><?php echo number_format($r['amount'],2); ?></td>
                <td><?php echo htmlspecialchars($r['description']); ?></td>
                <td><?php echo number_format($r['balance_after'],2); ?></td>
                <td><?php echo $r['created_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
