<?php
include '../config/database.php';

$booking_id = mysqli_real_escape_string($conn, $_POST['booking_id'] ?? '');
$action     = mysqli_real_escape_string($conn, $_POST['action'] ?? ''); // 'verify' or 'reject'
$admin_id   = intval($_POST['admin_id'] ?? 0);
$note       = mysqli_real_escape_string($conn, $_POST['note'] ?? '');

if(!$booking_id || !in_array($action, ['verify','reject'])){
    echo json_encode(['status'=>'error','message'=>'Invalid parameters']);
    exit;
}

$now = date('Y-m-d H:i:s');

if($action === 'verify'){
    mysqli_query($conn, "UPDATE payments SET status='verified', verified_at='$now', verified_by=$admin_id WHERE booking_id='$booking_id'");
    mysqli_query($conn, "UPDATE bookings SET status='confirmed' WHERE id='$booking_id'");
    echo json_encode(['status'=>'success','new_booking_status'=>'confirmed']);
} else {
    mysqli_query($conn, "UPDATE payments SET status='rejected', notes='$note' WHERE booking_id='$booking_id'");
    // Reset booking back to awaiting_payment so customer can re-upload
    // Give another 30 minutes
    $new_deadline = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    mysqli_query($conn, "UPDATE bookings SET status='awaiting_payment', payment_deadline='$new_deadline' WHERE id='$booking_id'");
    echo json_encode(['status'=>'success','new_booking_status'=>'awaiting_payment']);
}
