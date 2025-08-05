<?php
require_once 'db_connect.php';

$post = json_decode(file_get_contents('php://input'), true);
$services = 'Logout';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$id = $post['uid'];

if (isset($id) && !empty($id)) {
    // Prepare and execute the SQL statement to set device_id to NULL
    $stmtU = $db->prepare("UPDATE users SET device_id = NULL WHERE id = ?");
    $stmtU->bind_param('s', $id); // Assuming 'id' is a string or compatible type
    
    if ($stmtU->execute()) {
        $response = json_encode(
            array(
                "status" => "success",
                "message" => "Logged Out successfully and device ID reset."
            )
        );
    } else {
        // Handle error if the update fails
        $response = json_encode(
            array(
                "status" => "failed",
                "message" => "Logout failed: Could not reset device ID."
            )
        );
    }
    $stmtU->close();
} else {
    // Handle case where user ID is not provided
    $response = json_encode(
        array(
            "status" => "failed",
            "message" => "Logout failed: User ID (uid) not provided."
        )
    );
}

// Update the api_requests table with the final response
$stmtU_log = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
$stmtU_log->bind_param('ss', $response, $invid);
$stmtU_log->execute();
$stmtU_log->close();

$db->close(); // Close the database connection

echo $response;
?>