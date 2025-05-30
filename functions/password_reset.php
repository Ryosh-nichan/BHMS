<?php
include_once 'connection.php';

echo "<h2>Password Reset Script</h2>";
echo "<p>Since you don't remember the original passwords, let's reset them to known values.</p>";

// Get all users
$sql = "SELECT id, username, role FROM users";
$stmt = $db->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Current Users:</h3>";
echo "<ul>";
foreach ($users as $user) {
    echo "<li><strong>{$user['username']}</strong> ({$user['role']})</li>";
}
echo "</ul>";

if (isset($_POST['reset_passwords'])) {
    echo "<h3>Resetting Passwords...</h3>";
    
    $passwordUpdates = [
        'admin' => 'admin123',
        'landlord1' => 'landlord123'
    ];
    
    foreach ($users as $user) {
        $username = $user['username'];
        $newPassword = $passwordUpdates[$username] ?? $username . '123';
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateSql = "UPDATE users SET password = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateSql);
        
        if ($updateStmt->execute([$hashedPassword, $user['id']])) {
            echo "<div style='background-color: #d4edda; padding: 10px; margin: 5px 0; border-left: 4px solid #28a745;'>";
            echo "✅ <strong>User: {$username}</strong><br>";
            echo "New Password: <strong>{$newPassword}</strong><br>";
            echo "Role: {$user['role']}";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update password for: {$username}</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🎉 Password Reset Complete!</h3>";
    echo "<div style='background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    echo "<h4>Login Credentials:</h4>";
    echo "<strong>Admin Login:</strong><br>";
    echo "Username: <code>admin</code><br>";
    echo "Password: <code>admin123</code><br><br>";
    
    echo "<strong>Landlord Login:</strong><br>";
    echo "Username: <code>landlord1</code><br>";
    echo "Password: <code>landlord123</code><br>";
    echo "</div>";
    
    echo "<p><a href='../index.php' style='background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Go to Login Page</a></p>";
}

if (!isset($_POST['reset_passwords'])) {
    echo "<h3>Reset to Default Passwords</h3>";
    echo "<p>This will set the following passwords:</p>";
    echo "<ul>";
    echo "<li><strong>admin</strong> → password: <code>admin123</code></li>";
    echo "<li><strong>landlord1</strong> → password: <code>landlord123</code></li>";
    echo "</ul>";
    
    echo "<form method='post'>";
    echo "<div style='background-color: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
    echo "<strong>⚠️ Warning:</strong> This will permanently change the passwords for all users above.";
    echo "</div>";
    echo "<input type='submit' name='reset_passwords' value='Reset All Passwords' style='background-color: #dc3545; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px;'>";
    echo "</form>";
}

// Test section (always show)
echo "<hr>";
echo "<h3>Test Login</h3>";
echo "<form method='post'>";
echo "<label>Username:</label><br>";
echo "<select name='test_username' required>";
echo "<option value=''>-- Select User --</option>";
foreach ($users as $user) {
    echo "<option value='{$user['username']}'>{$user['username']} ({$user['role']})</option>";
}
echo "</select><br><br>";
echo "<label>Password:</label><br>";
echo "<input type='password' name='test_password' placeholder='Try: admin123 or landlord123' required><br><br>";
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
        if (password_verify($testPassword, $testUser['password'])) {
            echo "<div style='background-color: #d4edda; padding: 15px; margin: 10px 0; border-left: 4px solid #28a745;'>";
            echo "<h4 style='color: green; margin: 0;'>✅ LOGIN SUCCESSFUL!</h4>";
            echo "Username: {$testUser['username']}<br>";
            echo "Role: {$testUser['role']}<br>";
            echo "Password: ✅ Correct<br>";
            echo "<strong>🎉 Your login system should work now!</strong>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
            echo "<h4 style='color: red; margin: 0;'>❌ LOGIN FAILED</h4>";
            echo "Username: {$testUser['username']} ✅ Found<br>";
            echo "Password: ❌ Incorrect<br>";
            echo "Try: <code>admin123</code> or <code>landlord123</code>";
            echo "</div>";
        }
    } else {
        echo "<div style='background-color: #f8d7da; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "❌ User not found in database";
        echo "</div>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
code { background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
input, select { padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
input[type="submit"] { cursor: pointer; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>