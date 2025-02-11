<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$services = 'Activation';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

if(isset($post['userEmail'], $post['userPassword'], $post['userKey'])){
    $username=$post['userEmail'];
    $password=$post['userPassword'];
    $key=$post['userKey'];
    $now = date("Y-m-d H:i:s");
    
    $userexist = false;
    $keyexist = false;
    $macexist = false;
    $activated = '1';
    $uid = '1';
    
    // Check user
    $stmt = $db->prepare("SELECT * from users where username= ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(($row = $result->fetch_assoc()) !== null){
    	$userexist = true;
    	$uid = $row['id'];
    }
    
    // Check key
    $stmt2 = $db2->prepare("SELECT * from license_key where license= ?");
    $stmt2->bind_param('s', $key);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if(($row2 = $result2->fetch_assoc()) !== null){
    	$keyexist = true;
    }
    
    // Check mac
    /*$stmt3 = $db->prepare("SELECT * from indicators where mac_address= ?");
    $stmt3->bind_param('s', $mac_address);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    
    if(($row3 = $result3->fetch_assoc()) !== null){
    	$macexist = true;
    }*/
    
    //if($userexist && $keyexist && $macexist){
    if($userexist && $keyexist){
        if ($update_stmt = $db2->prepare("UPDATE license_key SET activated = ? WHERE license = ?")) {
            $update_stmt->bind_param('ss', $activated, $key);
            $update_stmt->execute();
		}
		
		/*if ($update_stmt2 = $db->prepare("UPDATE indicators SET users = ? WHERE mac_address = ? OR udid = ?")) {
            $update_stmt2->bind_param('sss', $uid, $mac_address, $mac_address);
            $update_stmt2->execute();
		}*/
		
		if ($update_stmt3 = $db->prepare("UPDATE users SET license_key = ?, activation_date = ? WHERE id = ?")) {
            $update_stmt3->bind_param('sss', $key, $now, $uid);
            $update_stmt3->execute();
		}

        $response = json_encode(
            array(
                "status"=> "success", 
                "message"=> "Activated",
                "uid" => $uid ?? ''
            )
        );
        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();

        //$stmt->close();
        $stmtU->close();
		$db->close();
        $db2->close();
        echo $response;
    }
    else{
        $response = json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Failed to activated"
            )
        );
        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();

        //$stmt->close();
        $stmtU->close();
		$db->close();
        $db2->close();
        echo $response;
    }
}
else{
    $response = json_encode(
        array(
            "status"=> "failed", 
            "message"=> "fill in all the fields"
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    //$stmt->close();
    $stmtU->close();
    $db->close();
    $db2->close();
    echo $response;
}

?>
