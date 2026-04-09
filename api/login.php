<?php
include '../config/database.php';

$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$password = md5($_POST['password'] ?? '');

$query = mysqli_query($conn,
    "SELECT id, username, role, full_name, phone, email FROM users
     WHERE username='$username' AND password='$password' AND role IN ('admin','customer')"
);

if(mysqli_num_rows($query) > 0){
    $user = mysqli_fetch_assoc($query);
    echo json_encode(['status'=>'success', 'user'=>$user]);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Invalid username or password.']);
}
