<?php
require_once 'db_connect.php';

$post = json_decode(file_get_contents('php://input'), true);

$services = 'Get_Info';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$companyId = $post['userId'];
$userId = $post['uid'];

//$lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
$stmt = $db->prepare("SELECT users.*, companies.farms_no, companies.reg_no, companies.name AS comp_name, companies.address, companies.address2, companies.address3, companies.address4, companies.phone, companies.fax, companies.email, companies.website, companies.products, companies.type, companies.parent from users, companies where users.customer = companies.id AND users.id= ?");
//$stmt = $db->prepare("SELECT * from users where username= ?");
$stmt->bind_param('s', $userId);
$stmt->execute();
$result = $stmt->get_result();

if(($row = $result->fetch_assoc()) !== null){
    $products = json_decode($row['products'], true);
    
    $message = array();
    $message['id'] = $row['id'];
    $message['username'] = $row['username'];
    $message['name'] = $row['name'];
    $message['role_code'] = $row['role_code'];
    $message['languages'] = $row['languages'];
    $message['customer'] = $row['customer'];
    $message['package'] = (in_array("M", $products) ? 'M' : 'S');
    $message['farm_no'] = $row['farms_no'];
    $message['type'] = $row['type'];
    $message['customer_det'] = array(
        "id" => $row['customer'],
        "reg_no" => $row['reg_no'] ?? '',
        "name" => $row['comp_name'],
        "address" => $row['address'],
        "address2" => $row['address2'] ?? '',
        "address3" => $row['address3'] ?? '',
        "address4" => $row['address4'] ?? '',
        "phone" => $row['phone'],
        "email" => $row['email'],
        "farms_no" => $row['farms_no'] ?? '1',
        "fax" => $row['fax'] ?? '',
        "website" => $row['website'] ?? '',
        "products" => $products,
        "type" => $row['type']
    );

    if($row['farms'] != null){
        $message['farms'] = json_decode($row['farms'], true);
    }
    else{
        $message['farms'] = array();
    }
    
    if($row['type'] == 'CONTRACT'){
        $stmt2 = $db->prepare("SELECT * FROM companies WHERE id= ?");
        $stmt2->bind_param('s', $row['parent']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if(($row2 = $result2->fetch_assoc()) !== null){
            $message['customer_det']['preg_no'] = $row2['reg_no'] ?? '';
            $message['customer_det']['pname'] = $row2['name'] ?? '';
            $message['customer_det']['paddress'] = $row2['address'] ?? '';
            $message['customer_det']['paddress2'] = $row2['address2'] ?? '';
            $message['customer_det']['paddress3'] = $row2['address3'] ?? '';
            $message['customer_det']['paddress4'] = $row2['address4'] ?? '';
            $message['customer_det']['pphone'] = $row2['phone'] ?? '';
            $message['customer_det']['pemail'] = $row2['email'] ?? '';
            $message['customer_det']['pfax'] = $row2['fax'] ?? '';
            $message['customer_det']['pwebsite'] = $row2['website'] ?? '';
        }
    }
    
    $response = json_encode(
        array(
            "status"=> "success", 
            "message"=> $message
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $stmt->close();
    $stmtU->close();
    $db->close();
    echo $response;
} 
else{
    $response = json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Username or Password is wrong"
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $db->close();
    echo $response;
}
?>