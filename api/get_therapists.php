<?php
include '../config/database.php';
$result = mysqli_query($conn, "SELECT * FROM therapists ORDER BY name");
$data = [];
while($row = mysqli_fetch_assoc($result)) $data[] = $row;
echo json_encode($data);
