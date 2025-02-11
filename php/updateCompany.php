<?php
require_once 'db_connect.php';
session_start();

if(isset($_POST['name'], $_POST['address'])){
	$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
	$reg_no = null;
	$address2 = null;
	$address3 = null;
	$address4 = null;
	$phone = null;
	$email = null;
	$id = $_SESSION['customer'];

	if(isset($_POST['reg_no']) && $_POST['reg_no'] != null && $_POST['reg_no'] != ""){
		$reg_no = filter_input(INPUT_POST, 'reg_no', FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['address2']) && $_POST['address2'] != null && $_POST['address2'] != ""){
		$address2 = filter_input(INPUT_POST, 'address2', FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['address3']) && $_POST['address3'] != null && $_POST['address3'] != ""){
		$address3 = filter_input(INPUT_POST, 'address3', FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['address4']) && $_POST['address4'] != null && $_POST['address4'] != ""){
		$address4 = filter_input(INPUT_POST, 'address4', FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['phone']) && $_POST['phone'] != null && $_POST['phone'] != ""){
		$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
	}
	
	if(isset($_POST['email']) && $_POST['email'] != null && $_POST['email'] != ""){
		$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
	}

	if ($stmt2 = $db->prepare("UPDATE companies SET name=?, reg_no=?, address=?, address2=?, address3=?, address4=?, phone=?, email=? WHERE id=?")) {
		$stmt2->bind_param('sssssssss', $name, $reg_no, $address, $address2, $address3, $address4, $phone, $email, $id);
		
		if($stmt2->execute()){
			$stmt2->close();
			$db->close();
			
			echo json_encode(
				array(
					"status"=> "success", 
					"message"=> "Your company profile is updated successfully!" 
				)
			);
		} else{
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> $stmt->error
				)
			);
		}
	} 
	else{
		echo json_encode(
			array(
				"status"=> "failed", 
				"message"=> "Something went wrong!"
			)
		);
	}
} 
else{
	echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all fields"
        )
    ); 
}
?>
