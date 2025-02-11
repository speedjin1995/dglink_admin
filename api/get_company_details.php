<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);

if(isset($post['uid'])){
    $uid = $post['uid'];
    $language = 'en';
    
    if(isset($post['languages']) && $post['languages'] != null && $post['languages'] != ''){
		$language = $post['languages'];
	}
    
    $stmt = $db->prepare("SELECT * from users WHERE id = ?");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = array();
    
    if($row = $result->fetch_assoc()){
        if($row['languages'] == $language){
            $message[] = array( 
                'id'=>$row['id'],
                'languages'=>$row['languages']
            );
        }
        else{
            $message[] = array( 
                'id'=>$row['id'],
                'languages'=>$language
            );
            
            if ($update_stmt = $db->prepare("UPDATE users SET languages=? WHERE id=?")){
        		$update_stmt->bind_param('ss', $language, $uid);
        	    $update_stmt->execute();
        		$update_stmt->close();
        	}
        }
    }
    
    $stmt->close();
    $db->close();
    
    echo json_encode(
        array(
            "status"=> "success", 
            "message"=> $message
        )
    );
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Missing User Id"
        )
    ); 
}
?>
