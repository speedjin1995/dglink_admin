<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['staffName'])){
	$staffName = $post['staffName'];

	if(isset($post['userId']) && $post['userId'] != null && $post['userId'] != ''){
	    if ($update_stmt = $db->prepare("UPDATE staff SET staff_name = ? WHERE id = ?")) {
            $update_stmt->bind_param('ss', $staffName, $post['userId']);
            
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Failed to get latest count"
                    )); 
            }
            else{
                echo json_encode(
    				array(
    					"status"=> "success", 
    					"message"=> "Updated Successfully!!",
    					"id" => $post['userId']
    				)
    			);
			}
		}
	}
	else{
	    if ($insert_stmt = $db->prepare("INSERT INTO staff (staff_name) VALUES (?)")){	
    	    $insert_stmt->bind_param('s', $staffName);		
    		// Execute the prepared query.
    		if (! $insert_stmt->execute()){
    			echo json_encode(
    				array(
    					"status"=> "failed", 
    					"message"=> $insert_stmt->error
    				)
    			);
    		} 
    		else{
    			$id = $insert_stmt->insert_id;
				$insert_stmt->close();
    			
    			echo json_encode(
    				array(
    					"status"=> "success", 
    					"message"=> "Added Successfully!!",
    					"id"=> $id
    				)
    			);
    		}
    
    		$db->close();
    	}
    	else{
    		echo json_encode(
    			array(
    				"status"=> "failed", 
    				"message"=> "cannot prepare statement"
    			)
    		);  
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