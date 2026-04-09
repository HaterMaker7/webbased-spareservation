<?php
include '../config/database.php';

$user_id    = intval($_POST['user_id'] ?? 0);
$customer   = mysqli_real_escape_string($conn, $_POST['customer'] ?? '');
$phone      = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$email      = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$service_id = mysqli_real_escape_string($conn, $_POST['service'] ?? '');
$therapist_id = mysqli_real_escape_string($conn, $_POST['therapist'] ?? '');
$date       = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
$time       = mysqli_real_escape_string($conn, $_POST['time'] ?? '');
$notes      = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

if(!$customer || !$phone || !$service_id || !$date || !$time){
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

// Auto-assign therapist if not specified
if(!$therapist_id){
    $sv_dur = intval(mysqli_fetch_assoc(mysqli_query($conn, "SELECT duration FROM services WHERE id='$service_id'"))['duration'] ?? 60);
    $available = mysqli_query($conn,
        "SELECT id FROM therapists
         WHERE status='active'
           AND id NOT IN (
             SELECT therapist_id FROM bookings
             WHERE date='$date' AND status NOT IN ('cancelled')
             AND time < ADDTIME('$time', SEC_TO_TIME($sv_dur * 60))
             AND end_time > '$time'
           )
         ORDER BY RAND() LIMIT 1"
    );
    $row = mysqli_fetch_assoc($available);
    if(!$row){
        echo json_encode(['status'=>'conflict','message'=>'No therapists available for this time slot. Please choose another time.']);
        exit;
    }
    $therapist_id = $row['id'];
}

// Get service duration to compute end_time
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT duration, price FROM services WHERE id='$service_id'"));
if(!$sv){ echo json_encode(['status'=>'error','message'=>'Invalid service']); exit; }

$duration = intval($sv['duration']);
$price    = intval($sv['price']);

// Check therapist availability (overlap logic)
$conflict_sql = "SELECT id FROM bookings
    WHERE therapist_id = '$therapist_id'
      AND date = '$date'
      AND status NOT IN ('cancelled')
      AND time < ADDTIME('$time', SEC_TO_TIME($duration * 60))
      AND end_time > '$time'";
$conflict = mysqli_query($conn, $conflict_sql);
if(mysqli_num_rows($conflict) > 0){
    echo json_encode(['status'=>'conflict','message'=>'Selected therapist is already booked for an overlapping time slot.']);
    exit;
}

// Generate unique ID
do {
    $id = 'BK' . strtoupper(substr(md5(uniqid()),0,6));
    $check = mysqli_query($conn, "SELECT id FROM bookings WHERE id='$id'");
} while(mysqli_num_rows($check) > 0);

// Payment deadline = 1 hour from now
$deadline = date('Y-m-d H:i:s', strtotime('+1 hour'));

$sql = "INSERT INTO bookings
        (id, user_id, customer, phone, email, service_id, therapist_id, date, time, end_time, status, notes, created_at, payment_deadline)
        VALUES (
            '$id', $user_id, '$customer', '$phone', '$email',
            '$service_id', '$therapist_id', '$date', '$time',
            ADDTIME('$time', SEC_TO_TIME($duration * 60)),
            'awaiting_payment', '$notes', NOW(), '$deadline'
        )";

if(mysqli_query($conn, $sql)){
    // Create payment record
    mysqli_query($conn, "INSERT INTO payments (booking_id, status, amount) VALUES ('$id','awaiting',$price)");
    echo json_encode([
        'status'   => 'success',
        'id'       => $id,
        'deadline' => $deadline,
        'amount'   => $price
    ]);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
