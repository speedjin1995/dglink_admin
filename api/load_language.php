<?php
require_once 'db_connect.php';

$lang = $db->query("SELECT * FROM message_resource");

$languages = array();

while ($row = mysqli_fetch_assoc($lang)) {
    $code = $row['message_key_code'];

    $languages['en'][$code] = $row['en'];
    $languages['zh'][$code] = $row['zh'];
    $languages['my'][$code] = $row['my'];
    $languages['ne'][$code] = $row['ne'];
}

$db->close();

echo json_encode(
    array(
        "status" => "success", 
        "languages" => $languages
    ), JSON_UNESCAPED_UNICODE
);
?>
