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

    $code =  null;
    $packages = null;
	$address = null;
    $address2 = null;
    $address3 = null;
    $address4 = null;
    $states = null;
    $supplier = null;

    if(isset($data['code']) && $data['code'] != null && $data['code'] != ''){
        $code = $data['code'];
    }
    if(isset($data['packages']) && $data['packages'] != null && $data['packages'] != ''){
        $packages = $data['packages'];
    }
    if(isset($data['address']) && $data['address'] != null && $data['address'] != ''){
        $address = $data['address'];
    }
    if(isset($data['address2']) && $data['reg_no'] != null && $data['address2'] != ''){
        $address2 = $data['address2'];
    }
    if(isset($data['address3']) && $data['address3'] != null && $data['address3'] != ''){
        $address3 = $data['reg_no'];
    }
    if(isset($data['address4']) && $data['address4'] != null && $data['address4'] != ''){
        $address4 = $data['address4'];
    }
    if(isset($data['states']) && $data['states'] != null && $data['states'] != ''){
        $states = $data['states'];
    }
    if(isset($data['supplier']) && $data['supplier'] != null && $data['supplier'] != ''){
        $supplier = $data['supplier'];
    }

    if ($insert_stmt = $db->prepare("INSERT INTO farms (farms_code, name, address, address2, address3, address4, states, suppliers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
        $insert_stmt->bind_param('ssssssss', $code, $packages, $address, $address2, $address3, $address4, $states, $supplier);
        
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