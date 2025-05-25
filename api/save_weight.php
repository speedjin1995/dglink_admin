<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

$services = 'Save_Weight';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

if(isset($post['status'], $post['product'], $post['timestampData']
, $post['vehicleNumber'], $post['driverName'], $post['farmId']
, $post['averageCage'], $post['averageBird'], $post['capturedData']
, $post['remark'], $post['startTime'], $post['endTime'], $post['cratesCount']
, $post['numberOfCages'], $post['totalCagesWeight'], $post['weightDetails']
, $post['cageDetails'], $post['assignedTo'], $post['company'])){

	$status = $post['status'];
	$product = $post['product'];
	$vehicleNumber = $post['vehicleNumber'];
	$driverName = $post['driverName'];
	$farmId = $post['farmId'];
	$averageCage = $post['averageCage'];
	$averageBird = $post['averageBird'];
	$capturedData = $post['capturedData'];
	$timestampData = $post['timestampData'];
	$weightDetails = $post['weightDetails'];
	$cageDetails = $post['cageDetails'];
	$cratesCount = $post['cratesCount'];
	$numberOfCages = $post['numberOfCages'];
	$totalCagesWeight = $post['totalCagesWeight'];
	$assignedTo = $post['assignedTo'];
	$company = $post['company'];

	$weighted_by = array();
	array_push($weighted_by, $assignedTo);
	$weighted_by = json_encode($weighted_by);
	$max_crates = 0;
	$insert = true;
	
	$currentDateTimeObj = new DateTime();
    $currentDateTime = $currentDateTimeObj->format("Y-m-d H:i:s");

	$remark = $post['remark'];
	$startTime = $post['startTime'];
	$endTime = $post['endTime'];

    $doNo = null;
	$customerName = null;
	$supplierName = null;
	$minWeight = null;
	$maxWeight = null;
	$attandence1 = null;
	$attandence2 = null;
	$serialNo = "";
	$today = date("Y-m-d 00:00:00");
	
	if(isset($post['doNo']) && $post['doNo'] != null && $post['doNo'] != ''){
		$doNo = $post['doNo'];
	}

	if(isset($post['max_crates']) && $post['max_crates'] != null && $post['max_crates'] != ''){
		$max_crates = (int)$post['max_crates'];
	}

	if(isset($post['customerName']) && $post['customerName'] != null && $post['customerName'] != ''){
		$customerName = $post['customerName'];
	}

	if(isset($post['minWeight']) && $post['minWeight'] != null && $post['minWeight'] != ''){
		$minWeight = $post['minWeight'];
	}

	if(isset($post['maxWeight']) && $post['maxWeight'] != null && $post['maxWeight'] != ''){
		$maxWeight = $post['maxWeight'];
	}

	if(isset($post['attandence1']) && $post['attandence1'] != null && $post['attandence1'] != ''){
		$attandence1 = $post['attandence1'];
	}

	if(isset($post['attandence2']) && $post['attandence2'] != null && $post['attandence2'] != ''){
		$attandence2 = $post['attandence2'];
	}

	if(isset($post['serialNo']) && ($post['serialNo'] == null || $post['serialNo'] == '')){
		$serialNo = 'S'.date("Ymd");

		if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM weighing WHERE booking_date >= ? AND company = ? AND deleted='0'")) {
            $select_stmt->bind_param('ss', $today, $company);
            
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Failed to get latest count"
                    )); 
            }
            else{
                $result = $select_stmt->get_result();
                $count = 1;
                
                if ($row = $result->fetch_assoc()) {
                    $count = (int)$row['COUNT(*)'] + 1;
                }

                $charSize = strlen(strval($count));

                for($i=0; $i<(4-(int)$charSize); $i++){
                    $serialNo.='0';  // S0000
                }
        
                $serialNo .= strval($count);  //S00009
                
                // Check serial
                do {
                    // Generate the serial number
                    if ($select_stmt2 = $db->prepare("SELECT COUNT(*) FROM weighing WHERE serial_no = ? and company = ?")) {
                        $select_stmt2->bind_param('ss', $serialNo, $company);
                        
                        // Execute the prepared query to check if the serial number exists
                        if (! $select_stmt2->execute()) {
                            break; // Exit the loop if there's an error
                        }
                        
                        $result = $select_stmt2->get_result();
                        $row = $result->fetch_assoc();
                        $existing_count = (int)$row['COUNT(*)'];
                        
                        if ($existing_count == 0) {
                            // If the serial number does not exist in the table, exit the loop
                            break;
                        }
                        
                        // If the serial number already exists, increment the count and generate a new serial number
                        $count++; // Increment the count
                        $charSize = strlen(strval($count));
                        $serialNo = 'S'.date("Ymd"); // Reset the serial number
                        
                        // Generate the new serial number
                        for($ind = 0; $ind < (4 - (int)$charSize); $ind++) {
                            $serialNo .= '0'; // Append leading zeros
                        }
                        $serialNo .= strval($count); // Append the count
                    }
                } while (true);
			}
		}
		
		$select_stmt->close();
	}
	else if(isset($post['serialNo']) && $post['serialNo'] != null && $post['serialNo'] != ''){
	    $serialNo = $post['serialNo'];
	}

    if ($select_stmt2 = $db->prepare("SELECT COUNT(*) FROM weighing WHERE start_time = ? AND weighted_by = ? AND farm_id = ?")) {
	    $select_stmt2->bind_param('sss', $startTime, $weighted_by, $farmId);
        // Execute the prepared query.
        if (! $select_stmt2->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => $select_stmt2->error
                )); 
        }
        else{
            $result = $select_stmt2->get_result();
            $count = 1;
            
            if ($row = $result->fetch_assoc()) {
                if((int)$row['COUNT(*)'] > 0){
                    $insert = false;
                }
            }
		}
	}
	
	$select_stmt2->close();
	
	if((isset($post['id']) && $post['id'] != null && $post['id'] != '')){
		$id = $post['id'];
		$data = json_encode($weightDetails);
		$data2 = json_encode($timestampData);
		$data3 = json_encode($cageDetails);

		if ($update_stmt = $db->prepare("UPDATE weighing SET customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, average_cage=?, average_bird=?, 
		minimum_weight=?, maximum_weight=?, weight_data=?, remark=?, start_time=?, weight_time=?, end_time=?, total_cage=?, number_of_cages=?, total_cages_weight=?, 
		follower1=?, follower2=?, status=?, po_no=?, cage_data=?, company=?, weighted_by=? WHERE id=?")){
			$update_stmt->bind_param('ssssssssssssssssssssssssss', $customerName, $supplierName, $product, $driverName, 
			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, 
			$data2, $endTime, $cratesCount, $numberOfCages, $totalCagesWeight, $attandence1, $attandence2, $status, $doNo, $data3, $company, $weighted_by, $id);
		
			// Execute the prepared query.
			if (! $update_stmt->execute()){
			    $response = json_encode(
                    array(
                        "status"=> "failed", 
						"message"=> $update_stmt->error
                    )
                );
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $update_stmt->close();
                $stmtU->close();
                $db->close();
                echo $response;
			} 
			else{
			    $response = json_encode(
                    array(
						"status"=> "success", 
						"message"=> "Updated Successfully!!",
						"serialNo" => $post['serialNo'],
						"weightId" => $id
					)
                );
                $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $response, $invid);
                $stmtU->execute();
        
                $update_stmt->close();
                $stmtU->close();
                $db->close();
                echo $response;
			}
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
    
            $stmtU->close();
            $db->close();
            echo $response;
		}
	}
	else{
		$data = json_encode($weightDetails);
		$data2 = json_encode($timestampData);
		$data3 = json_encode($cageDetails);
		$now = date("Y-m-d H:i:s");
		$id = '0';

        if($insert){
            if ($insert_stmt = $db->prepare("INSERT INTO weighing (serial_no, customer, supplier, product, driver_name, lorry_no, farm_id, 
    		average_cage, average_bird, minimum_weight, maximum_weight, weight_data, remark, start_time, weight_time, end_time,total_cage, 
    		number_of_cages, total_cages_weight, follower1, follower2, status, po_no, cage_data, booking_date, company, weighted_by, created_datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")){		    
    			$insert_stmt->bind_param('ssssssssssssssssssssssssssss', $serialNo, $customerName, $supplierName, $product, $driverName, 
    			$vehicleNumber, $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime, $data2, $endTime,
    			$cratesCount, $numberOfCages, $totalCagesWeight, $attandence1, $attandence2, $status, $doNo, $data3, $startTime, $company, $weighted_by, $currentDateTime);		
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
            
                    $insert_stmt->close();
                    $stmtU->close();
                    $db->close();
                    echo $response;
    			} 
    			else{
    				$id = $insert_stmt->insert_id;
    				
    				$response = json_encode(
                        array(
    						"status"=> "success", 
    						"message"=> "Added Successfully!!",
    						"serialNo"=> $serialNo,
    						"weightId" => $id
    					)
                    );
                    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
                    $stmtU->bind_param('ss', $response, $invid);
                    $stmtU->execute();
            
                    $insert_stmt->close();
                    $stmtU->close();
                    $db->close();
                    echo $response;
    			}
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
        
                $stmtU->close();
                $db->close();
                echo $response; 
    		}
        }
        else{
            if ($select_stmt = $db->prepare("SELECT * FROM weighing WHERE start_time = ? AND weighted_by = ? AND farm_id = ?")) {
        	    $select_stmt->bind_param('sss', $startTime, $weighted_by, $farmId);
                // Execute the prepared query.
                $id = 0;
                if (! $select_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => $select_stmt->error
                        )
                    ); 
                }
                else{
                    $result = $select_stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        $serialNo = $row['serial_no'];
                        $id = $row['id'];
                        
                        echo json_encode(
            				array(
            					"status"=> "success", 
            					"message"=> "Updated Successfully!!",
            					"serialNo"=> $serialNo,
            					"id" => $id
            				)
            			);
                    }
                    else{
                        echo json_encode(
                            array(
                                "status" => "failed",
                                "message" => "Failed to get result"
                            )
                        ); 
                    }
        		}
        	}
        }
	}
} 
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );     
}
?>