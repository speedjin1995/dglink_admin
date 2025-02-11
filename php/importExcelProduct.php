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
    $product = null;
	$remark = null;

    if(isset($data['code']) && $data['code'] != null && $data['code'] != ''){
        $code = $data['code'];
    }
    if(isset($data['product']) && $data['product'] != null && $data['product'] != ''){
        $product = $data['product'];
    }
    if(isset($data['remark']) && $data['remark'] != null && $data['remark'] != ''){
        $remark = $data['remark'];
    }

    if ($insert_stmt = $db->prepare("INSERT INTO products (product_code, product_name, remark) VALUES (?, ?, ?)")) {
        $insert_stmt->bind_param('sss', $code, $product, $remark);
        
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