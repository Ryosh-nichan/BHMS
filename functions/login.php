<?php
//login.php//
include_once 'authentication.php';
include_once 'connection.php';
session_start();

$errorMessage = '';
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php?error=Invalid request method');
    exit();
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validate input
if (empty($username) || empty($password)) {
    header('Location: ../login.php?error=Please enter both username and password');
    exit();
}

try {
    // Check database connection
    if (!isset($db) || $db === null) {
        throw new Exception("Database connection not established");
    }

    // Prepare and execute query - Case insensitive username search
    $sql = "SELECT id, username, password, role FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1";
    $stmt = $db->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . implode(", ", $db->errorInfo()));
    }
    
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging output (uncomment for testing)
    /*
    echo "<pre>";
    echo "Debug Info:\n";
    echo "Username: " . htmlspecialchars($username) . "\n";
    echo "Password length: " . strlen($password) . "\n";
    echo "User found: " . ($user ? 'Yes' : 'No') . "\n";
    if ($user) {
        echo "User ID: " . $user['id'] . "\n";
        echo "Stored username: " . $user['username'] . "\n";
        echo "User role: " . $user['role'] . "\n";
        echo "Stored password hash: " . $user['password'] . "\n";
        echo "Hash verification: " . (password_verify($password, $user['password']) ? 'Success' : 'Failed') . "\n";
        echo "Hash info: ";
        print_r(password_get_info($user['password']));
    }
    echo "</pre>";
    exit(); // Stop execution for debugging
    */

    if ($user && !empty($user['password'])) {
        // Check if password is hashed (starts with $2y$ for bcrypt)
        if (strpos($user['password'], '$') === 0) {
            // Password is hashed, use password_verify
            $password_valid = password_verify($password, $user['password']);
        } else {
            // Password might be stored as plain text (not recommended)
            $password_valid = ($password === $user['password']);
        }

        if ($password_valid) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Log successful login
            error_log("Successful login for user: " . $user['username'] . " (ID: " . $user['id'] . ")");

            // Handle remember me functionality
            if (isset($_POST['remember']) && $_POST['remember'] == '1') {
                // Store username only, never store passwords in cookies
                setcookie('remembered_username', $username, time() + (86400 * 30), "/", "", false, true);
            } else {
                // Clear remember me cookie
                setcookie('remembered_username', '', time() - 3600, "/");
            }

            // Redirect based on role
            $redirect_url = '';
            switch (strtolower(trim($user['role']))) {
                case 'admin':
                    $redirect_url = '../dashboard.php';
                    break;
                case 'landlord':
                    $redirect_url = '../landlord-dashboard.php';
                    break;
                case 'tenant':
                    $redirect_url = '../tenant-dashboard.php';
                    break;
                default:
                    error_log("Unknown user role: " . $user['role'] . " for user: " . $user['username']);
                    header('Location: ../login.php?error=Unknown user role');
                    exit();
            }

            // Successful login redirect
            header('Location: ' . $redirect_url);
            exit();
            
        } else {
            // Password verification failed
            error_log("Login failed for user: $username - Invalid password");
            header('Location: ../login.php?error=Invalid username or password');
            exit();
        }
    } else {
        // User not found or password field is empty
        error_log("Login failed - User not found or empty password field: $username");
        header('Location: ../login.php?error=Invalid username or password');
        exit();
    }

} catch (Exception $e) {
    // Log the error with more detail
    error_log("Login error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Show user-friendly error
    header('Location: ../login.php?error=System error. Please try again.');
    exit();
}
?>

<?php if (!empty($errorMessage)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    });
</script>
<?php endif; ?>