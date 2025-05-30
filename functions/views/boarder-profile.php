<?php
include_once 'functions/connection.php';


// Validate and sanitize the ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid boarder ID.');
}

// Correct query with parameterized placeholder
$sql = 'SELECT b.*, r.rent FROM boarders b
        INNER JOIN rooms r ON b.room = r.id
        WHERE b.id = ?';

// Prepare and bind parameter correctly
$stmt = $db->prepare($sql);
$stmt->execute([$id]);

// Fetch result
$results = $stmt->fetch();

if (!$results) {
    exit('Boarder not found.');
}

// Assign variables
$fullname = $results['fullname'];
$phone = $results['phone'];
$address = $results['address'];
$room = $results['room'];
$rent = $results['rent'];
$type = $results['type'];
$profile_picture = $results['profile_picture'];
$proof_of_identity = $results['proof_of_identity'];
?>
