<?php
include '../config/database.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name        = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $category    = mysqli_real_escape_string($conn, $_POST['category'] ?? 'massage');
    $duration    = intval($_POST['duration'] ?? 60);
    $price       = intval($_POST['price'] ?? 0);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');

    if (!$name) { echo json_encode(['status'=>'error','message'=>'Name required']); exit; }

    // Auto-generate ID
    $res  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM services"));
    $num  = $res['c'] + 1;
    $id   = 'S' . str_pad($num, 3, '0', STR_PAD_LEFT);
    // Make sure ID is unique
    while (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM services WHERE id='$id'")) > 0) {
        $num++; $id = 'S' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    mysqli_query($conn,
        "INSERT INTO services (id,name,category,duration,price,description,is_active)
         VALUES ('$id','$name','$category',$duration,$price,'$description',1)"
    );
    echo json_encode(['status'=>'success','id'=>$id]);

} elseif ($action === 'edit') {
    $id          = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
    $name        = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $duration    = intval($_POST['duration'] ?? 60);
    $price       = intval($_POST['price'] ?? 0);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');

    if (!$id || !$name) { echo json_encode(['status'=>'error','message'=>'ID and name required']); exit; }

    mysqli_query($conn,
        "UPDATE services SET name='$name', duration=$duration, price=$price, description='$description'
         WHERE id='$id'"
    );
    echo json_encode(['status'=>'success']);

} elseif ($action === 'delete') {
    $id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID required']); exit; }
    mysqli_query($conn, "DELETE FROM services WHERE id='$id'");
    echo json_encode(['status'=>'success']);

} else {
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
?>
