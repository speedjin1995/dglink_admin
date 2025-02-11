<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}
else{
    $userId = $_SESSION['userID'];
}

$input = file_get_contents('php://input');

// Decode the JSON data received
$data = json_decode($input, true); // 'true' to decode as associative arrays

if ($data !== null) {

    $username =  null;
    $name = null;
    $roleCode = null;
	$farmsCode = null;

    if(isset($data['username']) && $data['username'] != null && $data['username'] != ''){
        $username = $data['username'];
    }
    if(isset($data['name']) && $data['name'] != null && $data['name'] != ''){
        $name = $data['name'];
    }
    if(isset($data['role_code']) && $data['role_code'] != null && $data['role_code'] != ''){
        $roleCode = $data['role_code'];
    }
    if(isset($data['farms']) && $data['farms'] != null && $data['farms'] != ''){
        $farmsCode = $data['farms'];
    }

    $farms = [];
    $array = json_decode($farmsCode, true);

    if ($array !== null) {
        foreach ($array as $value) {
            $empQuery = "SELECT * FROM farms WHERE deleted = '0' and farms_code like '%".$value."%'";
            $empRecords = mysqli_query($db, $empQuery);
            
            while($row = mysqli_fetch_assoc($empRecords)) {
                array_push($farms, $row['id']);
            }
        }
    } else {
        echo "Invalid JSON data.";
    }

    $farms = json_encode($farms);
    $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));
    $password = '123456';
    $password = hash('sha512', $password . $random_salt);

    if ($insert_stmt = $db->prepare("INSERT INTO users (username, name, password, salt, created_by, role_code, farms) VALUES (?, ?, ?, ?, ?, ?, ?)")) {
        $insert_stmt->bind_param('sssssss', $username, $name, $password, $random_salt, $userId, $roleCode, $farms);
        
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