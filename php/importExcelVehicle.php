<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

$input = file_get_contents('php://input');

// Decode the JSON data received
$data = json_decode($input, true); // 'true' to decode as associative arrays

if ($data !== null) {

    $vehicleNumber =  null;
    $driverName = null;
    $attendance1 = null;
	$attendance2 = null;

    if(isset($data['veh_number']) && $data['veh_number'] != null && $data['veh_number'] != ''){
        $vehicleNumber = $data['veh_number'];
    }
    if(isset($data['driver']) && $data['driver'] != null && $data['driver'] != ''){
        $driverName = $data['driver'];
    }
    if(isset($data['attandence_1']) && $data['attandence_1'] != null && $data['attandence_1'] != ''){
        $attendance1 = $data['attandence_1'];
    }
    if(isset($data['attandence_2']) && $data['attandence_2'] != null && $data['attandence_2'] != ''){
        $attendance2 = $data['attandence_2'];
    }

    $driver = null;

    $empQuery = "SELECT * FROM transporters WHERE deleted = '0' and transporter_name like '%".$driverName."%'";
    $empRecords = mysqli_query($db, $empQuery);
    while($row = mysqli_fetch_assoc($empRecords)) {
        $driver = $row['id'];
    }

    if ($insert_stmt = $db->prepare("INSERT INTO vehicles (veh_number, driver, attandence_1, attandence_2) VALUES (?, ?, ?, ?)")) {
        $insert_stmt->bind_param('ssss', $vehicleNumber, $driver, $attendance1, $attendance2);
        
        // Execute the prepared query.
        if (! $insert_stmt->execute()) {
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> $insert_stmt->error
                )
            );

            $insert_stmt->close();
            $db->close();
        }
        else{
            $insert_stmt->close();
            $db->close();
            
            echo json_encode(
                array(
                    "status"=> "success", 
                    "message"=> "Added Successfully!!" 
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