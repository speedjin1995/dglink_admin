<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

$services = 'Change_Password';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

if(isset($post['id'], $post['oldPass'], $post['newPass'], $post['conPass'])){
    $id = $post['id'];
    $oldPassword = $post['oldPass'];
	$newPassword = $post['newPass'];
	$confirmPassword = $post['conPass'];
	
	$stmt = $db->prepare("SELECT * from users where id = ?");
	$stmt->bind_param('s', $id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if(($row = $result->fetch_assoc()) !== null){
		$oldPassword = hash('sha512', $oldPassword . $row['salt']);
		
		if($oldPassword == $row['password']){
			$password = hash('sha512', $newPassword . $row['salt']);
			$stmt2 = $db->prepare("UPDATE users SET password = ? WHERE ID = ?");
			$stmt2->bind_param('ss', $password, $id);
			
			if($stmt2->execute()){
    			$response = json_encode(
        	        array(
        	            "status"=> "success", 
        	            "message"=> "Update successfully"
        	        )
        	    );
				$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
				$stmtU->bind_param('ss', $response, $invid);
				$stmtU->execute();
			
				$db->close();
				$stmt2->close();
				$stmtU->execute();
				echo $response;
    		} 
    		else{
    		    $response = json_encode(
        	        array(
        	            "status"=> "failed", 
        	            "message"=> $stmt2->error
        	        )
        	    );
				$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
				$stmtU->bind_param('ss', $response, $invid);
				$stmtU->execute();
			
				$db->close();
				echo $response;
    		}
		} 
		else{
			$response = json_encode(
    	        array(
    	            "status"=> "failed", 
    	            "message"=> "Old password is not matched"
    	        )
    	    );
			$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
			$stmtU->bind_param('ss', $response, $invid);
			$stmtU->execute();
		
			$db->close();
			echo $response;
		}
	} 
	else{
		$response = json_encode(
	        array(
	            "status"=> "failed", 
	            "message"=> "Data retrieve failed"
	        )
	    );
		$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
		$stmtU->bind_param('ss', $response, $invid);
		$stmtU->execute();
	
		$db->close();
		echo $response;
	}
}
else{
	$response = json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $db->close();
    echo $response;
}
?>