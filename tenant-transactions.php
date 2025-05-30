<?php
include_once 'functions/connection.php';
include_once 'functions/authentication.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Fetch tenant ID
$stmt = $db->prepare("SELECT id FROM boarders WHERE username = :username");
$stmt->execute([':username' => $username]);
$boarder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$boarder) {
    die("Boarder not found.");
}

// Fetch transaction records
$stmt = $db->prepare("SELECT amount, created_at FROM payments WHERE boarder = :boarder_id ORDER BY created_at DESC");
$stmt->execute([':boarder_id' => $boarder['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction History</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Your Payment History</h2>
    <a href="tenant-dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Amount Paid</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $txn): ?>
                <tr>
                    <td>₱<?= number_format($txn['amount'], 2) ?></td>
                    <td><?= date('F j, Y h:i A', strtotime($txn['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2">No transactions yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
