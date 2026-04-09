<?php
include '../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if($action === 'add'){
    $id         = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
    $name       = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $specialty  = mysqli_real_escape_string($conn, $_POST['specialty'] ?? '');
    $experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
    $phone      = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $status     = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');

    if(!$id || !$name){ echo json_encode(['status'=>'error','message'=>'ID and name required']); exit; }

    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM therapists WHERE id='$id'"));
    if($check){ echo json_encode(['status'=>'error','message'=>'ID already exists']); exit; }

    mysqli_query($conn, "INSERT INTO therapists VALUES ('$id','$name','$specialty','$experience','$phone','$status')");
    echo json_encode(['status'=>'success']);

} elseif($action === 'edit'){
    $id         = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
    $name       = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $specialty  = mysqli_real_escape_string($conn, $_POST['specialty'] ?? '');
    $experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
    $phone      = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $status     = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');

    mysqli_query($conn, "UPDATE therapists SET name='$name', specialty='$specialty', experience='$experience', phone='$phone', status='$status' WHERE id='$id'");
    echo json_encode(['status'=>'success']);

} elseif($action === 'delete'){
    $id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
    // Only soft-delete (set inactive) if they have bookings
    $has_bk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE therapist_id='$id' AND status NOT IN ('cancelled','done')"));
    if($has_bk['c'] > 0){
        mysqli_query($conn, "UPDATE therapists SET status='inactive' WHERE id='$id'");
        echo json_encode(['status'=>'success','note'=>'Therapist set to inactive (has active bookings)']);
    } else {
        mysqli_query($conn, "DELETE FROM therapists WHERE id='$id'");
        echo json_encode(['status'=>'success']);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
