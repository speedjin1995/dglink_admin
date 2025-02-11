<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$now = date("Y-m-d H:i:s");

$post = json_decode(file_get_contents('php://input'), true);

$services = 'Load_Weights';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$staffId = $post['company'];

$stmt = $db->prepare("SELECT * from weighing WHERE deleted = '0' AND status='Complete' AND company = '".$staffId."' ORDER BY `booking_date` DESC");
$stmt->execute();
$result = $stmt->get_result();
$message = array();

while($row = $result->fetch_assoc()){
    $farmId=$row['farm_id'];
    $farmName='';
    
    if ($update_stmt = $db->prepare("SELECT * FROM farms WHERE id=?")) {
        $update_stmt->bind_param('s', $farmId);
        
        if ($update_stmt->execute()) {
            $result3 = $update_stmt->get_result();
            
            if ($row3 = $result3->fetch_assoc()) {
                $farmName=$row3['name'];
            }
        }
    }
    
    $update_stmt->close();
    
	$message[] = array( 
        'id'=>$row['id'],
        'serial_no'=>$row['serial_no'],
        "booking_date"=>$row['booking_date'],
        'po_no'=>$row['po_no'],
        'group_no'=>$row['group_no'],
        'customer'=>$row['customer'],
        'supplier'=>$row['supplier'],
        'product'=>$row['product'],
        'driver_name'=>$row['driver_name'],
        'lorry_no'=>$row['lorry_no'],
        'farm_id'=>$row['farm_id'],
        'farm_name'=>$farmName,
        'average_cage'=>$row['average_cage'],
        'average_bird'=>$row['average_bird'],
        'minimum_weight'=>$row['minimum_weight'],
        'maximum_weight'=>$row['maximum_weight'],
        'total_cages_weight'=>$row['total_cages_weight'],
        'number_of_cages'=>$row['number_of_cages'],
        'total_cage'=>$row['total_cage'],
        'max_crate'=>$row['max_crate'],
        'weight_data'=>$row['weight_data'],
        'cage_data'=>$row['cage_data'],
        'created_datetime'=>$row['created_datetime'],
        'max_crate'=>$row['max_crate'],
        'start_time'=>$row['start_time'],
        'end_time'=>$row['end_time'],
        'grade'=>$row['grade'],
        'gender'=>$row['gender'],
        'house_no'=>$row['house_no'],
        'remark'=>$row['remark']
    );
}

$stmt->close();
$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "message"=> $message
    )
);
?>
