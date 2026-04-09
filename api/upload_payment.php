<?php
include '../config/database.php';

$booking_id = mysqli_real_escape_string($conn, $_POST['booking_id'] ?? '');

if(!$booking_id){
    echo json_encode(['status'=>'error','message'=>'Missing booking ID']);
    exit;
}

// Check booking still awaiting payment and not expired
$bk = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, payment_deadline, status FROM bookings WHERE id='$booking_id'"
));

if(!$bk){
    echo json_encode(['status'=>'error','message'=>'Booking not found']);
    exit;
}
if($bk['status'] === 'cancelled'){
    echo json_encode(['status'=>'error','message'=>'Booking has been cancelled due to payment timeout.']);
    exit;
}
if(strtotime($bk['payment_deadline']) < time()){
    mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id='$booking_id'");
    echo json_encode(['status'=>'error','message'=>'Payment deadline has passed. Booking cancelled.']);
    exit;
}

if(!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK){
    echo json_encode(['status'=>'error','message'=>'No file uploaded or upload error.']);
    exit;
}

$allowed = ['image/jpeg','image/png','image/gif','application/pdf'];
$mime = mime_content_type($_FILES['proof']['tmp_name']);
if(!in_array($mime, $allowed)){
    echo json_encode(['status'=>'error','message'=>'Only JPG, PNG, or PDF allowed.']);
    exit;
}

if($_FILES['proof']['size'] > 5 * 1024 * 1024){
    echo json_encode(['status'=>'error','message'=>'File too large. Max 5MB.']);
    exit;
}

$upload_dir = '../uploads/payments/';
if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
$filename = 'proof_' . $booking_id . '_' . time() . '.' . $ext;
$filepath = $upload_dir . $filename;

if(!move_uploaded_file($_FILES['proof']['tmp_name'], $filepath)){
    echo json_encode(['status'=>'error','message'=>'Failed to save file.']);
    exit;
}

// Update payment record
$rel_path = 'uploads/payments/' . $filename;
$now = date('Y-m-d H:i:s');

mysqli_query($conn, "UPDATE payments SET
    proof_filename = '$filename',
    proof_path     = '$rel_path',
    uploaded_at    = '$now',
    status         = 'uploaded'
    WHERE booking_id = '$booking_id'");

// Move booking to pending (awaiting admin confirmation)
mysqli_query($conn, "UPDATE bookings SET status='pending' WHERE id='$booking_id'");

echo json_encode(['status'=>'success','filename'=>$filename]);
