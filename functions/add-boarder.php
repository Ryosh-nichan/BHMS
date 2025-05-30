<?php
// add-boarder.php
include_once 'connection.php';

$fullname = $_POST['fullname'];
$username = $_POST['username'];
$password = $_POST['password'];
$phone = $_POST['phone'];
$sex = $_POST['sex'];
$address = $_POST['address'];
$start_date = $_POST['start_date'];
$room = $_POST['room'];
$type = $_POST['type'];

// Hash the password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Fix the path
$target_dir = __DIR__ . '/../img/';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Sanitize filenames
function sanitize_filename($name) {
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
}

$clean_fullname = str_replace(' ', '_', $fullname);

function prepare_file($file, $prefix, $target_dir) {
    $filename = $prefix . '_' . basename($file["name"]);
    $filename = sanitize_filename($filename);

    // Convert .jfif to .jpg
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'jfif') {
        $filename = preg_replace('/\.jfif$/i', '.jpg', $filename);
    }

    $target_path = $target_dir . $filename;
    if (!move_uploaded_file($file["tmp_name"], $target_path)) {
        die("Error uploading file: $filename");
    }

    return $filename;
}

$db_profile_path = prepare_file($_FILES["profile"], $clean_fullname, $target_dir);
$db_proof_path = prepare_file($_FILES["proof"], $clean_fullname, $target_dir);

// Check for duplicate fullname, phone, or username
$sql = "SELECT * FROM boarders WHERE fullname = :fullname OR phone = :phone OR username = :username";
$stmt = $db->prepare($sql);
$stmt->bindParam(':fullname', $fullname);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':username', $username);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    header('Location: ../boarders.php?type=error&message=' . urlencode('Boarder with the same name, phone or username already exists'));
    exit;
}

// Insert into boarders table
$sql = "INSERT INTO boarders 
        (fullname, username, password, phone, sex, address, start_date, room, type, profile_picture, proof_of_identity) 
        VALUES 
        (:fullname, :username, :password, :phone, :sex, :address, :start_date, :room, :type, :profile, :proof)";
$stmt = $db->prepare($sql);
$stmt->bindParam(':fullname', $fullname);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':sex', $sex);
$stmt->bindParam(':address', $address);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':room', $room);
$stmt->bindParam(':type', $type);
$stmt->bindParam(':profile', $db_profile_path);
$stmt->bindParam(':proof', $db_proof_path);
$stmt->execute();

$boarder = $db->lastInsertId();

// Insert into users table to enable login
$sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'tenant')";
$stmt = $db->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashed_password);
$stmt->execute();

// Create payment record
$sql = "SELECT rent FROM rooms WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $room);
$stmt->execute();
$rent = $stmt->fetchColumn();

$sql = "INSERT INTO payments (boarder, room, amount, total) VALUES (:boarder, :room, :amount, :total)";
$stmt = $db->prepare($sql);
$stmt->bindParam(':boarder', $boarder);
$stmt->bindParam(':room', $room);
$stmt->bindParam(':amount', $rent);
$stmt->bindParam(':total', $rent);
$stmt->execute();

$paymentId = $db->lastInsertId();

// Update room status
$sql = "UPDATE rooms SET status = 'Occupied' WHERE id = :room";
$stmt = $db->prepare($sql);
$stmt->bindParam(':room', $room);
$stmt->execute();

// Redirect to receipt
header('Location: ../reciept.php?id=' . $paymentId);
exit;
?>

