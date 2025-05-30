<?php
include_once 'connection.php';

echo "<h2>Password Tester</h2>";

if (isset($_POST['test_login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<h3>User Found:</h3>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Password Hash: " . substr($user['password'], 0, 30) . "...<br><br>";
        
        if (password_verify($password, $user['password'])) {
            echo "<div style='color: green; font-weight: bold;'>✅ PASSWORD CORRECT! Login should work.</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ PASSWORD INCORRECT</div>";
            echo "<p>Try common passwords like:</p>";
            echo "<ul>";
            echo "<li>admin</li>";
            echo "<li>admin123</li>";
            echo "<li>password</li>";
            echo "<li>123456</li>";
            echo "<li>landlord</li>";
            echo "<li>landlord1</li>";
            echo "</ul>";
        }
    } else {
        echo "<div style='color: red;'>❌ User not found</div>";
    }
}
?>

<form method="post">
    <h3>Test Login Credentials:</h3>
    <label>Username:</label><br>
    <select name="username" required>
        <option value="">-- Select User --</option>
        <option value="admin">admin</option>
        <option value="landlord1">landlord1</option>
    </select><br><br>
    
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    
    <input type="submit" name="test_login" value="Test Password">
</form>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
input, select { padding: 8px; margin: 5px 0; }
input[type="submit"] { background-color: #007cba; color: white; border: none; padding: 10px 20px; cursor: pointer; }
</style>