<?php
$host = 'localhost';
$dbname = 'spa_reservation';
$user = '';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if(!$conn){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database connection failed']);
    exit;
}
mysqli_set_charset($conn, 'utf8mb4');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
