<?php
// Start session first
session_start();

// Include required files
include_once 'functions/connection.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch user data using username from session
try {
    // Check database connection
    if (!isset($db) || $db === null) {
        throw new Exception("Database connection not established");
    }
    
    $stmt = $db->prepare("SELECT id, fullname, email, phone, address, role, username FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        // User not found in database, destroy session and redirect
        session_destroy();
        header("Location: login.php?error=User session invalid");
        exit();
    }
    
    // Store user_id in session for future use
    $_SESSION['user_id'] = $user_data['id'];
    
    // For backward compatibility, use user_data as admin_data
    $admin_data = $user_data;
    
} catch (PDOException $e) {
    error_log("Database error in dashboard: " . $e->getMessage());
    die("Database error: Please contact administrator");
} catch (Exception $e) {
    error_log("Error in dashboard: " . $e->getMessage());
    die("System error: Please contact administrator");
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard - BHMS</title>
    <meta name="description" content="Boarding House Rental Management System">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/Change-Password-floating-Label.css">
    <link rel="stylesheet" href="assets/css/Navbar-Right-Links-icons.css">
    <link rel="stylesheet" href="assets/css/Profile-with-data-and-skills.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <div class="d-flex flex-column" id="content-wrapper" style="background: rgb(255,255,255);">
            <nav class="navbar navbar-expand-lg shadow py-3 border mb-4 navbar-light">
                <div class="container-fluid"><span class="bs-icon-md bs-icon-rounded bs-icon-semi-white border rounded-circle border-primary-subtle shadow-lg d-flex justify-content-center align-items-center me-2 bs-icon"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16" class="bi bi-house-heart-fill">
                            <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.707L8 2.207 1.354 8.853a.5.5 0 1 1-.708-.707L7.293 1.5Z"></path>
                            <path d="m14 9.293-6-6-6 6V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9.293Zm-6-.811c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.691 0-5.018Z"></path>
                        </svg></span><a class="navbar-brand d-flex align-items-center" href="/"><span>&nbsp;BHRMS</span></a><button data-bs-toggle="collapse" data-bs-target="#navcol-1" class="navbar-toggler"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
                    <div class="collapse navbar-collapse" id="navcol-1">
                        <ul class="navbar-nav mx-auto">
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can see your Dashboard." data-bs-placement="bottom" href="dashboard.php" style="color:#393939;" title="Here you can see your Dashboard."><i class="fas fa-th-list"></i>&nbsp;Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can see your Dashboard." data-bs-placement="bottom" href="boarders.php" style="color:#393939;" title="Here you can view and manage the boarders."><i class="fas fa-users"></i>&nbsp;Boarders</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can see your Dashboard." data-bs-placement="bottom" href="rooms.php" style="color:#393939;" title="Here you can view and manage the rooms."><i class="fas fa-home"></i>&nbsp;Rooms</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can see your Dashboard." data-bs-placement="bottom" href="rentals.php" style="color:#393939;" title="Here you can view and transact payments."><i class="fas fa-credit-card"></i>&nbsp;Rental</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can see your Sales &amp; Transactions." data-bs-placement="bottom" href="reports.php" style="color:#393939;" title="Here you can view, export and print the sales reports."><i class="fas fa-chart-pie"></i>&nbsp;Reports</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tooltip" data-bss-tooltip="" data-bs-original-title="Here you can manage your account." data-bs-placement="bottom" href="account.php" style="color:#393939;" title="Here you can manage your account."><i class="fas fa-user-shield"></i>&nbsp;My Account</a></li>
                        </ul><a class="btn btn-light shadow" role="button" data-bs-original-title="Here you can logout your acccount." data-bs-placement="left" data-bs-toggle="tooltip" data-bss-tooltip="" href="functions/logout.php">logout</a>
                    </div>
                </div>
            </nav>
            <div id="content">
                <div class="container-fluid">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0">My Account</h3>
                    </div>
                </div>
            </div>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <div class="container">
        <div class="main-body">
            <div class="row gutters-sm">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center d-flex flex-column align-items-center"><img class="rounded-circle" src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="Admin" width="150">
                                <div class="mt-3">
                                    <h4><?php echo htmlspecialchars($user_data['fullname'] ?? $user_data['username']); ?></h4>
                                    <p class="text-secondary mb-1"><?php echo htmlspecialchars(ucfirst($user_data['role'])); ?></p>
                                    <p class="text-muted font-size-sm"><?php echo htmlspecialchars($user_data['address'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Full Name</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars($user_data['fullname'] ?? 'N/A'); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Username</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars($user_data['username']); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Email</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Phone</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars($user_data['phone'] ?? 'N/A'); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Address</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars($user_data['address'] ?? 'N/A'); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Role</h6>
                                </div>
                                <div class="col-sm-9 text-secondary"><span><?php echo htmlspecialchars(ucfirst($user_data['role'])); ?></span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12"><button class="btn btn-info" type="button" data-bs-target="#edit" data-bs-toggle="modal">Edit</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (strtolower($user_data['role']) === 'admin'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Activity Logs</h5>
                <div class="card-tools">
                    <button class="btn btn-tool" id="refreshLogs">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" id="logSearch" class="form-control float-right" placeholder="Search logs...">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="dataTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Activity</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Only include activity logs for admin users
                        if (file_exists('functions/views/activity.php')) {
                            include 'functions/views/activity.php'; 
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Activity logs not available</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal for detailed view -->
        <div class="modal fade" role="dialog" tabindex="-1" id="edit">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Profile</h4>
                        <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editAdminForm" method="post" action="functions/admin-edit.php" onsubmit="console.log('Form submitted'); console.log(new FormData(this)); return true;">
                        <div class="modal-body">
                            <div class="container">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" required value="<?php echo htmlspecialchars($admin_data['fullname'] ?? ''); ?>">
                                    <label for="fullname">Full Name</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>">
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" required value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>">
                                            <label for="phone">Phone</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Address" required value="<?php echo htmlspecialchars($admin_data['address'] ?? ''); ?>">
                                    <label for="address">Address</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Current Password">
                                            <label for="current_password">Current Password (leave blank if not changing)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                                            <label for="new_password">New Password</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="edit_admin" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/dataTables.buttons.min.js"></script>
    <script src="assets/js/jszip.min.js"></script>
    <script src="assets/js/pdfmake.min.js"></script>
    <script src="assets/js/vfs_fonts.js"></script>
    <script src="assets/js/buttons.html5.min.js"></script>
    <script src="assets/js/buttons.print.min.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/sweetalert.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <?php if (strtolower($user_data['role']) === 'admin'): ?>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                dom: 'Blfrtip',
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-primary'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-primary'
                    }
                ]
            });
        });

        document.getElementById('editAdminForm').addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('Form submission intercepted');
    
    // You can add validation here if needed
    
    // Proceed with form submission
    this.submit();
});
    </script>
    <?php endif; ?>
    
    <script>
    $(document).ready(function() {
        // Handle form submission
        $('#editAdminForm').on('submit', function(e) {
            // Validate password fields
            const currentPass = $('#current_password').val();
            const newPass = $('#new_password').val();
            
            if ((currentPass || newPass) && (!currentPass || !newPass)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Error',
                    text: 'You must fill both current and new password fields to change your password',
                });
                return false;
            }
            
            if (newPass && newPass.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Error',
                    text: 'New password must be at least 8 characters long',
                });
                return false;
            }
        });
        
        // Display any messages from session
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: '<?php echo $_SESSION['message']['type']; ?>',
                title: '<?php echo $_SESSION['message']['type'] === 'success' ? 'Success' : 'Error'; ?>',
                text: '<?php echo $_SESSION['message']['text']; ?>',
            });
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    });
    </script>
</body>

</html>