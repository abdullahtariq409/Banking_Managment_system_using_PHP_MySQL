<?php
include 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("location: index.php");
    exit();
}

if (!isset($_POST['submit'])) {
    header("location: accounts.php");
    exit();
}

$account_id = (int)$_POST['account_id'];
$type = $_POST['type'] === 'debit' ? 'debit' : 'credit';
$amount = (float)$_POST['amount'];
$description = trim($_POST['description']);

// Validate amount
if ($amount <= 0) {
    die("Amount must be greater than zero. <a href='accounts.php'>Back</a>");
}

// Verify the account belongs to logged-in user
$username = $_SESSION['username'];
$stmt = $conn->prepare("
    SELECT a.id, a.balance 
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

$current_balance = (float)$account['balance'];

if ($type === 'debit' && $amount > $current_balance) {
    die("Insufficient balance for this debit. <a href='account_view.php?id={$account_id}'>Back</a>");
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert transaction
    $tstmt = $conn->prepare("
        INSERT INTO transactions (account_id, type, amount, description) 
        VALUES (?, ?, ?, ?)
    ");
    $tstmt->bind_param("isds", $account_id, $type, $amount, $description);
    if (!$tstmt->execute()) throw new Exception($tstmt->error);
    $tstmt->close();

    // Update account balance
    $new_balance = ($type === 'credit') ? $current_balance + $amount : $current_balance - $amount;

    $ustmt = $conn->prepare("UPDATE accounts SET balance=? WHERE id=?");
    $ustmt->bind_param("di", $new_balance, $account_id);
    if (!$ustmt->execute()) throw new Exception($ustmt->error);
    $ustmt->close();

    $conn->commit();
    header("Location: account_view.php?id={$account_id}");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Transaction failed: " . $e->getMessage() . " <a href='account_view.php?id={$account_id}'>Back</a>";
    exit();
}
?>
