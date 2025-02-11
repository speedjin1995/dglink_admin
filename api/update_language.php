<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['uid'], $post['language'])){
    $uid = $post['uid'];
	$lang = $post['language'];

	if ($update_stmt = $db->prepare("UPDATE users SET languages=? WHERE id=?")){
		$update_stmt->bind_param('ss', $lang, $uid);
	
		// Execute the prepared query.
		if (! $update_stmt->execute()){
			echo json_encode(
				array(
					"status"=> "failed", 
					"message"=> $update_stmt->error
				)
			);
		} 
		else{
			$update_stmt->close();
			
			echo json_encode(
				array(
					"status"=> "success", 
					"message"=> "Updated Successfully!!"
				)
			);
		}
	}
	else{
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Failed to prepare statement"
            )
        );     
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