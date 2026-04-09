<?php
include '../config/database.php';

$user_id  = intval($_GET['user_id'] ?? 0);
$role     = mysqli_real_escape_string($conn, $_GET['role'] ?? '');

// Auto-cancel awaiting_payment bookings past deadline
mysqli_query($conn,
    "UPDATE bookings SET status='cancelled'
     WHERE status='awaiting_payment' AND payment_deadline < NOW()"
);

// Include uploaded_at and amount from payments in the join
if($role === 'admin'){
    $sql = "SELECT b.*,
                   p.status       AS payment_status,
                   p.proof_filename,
                   p.uploaded_at  AS proof_uploaded_at,
                   p.amount       AS payment_amount,
                   p.id           AS payment_id
            FROM bookings b
            LEFT JOIN payments p ON b.id = p.booking_id
            ORDER BY b.created_at DESC";
} else {
    $sql = "SELECT b.*,
                   p.status       AS payment_status,
                   p.proof_filename,
                   p.uploaded_at  AS proof_uploaded_at,
                   p.amount       AS payment_amount,
                   p.id           AS payment_id
            FROM bookings b
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.user_id = $user_id
            ORDER BY b.created_at DESC";
}

$result = mysqli_query($conn, $sql);
$data = [];
while($row = mysqli_fetch_assoc($result)){
    // Normalize nulls for JS
    $row['payment_status']    = $row['payment_status']    ?? 'awaiting';
    $row['proof_filename']    = $row['proof_filename']    ?? '';
    $row['proof_uploaded_at'] = $row['proof_uploaded_at'] ?? '';
    $row['payment_amount']    = $row['payment_amount']    ?? 0;
    $data[] = $row;
}
echo json_encode($data);
