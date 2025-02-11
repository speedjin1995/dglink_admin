<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
$db = mysqli_connect("localhost", "u865368203_chickenweigher", "Aa@111222333", "u865368203_chickenweigher");
$db2 = mysqli_connect("localhost", "u865368203_dglink", "Aa@111222333", "u865368203_dglink");

if(mysqli_connect_errno()){
    echo 'Database connection failed with following errors: ' . mysqli_connect_error();
    die();
}
?>