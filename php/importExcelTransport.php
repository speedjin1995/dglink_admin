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
    $transporter = null;

    if(isset($data['code']) && $data['code'] != null && $data['code'] != ''){
        $code = $data['code'];
    }
    if(isset($data['transporter']) && $data['transporter'] != null && $data['transporter'] != ''){
        $transporter = $data['transporter'];
    }

    if ($insert_stmt = $db->prepare("INSERT INTO transporters (transporter_code, transporter_name) VALUES (?, ?)")) {
        $insert_stmt->bind_param('ss', $code, $transporter);
        
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