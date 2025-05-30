<?php
session_start();
require_once '../functions/connection.php'; // or your database connection file

if (isset($_POST['edit_admin'])) {
    // Get form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Get admin ID from session
    $admin_id = $_SESSION['admin_id']; // or however you store admin ID
    
    try {
        // Update basic info
        $stmt = $pdo->prepare("UPDATE admins SET fullname=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->execute([$fullname, $email, $phone, $address, $admin_id]);
        
        // Update password if provided
        if (!empty($current_password) && !empty($new_password)) {
            // Verify current password first
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id=?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();
            
            if (password_verify($current_password, $admin['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password=? WHERE id=?");
                $stmt->execute([$hashed_password, $admin_id]);
            } else {
                $_SESSION['error'] = "Current password is incorrect";
                header("Location: ../admin-profile.php");
                exit();
            }
        }
        
        $_SESSION['success'] = "Profile updated successfully";
        header("Location: ../account.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
        header("Location: ../account.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request";
    header("Location: ../account.php");
    exit();
}