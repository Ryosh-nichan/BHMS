<?php 
// remove_boarder.php
include_once 'connection.php';

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Location: ../boarders.php?type=error&message=Invalid boarder ID');
    exit;
}

$id = $_POST['id'];

try {
    // Start transaction
    $db->beginTransaction();
    
    // 1. First get the room ID assigned to this boarder
    $sql = "SELECT room FROM boarders WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $boarder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$boarder) {
        throw new Exception("Boarder not found");
    }
    
    $room_id = $boarder['room'];
    
    // 2. Delete dependent payments first
    $sql = "DELETE FROM payments WHERE boarder = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // 3. Then delete the boarder
    $sql = "DELETE FROM boarders WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // 4. Update room status to "Available" (not "Occupied" as in your original code)
    if ($room_id) {
        $sql = "UPDATE rooms SET status = 'Available' WHERE id = :room_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->execute();
    }
    
    // Commit transaction if all queries succeeded
    $db->commit();
    
    header('Location: ../boarders.php?type=success&message=Boarder removed successfully');
    exit;
    
} catch (PDOException $e) {
    // Roll back transaction on error
    $db->rollBack();
    header('Location: ../boarders.php?type=error&message=Database error: ' . $e->getMessage());
    exit;
} catch (Exception $e) {
    $db->rollBack();
    header('Location: ../boarders.php?type=error&message=' . $e->getMessage());
    exit;
}