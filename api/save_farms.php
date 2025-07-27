<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

$services = 'Save_Farms';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

if(isset($post['staffName'], $post['customer'])){
	$staffName = $post['staffName'];
	$customer = $post['customer'];

	if(isset($post['userId']) && $post['userId'] != null && $post['userId'] != ''){
	    if ($update_stmt = $db->prepare("UPDATE farms SET name = ? WHERE id = ?")) {
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
	    // Step 1: Get farms_no limit from companies table
        $limit_stmt = $db->prepare("SELECT farms_no FROM companies WHERE id = ?");
        $limit_stmt->bind_param('s', $customer);
        $limit_stmt->execute();
        $limit_stmt->bind_result($farm_limit);
        
        if ($limit_stmt->fetch()) {
            $limit_stmt->close();
    
            // Step 2: Count current farms for the customer
            $count_stmt = $db->prepare("SELECT COUNT(*) FROM farms WHERE customer = ? and deleted = '0'");
            $count_stmt->bind_param('s', $customer);
            $count_stmt->execute();
            $count_stmt->bind_result($current_count);
            $count_stmt->fetch();
            $count_stmt->close();
    
            // Step 3: Compare with farm limit
            if ($current_count < $farm_limit) {
                // Proceed to insert new farm
                if ($insert_stmt = $db->prepare("INSERT INTO farms (name, customer) VALUES (?, ?)")) {
                    $insert_stmt->bind_param('ss', $staffName, $customer);
                    if (! $insert_stmt->execute()) {
                        $response = json_encode(
                            array("status" => "failed", "message" => $insert_stmt->error)
                        );
                    } else {
                        $id = $insert_stmt->insert_id;
                        $insert_stmt->close();
                        $response = json_encode(
                            array("status" => "success", "message" => "Added Successfully!!", "id" => $id)
                        );
                        
                        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                    	$stmtU->bind_param('ss', $response, $invid);
                    	$stmtU->execute();
                    
                    	$db->close();
                    	echo $response; 
                    }
                } else {
                    $response = json_encode(
                        array("status" => "failed", "message" => "cannot prepare insert statement")
                    );
                    
                    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                	$stmtU->bind_param('ss', $response, $invid);
                	$stmtU->execute();
                
                	$db->close();
                	echo $response; 
                }
            } else {
                // Exceeded farm limit
                $response = json_encode(
                    array("status" => "failed", "message" => "Already reached farm limit")
                );
                
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
            	$stmtU->bind_param('ss', $response, $invid);
            	$stmtU->execute();
            
            	$db->close();
            	echo $response; 
            }
        } else {
            // Customer not found in companies table
            $response = json_encode(
                array("status" => "failed", "message" => "Customer not found")
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