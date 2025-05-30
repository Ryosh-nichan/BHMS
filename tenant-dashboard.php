<?php
include_once 'functions/connection.php';
include_once 'functions/authentication.php';

// Get tenant username from session
$tenant_username = $_SESSION['username'];

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

try {
    // Get tenant information with payment history
    $stmt = $db->prepare("
         SELECT b.*, r.rent, r.pax AS room_name,
           DATEDIFF(DATE_ADD(b.start_date, INTERVAL 1 MONTH), CURDATE()) AS days_due,
           DATE_FORMAT(DATE_ADD(b.start_date, INTERVAL 1 MONTH), '%M %d, %Y') AS due_date,
           (SELECT COALESCE(SUM(amount), 0) FROM payments p 
            WHERE p.boarder = b.id 
            AND p.created_at >= b.start_date 
            AND p.created_at < DATE_ADD(b.start_date, INTERVAL 1 MONTH)) AS paid_amount
    FROM boarders b
    LEFT JOIN rooms r ON b.room = r.id
    WHERE b.username = :username
    ");
    
    $stmt->bindParam(':username', $tenant_username, PDO::PARAM_STR);
    $stmt->execute();
    $boarder = $stmt->fetch(PDO::FETCH_ASSOC);

    // Initialize variables
    $room_status = 'Not Assigned';
    $payment_status = 'No Payment Due';
    $payment_status_class = 'info';
    $due_date_display = 'N/A';
    $status_class = 'secondary';
    $total_amount = 0;
    $due_date = 'N/A';
    $base_rent = 0;
    $current_period_paid = false;

    if (!empty($boarder['room'])) {
    $room_status = 'Room ' . $boarder['room']; // or use $boarder['room_name'] if joining with room name
    }

           if (!empty($boarder['start_date'])) {
    $base_rent = $boarder['rent'];
    $paid_amount = $boarder['paid_amount'] ?? 0;
    
    // Calculate the current billing period using DateTime
    $current_date = new DateTime();
    $start_date = new DateTime($boarder['start_date']);
    $due_date = clone $start_date;
    $due_date->add(new DateInterval('P1M')); // Add 1 month
    
    // Format the due date for display
    $due_date_display_formatted = $due_date->format('F j, Y');
    
    // Calculate days until/since due date
    $interval = $due_date->diff($current_date);
    $daysDue = $interval->days;
    if ($current_date > $due_date) {
        $daysDue = -$daysDue; // Make negative if overdue
    }
    
    // 1. Calculate Late Fee (5% per week)
    $late_fee = 0;
    $weeks_overdue = 0;
    if ($daysDue < 0) {
        $weeks_overdue = ceil(abs($daysDue)/7);
        $late_percentage = min($weeks_overdue * 5, 50);
        $late_fee = $base_rent * ($late_percentage/100);
    }

    // 2. Calculate Total Amount Due
    $total_amount = $base_rent + $late_fee;
    
    // 3. Calculate Remaining Balance
    $remaining_balance = max($total_amount - $paid_amount, 0);
    
    // 4. Check Payment Status
    if ($remaining_balance <= 0 && $paid_amount > 0) {
        $payment_status = 'Paid';
        $payment_status_class = 'success';
        $current_period_paid = true;
    } elseif ($daysDue < 0) {
        $payment_status = 'Overdue';
        $payment_status_class = 'danger';
        $current_period_paid = false;
    } else {
        $payment_status = 'Unpaid';
        $payment_status_class = 'warning';
        $current_period_paid = false;
    }
    
    // 5. Due Date Status
    if ($daysDue > 0) {
        $status_class = 'success';
        $due_date_display = 'Due in ' . $daysDue . ' days';
    } elseif ($daysDue == 0) {
        $status_class = 'warning';
        $due_date_display = 'Due Today';
    } else {
        $status_class = 'danger';
        $due_date_display = 'Overdue by ' . abs($daysDue) . ' days';
    }
    
    // Update the due date variable for display
    $due_date = $due_date_display_formatted;
    $amount_due = $remaining_balance;
}
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">
<head>
    <!-- Your existing head content -->
     <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/Navbar-Right-Links-icons.css">

</head>
<body id="page-top">
    <div id="wrapper">
        <div class="d-flex flex-column" id="content-wrapper" style="background: rgb(255,255,255);">
            <!-- Your existing navbar -->
             <nav class="navbar navbar-expand-lg shadow py-3 border mb-4 navbar-light">
                <div class="container-fluid">
                    <span class="bs-icon-md bs-icon-rounded bs-icon-semi-white border rounded-circle border-primary-subtle shadow-lg d-flex justify-content-center align-items-center me-2 bs-icon"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" class="bi bi-house-heart-fill">
                            <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.707L8 2.207 1.354 8.853a.5.5 0 1 1-.708-.707L7.293 1.5Z"></path>
                            <path d="m14 9.293-6-6-6 6V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9.293Zm-6-.811c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.691 0-5.018Z"></path>
                        </svg>
                        <i class="fas fa-house-user"></i>
                    </span>
                    <a class="navbar-brand d-flex align-items-center" href="#"><span>&nbsp;BHRMS</span></a>
                    <button data-bs-toggle="collapse" data-bs-target="#navcol-1" class="navbar-toggler">
                        <span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navcol-1">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item"><a class="nav-link active" href="tenant-dashboard.php"><i class="fas fa-th-list"></i>&nbsp;Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link active" href="tenant-transactions.php"><i class="fas fa-credit-card"></i>&nbsp;Transactions</a></li>
                        </ul>
                        <a class="btn btn-light shadow" href="functions/logout.php">Logout</a>
                    </div>
                </div>
            </nav>
            
            <div id="content">
                <div class="container-fluid">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0">Hi! <?= ucfirst($_SESSION['username']);?></h3>
                    </div>

                    <div class="row">
                        <!-- Room Card -->
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary fw-bold text-xs mb-1">Room</div>
                                            <div class="text-dark fw-bold h5 mb-0"><?= htmlspecialchars($room_status)?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-door-open fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status Card -->
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-<?= $payment_status_class ?> py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-<?= $payment_status_class ?> fw-bold text-xs mb-1">Payment Status</div>
                                            <div class="text-dark fw-bold h5 mb-0"><?= $payment_status ?></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-credit-card fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Due Date Status Card -->
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-<?= $status_class ?> py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-<?= $status_class ?> fw-bold text-xs mb-1">Due Date Status</div>
                                            <div class="text-dark fw-bold h5 mb-0"><?= $due_date_display ?></div>
                                            <small class="text-muted">Due Date: <?= $due_date ?></small>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    
                    <!-- Amount Due Card - Fixed -->
                    <div class="col-md-6 col-xl-3 mb-4">
                        <div class="card shadow border-start-<?= ($total_amount > 0) ? 'danger' : 'success' ?> py-2">
                            <div class="card-body">
                                <div class="row align-items-center no-gutters">
                                    <div class="col me-2">
                                        <div class="text-uppercase text-<?= ($total_amount > 0) ? 'danger' : 'success' ?> fw-bold text-xs mb-1">
                                            Amount Due
                                        </div>
                                        <div class="text-dark fw-bold h5 mb-0">₱<?= number_format($total_amount, 2) ?></div>
                                        <?php if ($total_amount > 0): ?>
                                            <small class="text-muted">Base Rent: ₱<?= number_format($base_rent, 2) ?></small>
                                            <?php if ($daysDue < 0): ?>
                                                <small class="text-danger">(+ ₱<?= number_format($total_amount - $base_rent, 2) ?> late fee)</small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pay Now Button (matches rentals.php logic) -->
                    <?php if ($boarder && !$current_period_paid && $daysDue <= 5): ?>
                        <div class="text-center">
                            <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#pay" 
                               data-id="<?= $boarder['id'] ?>" data-room="<?= $boarder['room'] ?>" data-total="<?= $total_amount ?>">
                               <i class="far fa-money-bill-alt"></i>&nbsp;Pay Now
                            </a>
                        </div>
                        
                        <!-- Payment Modal -->
                        <div class="modal fade" id="pay" tabindex="-1" aria-labelledby="payLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="POST" action="tenant-transactions.php">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="payLabel">Process Payment</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="modal_id">
                                            <input type="hidden" name="room" id="modal_room">
                                            <input type="hidden" name="total" id="modal_total">
                                            <div class="mb-3">
                                                <label for="amount" class="form-label">Amount Paid (₱)</label>
                                                <input type="number" name="amount" class="form-control" 
                                                    min="1" step="0.01" 
                                                    max="<?= $total_amount ?>" 
                                                    value="<?= $total_amount ?>"
                                                    required>
                                            </div>
                                            <div class="alert alert-secondary">
                                                <div>Total Due: ₱<span id="modal_total_display"><?= number_format($total_amount, 2) ?></span></div>
                                                <?php if (!$current_period_paid && $total_amount > 0): ?>
                                                    <div class="text-danger">Includes late fee: ₱<?= number_format($total_amount - $base_rent, 2) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning">
                                        <small>
                                            Base Rent: ₱<?= number_format($base_rent, 2) ?><br>
                                            Late Fee: ₱<?= number_format($late_fee, 2) ?><br>
                                            Total Due: ₱<?= number_format($total_amount, 2) ?>
                                        </small>
                                    </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Confirm Payment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <script>
                        document.querySelectorAll('a[data-bs-target="#pay"]').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                const room = this.getAttribute('data-room');
                                const total = this.getAttribute('data-total');
                                
                                document.getElementById('modal_id').value = id;
                                document.getElementById('modal_room').value = room;
                                document.getElementById('modal_total').value = total;
                                document.getElementById('modal_total_display').textContent = parseFloat(total).toFixed(2);
                                
                                // Set max amount to total due
                                const amountInput = document.querySelector('#pay input[name="amount"]');
                                amountInput.max = total;
                                amountInput.value = total;
                            });
                        });
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/theme.js"></script>
</body>
</html>