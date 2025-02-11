<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['keyCode'], $_POST['englishDecs'], $_POST['chineseDecs'], $_POST['malayDecs'], $_POST['nepaliDecs'])){
	$keyCode = filter_input(INPUT_POST, 'keyCode', FILTER_SANITIZE_STRING);
	$englishDecs = filter_input(INPUT_POST, 'englishDecs', FILTER_SANITIZE_STRING);
	$chineseDecs = filter_input(INPUT_POST, 'chineseDecs', FILTER_SANITIZE_STRING);
    $malayDecs = filter_input(INPUT_POST, 'malayDecs', FILTER_SANITIZE_STRING);
	$nepaliDecs = filter_input(INPUT_POST, 'nepaliDecs', FILTER_SANITIZE_STRING);

    if($_POST['keyId'] != null && $_POST['keyId'] != ''){
        if ($update_stmt = $db->prepare("UPDATE message_resource SET message_key_code=?, en=?, zh=?, my=?, ne=? WHERE id=?")) {
            $update_stmt->bind_param('ssssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs, $_POST['keyId']);
            
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
        if ($insert_stmt = $db->prepare("INSERT INTO message_resource (message_key_code, en, zh, my, ne) VALUES (?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('sssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs);
            
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
    echo '<script type="text/javascript">alert("Missing Attributes");';
    echo 'window.location.href = "../message.php";</script>';
}
?>