<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

$username = $_SESSION['username'];
$account_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify account belongs to logged-in user
$stmt = $conn->prepare("
    SELECT a.id, a.account_name, a.balance, a.created_at, u.id AS user_id
    FROM accounts a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ? AND u.username = ?
    LIMIT 1
");
$stmt->bind_param("is", $account_id, $username);
$stmt->execute();
$res = $stmt->get_result();
$account = $res->fetch_assoc();
$stmt->close();

if (!$account) {
    die("Account not found or not authorized. <a href='accounts.php'>Back</a>");
}

// Fetch transactions
$tstmt = $conn->prepare("
    SELECT type, amount, description, created_at 
    FROM transactions 
    WHERE account_id = ? 
    ORDER BY created_at DESC
");
$tstmt->bind_param("i", $account_id);
$tstmt->execute();
$transactions = $tstmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account: <?php echo htmlspecialchars($account['account_name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">

    <h2>Account: <?php echo htmlspecialchars($account['account_name']); ?></h2>
    <p>Balance: <strong><?php echo number_format($account['balance'],2); ?> PKR</strong></p>
    <p>Created: <?php echo $account['created_at']; ?></p>

    <h3>Make a Transaction</h3>
    <form action="process_transaction.php" method="post" class="form-inline mb-4">
        <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
        <input type="number" name="amount" step="0.01" placeholder="Amount" class="form-control mr-2" required>
        <select name="type" class="form-control mr-2">
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
        </select>
        <input type="text" name="description" placeholder="Description" class="form-control mr-2">
        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
    </form>

    <h3>Transactions</h3>
    <?php if ($transactions->num_rows == 0): ?>
        <p>No transactions yet.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount (PKR)</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo ucfirst($row['type']); ?></td>
                        <td><?php echo number_format($row['amount'],2); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        <a href="accounts.php" class="btn btn-secondary">Back to Accounts</a>
        <a href="welcome.php" class="btn btn-info">Dashboard</a>
    </p>

</div>
</body>
</html>
<?php $tstmt->close(); ?>
