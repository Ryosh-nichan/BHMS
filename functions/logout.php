<?php
include_once 'connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current session values
$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? 'Unknown';

// Re-fetch user ID using username
$userId = null;
if ($username) {
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $userId = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Failed to fetch user ID for logout: " . $e->getMessage());
    }
}

// Proceed only if user ID was found
if ($userId) {
    $logMessage = "User ($role) $username logged out";

    try {
        $stmt = $db->prepare("INSERT INTO logs (user_id, logs, type) VALUES (:user_id, :logs, :type)");
        $stmt->execute([
            ':user_id' => $userId,
            ':logs' => $logMessage,
            ':type' => 'authentication'
        ]);
    } catch (PDOException $e) {
        error_log("Logging logout failed: " . $e->getMessage());
    }
} else {
    error_log("Logout: Could not determine user ID for username: $username");
}

// Clear session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to homepage
header('Location: ../index.php');
exit();
