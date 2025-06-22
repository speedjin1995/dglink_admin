<?php
require_once 'db_connect.php';

$post = json_decode(file_get_contents('php://input'), true);
$services = 'Delete_Farms';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$ids = json_decode($post['id'], true);
$deleted = '1';
$success = true;

for($i=0; $i<count($ids); $i++){
    $id = $ids[$i];
    
    $stmt = $db->prepare("UPDATE farms SET deleted =? WHERE id =?");
    $stmt->bind_param('ss', $deleted, $id);
    
    
    if(!$stmt->execute()){
        $success = false;
    }
}

$stmt->close();

if($success){
    $response = json_encode(
        array(
            "status"=> "success", 
            "message"=> "deleted"
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $stmtU->close();
    $db->close();
    echo $response;
}
else{
    $response = json_encode(
        array(
            "status"=> 'failed', 
            "message"=> 'Failed to delete'
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $stmtU->close();
    $db->close();
    echo $response; 
}
?>