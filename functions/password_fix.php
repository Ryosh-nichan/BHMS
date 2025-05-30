<?php
include_once 'connection.php';

echo "<h2>Password Hash Checker and Fixer</h2>";

// Get all users and check their password hashes
$sql = "SELECT id, username, password, role FROM users";
$stmt = $db->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Current Password Hash Status:</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Username</th><th>Role</th><th>Hash Length</th><th>Hash Preview</th><th>Status</th>";
echo "</tr>";

$problematicUsers = [];
foreach ($users as $user) {
    $hashLength = strlen($user['password']);
    $hashPreview = substr($user['password'], 0, 20) . '...';
    $isValidHash = ($hashLength == 60 && strpos($user['password'], '$2y$') === 0);
    
    echo "<tr>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "<td>$hashLength</td>";
    echo "<td>$hashPreview</td>";
    echo "<td>" . ($isValidHash ? '✅ Valid' : '❌ Invalid') . "</td>";
    echo "</tr>";
    
    if (!$isValidHash) {
        $problematicUsers[] = $user;
    }
}
echo "</table>";

if (!empty($problematicUsers)) {
    echo "<h3>⚠️ Password Hash Issues Found!</h3>";
    echo "<p>The following users have invalid password hashes that need to be fixed:</p>";
    
    foreach ($problematicUsers as $user) {
        echo "<div style='background-color: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
        echo "<strong>User: {$user['username']}</strong><br>";
        echo "Current hash: " . htmlspecialchars($user['password']) . "<br>";
        echo "Length: " . strlen($user['password']) . " characters (should be 60)<br>";
        echo "</div>";
    }
    
    echo "<h3>Fix Password Hashes</h3>";
    echo "<p>Enter new passwords for each user below:</p>";
    
    echo "<form method='post'>";
    foreach ($problematicUsers as $user) {
        echo "<div style='margin: 15px 0; padding: 10px; border: 1px solid #ddd;'>";
        echo "<label><strong>New password for {$user['username']}:</strong></label><br>";
        echo "<input type='password' name='new_password[{$user['id']}]' placeholder='Enter new password' required>";
        echo "<input type='hidden' name='user_id[{$user['id']}]' value='{$user['id']}'>";
        echo "<input type='hidden' name='username[{$user['id']}]' value='{$user['username']}'>";
        echo "</div>";
    }
    echo "<br><input type='submit' name='fix_passwords' value='Update All Passwords' style='background-color: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
    echo "</form>";
    
    if (isset($_POST['fix_passwords'])) {
        echo "<h3>Updating Passwords...</h3>";
        foreach ($_POST['new_password'] as $userId => $newPassword) {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password = ? WHERE id = ?";
                $updateStmt = $db->prepare($updateSql);
                
                if ($updateStmt->execute([$hashedPassword, $userId])) {
                    echo "<p>✅ Updated password for user: {$_POST['username'][$userId]}</p>";
                } else {
                    echo "<p>❌ Failed to update password for user: {$_POST['username'][$userId]}</p>";
                }
            }
        }
        echo "<p><strong>🎉 Password update complete! <a href=''>Refresh page</a> to see updated status.</strong></p>";
    }
} else {
    echo "<h3>✅ All password hashes are valid!</h3>";
}

// Test login section
echo "<hr>";
echo "<h3>Test Login After Fix</h3>";
echo "<form method='post'>";
echo "<label>Username:</label><br>";
echo "<select name='test_username' required>";
echo "<option value=''>-- Select User --</option>";
foreach ($users as $user) {
    echo "<option value='{$user['username']}'>{$user['username']} ({$user['role']})</option>";
}
echo "</select><br><br>";
echo "<label>Password:</label><br>";
echo "<input type='password' name='test_password' required><br><br>";
echo "<input type='submit' name='test_login' value='Test Login'>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $testUsername = trim($_POST['test_username']);
    $testPassword = trim($_POST['test_password']);
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$testUsername]);
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>Login Test Results:</h4>";
    if ($testUser) {
        echo "<div style='background-color: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
        echo "✅ User found: {$testUser['username']}<br>";
        echo "Role: {$testUser['role']}<br>";
        echo "Hash length: " . strlen($testUser['password']) . "<br>";
        
        if (password_verify($testPassword, $testUser['password'])) {
            echo "<strong style='color: green;'>✅ PASSWORD VERIFICATION: SUCCESS!</strong><br>";
            echo "🎉 Login should work now!";
        } else {
            echo "<strong style='color: red;'>❌ PASSWORD VERIFICATION: FAILED</strong><br>";
            echo "The password you entered is incorrect.";
        }
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "❌ User not found in database";
        echo "</div>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
input, select { padding: 8px; margin: 5px 0; }
input[type="submit"] { cursor: pointer; }
</style>