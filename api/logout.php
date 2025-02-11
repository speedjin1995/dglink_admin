<?php
require_once 'db_connect.php';

session_start();
session_destroy();

$services = 'Logout';
$response = json_encode(
    array(
        "status"=> "success", 
        "message"=> "Logged Out"
    )
);

$stmtL = $db->prepare("INSERT INTO api_requests (services, response) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $response);
$stmtL->execute();

echo $response;
?>