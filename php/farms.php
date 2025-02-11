<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}
else{
    $userId = $_SESSION['userID'];
    $company = $_SESSION['customer'];
}

if(isset($_POST['code'], $_POST['packages'], $_POST['address'])){
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $packages = filter_input(INPUT_POST, 'packages', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $address2 = null;
    $address3 = null;
    $address4 = null;
    $states = filter_input(INPUT_POST, 'states', FILTER_SANITIZE_STRING);
    $supplier = null;

    if($_POST['address2'] != null && $_POST['address2'] != ''){
        $address2 = filter_input(INPUT_POST, 'address2', FILTER_SANITIZE_STRING);
    }

    if($_POST['address3'] != null && $_POST['address3'] != ''){
        $address3 = filter_input(INPUT_POST, 'address3', FILTER_SANITIZE_STRING);
    }

    if($_POST['address4'] != null && $_POST['address4'] != ''){
        $address4 = filter_input(INPUT_POST, 'address4', FILTER_SANITIZE_STRING);
    }

    if(isset($_POST['supplier']) && $_POST['supplier'] != null && $_POST['supplier'] != ''){
        $supplier = filter_input(INPUT_POST, 'supplier', FILTER_SANITIZE_STRING);
    }

    if($_POST['id'] != null && $_POST['id'] != ''){
        if ($update_stmt = $db->prepare("UPDATE farms SET farms_code=?, name=?, address=?, address2=?, address3=?, address4=?, states=?, suppliers=? WHERE id=?")) {
            $update_stmt->bind_param('sssssssss', $code, $packages, $address, $address2, $address3, $address4, $states, $supplier, $_POST['id']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $update_stmt->error
                    )
                );
            }
            else{
                $update_stmt->close();
                $db->close();
                
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Updated Successfully!!" 
                    )
                );
            }
        }
    }
    else{
        if ($insert_stmt = $db->prepare("INSERT INTO farms (farms_code, name, address, address2, address3, address4, states, suppliers, customer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('sssssssss', $code, $packages, $address, $address2, $address3, $address4, $states, $supplier, $company);
            
            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $insert_stmt->error
                    )
                );
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