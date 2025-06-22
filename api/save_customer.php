<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

$services = 'Save_Customer';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

if(isset($post['staffName'], $post['customer'], $post['address'], $post['phone'])){
	$staffName = $post['staffName'];
	$customer = $post['customer'];
	$address = $post['address'];
	$phone = $post['phone'];
	
	$regNo = null;
	$address2 = null;
	$address3 = null;
	$address4 = null;
	$email = null;
	
	if(isset($post['regNo']) && $post['regNo'] != null && $post['regNo'] != ''){
	    $regNo = $post['regNo'];
	}
	
	if(isset($post['address2']) && $post['address2'] != null && $post['address2'] != ''){
	    $address2 = $post['address2'];
	}
	
	if(isset($post['address3']) && $post['address3'] != null && $post['address3'] != ''){
	    $address3 = $post['address3'];
	}
	
	if(isset($post['address4']) && $post['address4'] != null && $post['address4'] != ''){
	    $address4 = $post['address4'];
	}
	
	if(isset($post['email']) && $post['email'] != null && $post['email'] != ''){
	    $email = $post['email'];
	}

	if(isset($post['userId']) && $post['userId'] != null && $post['userId'] != ''){
	    if ($update_stmt = $db->prepare("UPDATE customers SET reg_no = ?, customer_name = ?, customer_address = ?, customer_address2 = ?, customer_address3 = ?, customer_address4 = ?, customer_phone = ?, pic = ? WHERE id = ?")) {
            $update_stmt->bind_param('sssssssss', $regNo, $staffName, $address, $address2, $address3, $address4, $phone, $email, $post['userId']);
            
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
	    if ($insert_stmt = $db->prepare("INSERT INTO customers (reg_no, customer_name, customer_address, customer_address2, customer_address3, customer_address4, customer_phone, pic, customer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")){	
    	    $insert_stmt->bind_param('sssssssss', $regNo, $staffName, $address, $address2, $address3, $address4, $phone, $email, $customer);		
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