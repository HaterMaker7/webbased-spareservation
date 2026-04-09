<?php
include '../config/database.php';

$action = $_POST['action'] ?? '';

if($action === 'list'){
    $result = mysqli_query($conn, "SELECT id, username, full_name, phone, email, role, created_at FROM users WHERE role='customer' ORDER BY full_name");
    $data = [];
    while($row = mysqli_fetch_assoc($result)) $data[] = $row;
    echo json_encode($data);

} elseif($action === 'add'){
    $username  = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password  = md5($_POST['password'] ?? '');
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $phone     = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email     = mysqli_real_escape_string($conn, $_POST['email'] ?? '');

    if(!$username || !$_POST['password'] || !$full_name){
        echo json_encode(['status'=>'error','message'=>'Username, password, and full name are required.']); exit;
    }

    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username='$username'"));
    if($check){ echo json_encode(['status'=>'error','message'=>'Username already exists.']); exit; }

    mysqli_query($conn, "INSERT INTO users (username,password,role,full_name,phone,email) VALUES ('$username','$password','customer','$full_name','$phone','$email')");
    $new_id = mysqli_insert_id($conn);
    echo json_encode(['status'=>'success','id'=>$new_id]);

} elseif($action === 'edit'){
    $id        = intval($_POST['id'] ?? 0);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $phone     = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email     = mysqli_real_escape_string($conn, $_POST['email'] ?? '');

    $pw_clause = '';
    if(!empty($_POST['password'])){
        $pw = md5($_POST['password']);
        $pw_clause = ", password='$pw'";
    }

    mysqli_query($conn, "UPDATE users SET full_name='$full_name', phone='$phone', email='$email' $pw_clause WHERE id=$id AND role='customer'");
    echo json_encode(['status'=>'success']);

} elseif($action === 'delete'){
    $id = intval($_POST['id'] ?? 0);
    // Don't delete if they have active bookings
    $has = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE user_id=$id AND status NOT IN ('cancelled','done')"));
    if($has['c'] > 0){
        echo json_encode(['status'=>'error','message'=>'Cannot delete customer with active bookings.']); exit;
    }
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='customer'");
    echo json_encode(['status'=>'success']);

} else {
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
