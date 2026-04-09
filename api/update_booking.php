<?php
include '../config/database.php';

$id     = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
$status = mysqli_real_escape_string($conn, $_POST['status'] ?? '');

$allowed = ['awaiting_payment','pending','confirmed','inprogress','done','cancelled'];
if(!$id || !in_array($status, $allowed)){
    echo json_encode(['status'=>'error','message'=>'Invalid parameters']);
    exit;
}

if(mysqli_query($conn, "UPDATE bookings SET status='$status' WHERE id='$id'")){
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
