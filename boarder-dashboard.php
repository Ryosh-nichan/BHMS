<?php
include_once 'functions/connection.php';
include_once 'functions/authentication.php';

session_start();

if (!isset($_SESSION['id']) || $_SESSION['level'] !== '0') {
    header('Location: index.php');
    exit;
}

$boarderId = $_SESSION['id'];

$sql = "SELECT fullname, room, 
               (SELECT due_date FROM payments WHERE boarder = :id ORDER BY id DESC LIMIT 1) AS due_date, 
               (SELECT amount FROM payments WHERE boarder = :id ORDER BY id DESC LIMIT 1) AS amount
        FROM boarders 
        WHERE id = :id";

$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $boarderId, PDO::PARAM_INT);
$stmt->execute();
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    $info = [
        'fullname' => 'Boarder',
        'room' => 'N/A',
        'due_date' => null,
        'amount' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Boarder Dashboard - BHRMS</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css" />
</head>
<body>
    <nav class="navbar navbar-light bg-light shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">BHMS - Boarder Panel</a>
            <div>
                <a class="btn btn-outline-primary me-2" href="boarder-payments.php">Payments</a>
                <a class="btn btn-danger" href="functions/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-3">Welcome, <?= htmlspecialchars($info['fullname']) ?>!</h3>
        <div class="card mb-4">
            <div class="card-header">
                Rental Information
            </div>
            <div class="card-body">
                <p><strong>Room:</strong> <?= htmlspecialchars($info['room']) ?></p>
                <p><strong>Rent Amount:</strong> ₱<?= number_format($info['amount'], 2) ?></p>
                <p><strong>Due Date:</strong> <?= $info['due_date'] ? date('F d, Y', strtotime($info['due_date'])) : 'No due date' ?></p>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>


