<?php
require_once 'db_connect.php';

session_start();

$message_resource = $db->query("SELECT * FROM message_resource");
$languageArray = Array();

while($row=mysqli_fetch_assoc($message_resource)){
    $languageArray[$row['message_key_code']] = array("en"=>$row['en'],"zh"=>$row['zh'],"my"=>$row['my'],"ne"=>$row['ne']);
}

$_SESSION['languageArray'] = $languageArray;
?>