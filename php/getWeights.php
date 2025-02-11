<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM weighing WHERE id=?")) {
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
            $result = $update_stmt->get_result();
            $message = array();
            
            while ($row = $result->fetch_assoc()) {
                $message['id'] = $row['id'];
                $message['status'] = $row['status'];
                $message['po_no'] = $row['po_no'];
                $message['group_no'] = $row['group_no'];
                $message['customer'] = $row['customer'];
                $message['supplier'] = $row['supplier'];
                $message['product'] = $row['product'];
                $message['driver_name'] = $row['driver_name'];
                $message['lorry_no'] = $row['lorry_no'];
                $message['farm_id'] = $row['farm_id'];
                $message['grade'] = $row['grade'];
                $message['gender'] = $row['gender'];
                $message['house_no'] = $row['house_no'];
                $message['average_cage'] = $row['average_cage'];
                $message['average_bird'] = $row['average_bird'];
                $message['booking_date'] = $row['booking_date'];
                $message['minimum_weight'] = $row['minimum_weight'];
                $message['max_crate'] = $row['max_crate'];
                $message['group_no'] = $row['group_no'];

                if($row['weighted_by'] != null){
                    $message['weighted_by'] = json_decode($row['weighted_by'], true);
                }
                else{
                    $message['weighted_by'] = array();
                }

                $message['remark'] = $row['remark'];
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
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