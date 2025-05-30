<?php
include_once 'connection.php';

$id = $_POST['id'];
$pax = $_POST['pax'];
$rent = $_POST['rent'];

// Update room details
$sql = "UPDATE rooms SET pax = :pax, rent = :rent WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':pax', $pax);
$stmt->bindParam(':rent', $rent);
$stmt->bindParam(':id', $id);
$stmt->execute();

// Check if any boarder is assigned to this room
$check = $db->prepare("SELECT COUNT(*) FROM boarders WHERE room = :room_id");
$check->bindParam(':room_id', $id);
$check->execute();
$isOccupied = $check->fetchColumn();

// Update room status accordingly
$status = $isOccupied > 0 ? 'Occupied' : 'Available';
$updateStatus = $db->prepare("UPDATE rooms SET status = :status WHERE id = :id");
$updateStatus->bindParam(':status', $status);
$updateStatus->bindParam(':id', $id);
$updateStatus->execute();

header('Location: ../rooms.php?type=success&message=Room was updated successfully');
?>
