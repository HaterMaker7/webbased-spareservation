<?php
include '../config/database.php';

$therapist_id = mysqli_real_escape_string($conn, $_POST['therapist_id'] ?? '');
$service_id   = mysqli_real_escape_string($conn, $_POST['service_id'] ?? '');
$date         = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
$time         = mysqli_real_escape_string($conn, $_POST['time'] ?? '');
$exclude_id   = mysqli_real_escape_string($conn, $_POST['exclude_id'] ?? '');

if(!$therapist_id || !$service_id || !$date || !$time){
    echo json_encode(['available'=>false,'message'=>'Missing parameters']);
    exit;
}

// Get service duration
$sv = mysqli_fetch_assoc(mysqli_query($conn, "SELECT duration FROM services WHERE id='$service_id'"));
if(!$sv){ echo json_encode(['available'=>false,'message'=>'Service not found']); exit; }

$duration = intval($sv['duration']);

// The requested slot: start=time, end=time+duration
// A conflict exists if any existing booking for this therapist on this date overlaps:
//   existing.time < requested_end AND existing.end_time > requested_start
$exclude_clause = $exclude_id ? "AND b.id != '$exclude_id'" : '';

$sql = "SELECT b.id, b.time, b.end_time, s.name AS service_name
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE b.therapist_id = '$therapist_id'
          AND b.date = '$date'
          AND b.status NOT IN ('cancelled')
          $exclude_clause
          AND b.time < ADDTIME('$time', SEC_TO_TIME($duration * 60))
          AND b.end_time > '$time'";

$result = mysqli_query($conn, $sql);
$conflicts = [];
while($row = mysqli_fetch_assoc($result)) $conflicts[] = $row;

if(count($conflicts) > 0){
    echo json_encode([
        'available' => false,
        'conflicts' => $conflicts,
        'message'   => 'Therapist is busy during this time slot'
    ]);
} else {
    echo json_encode(['available' => true]);
}
