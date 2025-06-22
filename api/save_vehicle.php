<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['staffName'], $post['customer'])){
	$staffName = $post['staffName'];
	$customer = $post['customer'];

	if(isset($post['userId']) && $post['userId'] != null && $post['userId'] != ''){
	    if ($update_stmt = $db->prepare("UPDATE vehicles SET veh_number = ? WHERE id = ?")) {
            $update_stmt->bind_param('ss', $staffName, $post['userId']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                $response = json_encode(
    				array(
    					"status" => "failed",
                        "message" => $update_stmt->error
    				)
    			);
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $stmtU->close();
                echo $response;
            }
            else{
                $response = json_encode(
    				array(
    					"status"=> "success", 
    					"message"=> "Updated Successfully!!",
    					"id" => $post['userId']
    				)
    			);
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $stmtU->close();
                echo $response;
			}
		}
	}
	else{
	    if ($insert_stmt = $db->prepare("INSERT INTO vehicles (veh_number, customer) VALUES (?, ?)")){	
    	    $insert_stmt->bind_param('ss', $staffName, $customer);		
    		// Execute the prepared query.
    		if (! $insert_stmt->execute()){
    		    $response = json_encode(
    				array(
    					"status"=> "failed", 
    					"message"=> $insert_stmt->error
    				)
    			);
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $stmtU->close();
                echo $response;
    		} 
    		else{
    			$id = $insert_stmt->insert_id;
				$insert_stmt->close();
				$response = json_encode(
    				array(
    					"status"=> "success", 
    					"message"=> "Added Successfully!!",
    					"id"=> $id
    				)
    			);
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $stmtU->close();
                echo $response;
    		}
    
    		$db->close();
    	}
    	else{
			$response = json_encode(
    			array(
    				"status"=> "failed", 
    				"message"=> "cannot prepare statement"
    			)
    		);
			$stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
			$stmtU->bind_param('ss', $response, $invid);
			$stmtU->execute();
		
			$db->close();
			echo $response;
    	}
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