<?php
require_once 'db_connect.php';

$staff = $db->query("SELECT * FROM staff WHERE deleted = '0'");

$data6 = array();

while($row6=mysqli_fetch_assoc($staff)){
    $data6[] = array( 
        'id'=>$row6['id'],
        'staff'=>$row6['staff_name']
    );
}

$db->close();

echo json_encode(
    array(
        "status"=> "success", 
        "message"=> $data6
    )
);
?>