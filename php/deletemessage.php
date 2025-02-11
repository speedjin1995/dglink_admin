<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['messageId'])){
	$id = filter_input(INPUT_POST, 'messageId', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("DELETE FROM message_resource WHERE id=?")) {
        $update_stmt->bind_param('s', $id);
        
        // Execute the prepared query.
        if (! $update_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                    )); 
        }
        else{
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => "delete successfull"
                    ));   
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
            )); 
}
?>