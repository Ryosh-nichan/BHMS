<?php
include_once 'connection.php';

$id = $_POST['id'];

$sql = "DELETE FROM rooms WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();


header('Location: ../rooms.php?type=success&message=Room was remove successfully');
?>