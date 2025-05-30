<?php
//functions/add-room.php//
include_once ('connection.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pax = $_POST['pax'] ?? null;
    $rent = $_POST['rent'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($pax && $rent && $status) {
        $sql = "INSERT INTO rooms (pax, rent, status) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$pax, $rent, $status]);

        header("Location: ../rooms.php?success=RoomAdded");
        exit();
    } else {
        echo "Missing required fields.";
    }
}
