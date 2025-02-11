<?php
## Database configuration
require_once 'db_connect.php';
session_start();
$company = $_SESSION['customer'];

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND vehicles.veh_number like '%".$searchValue."%'";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from vehicles LEFT JOIN transporters ON vehicles.driver = transporters.id WHERE vehicles.customer = '$company'");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from vehicles LEFT JOIN transporters ON vehicles.driver = transporters.id WHERE vehicles.deleted='0' AND vehicles.customer = '$company'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select vehicles.*, transporters.transporter_name from vehicles LEFT JOIN transporters ON vehicles.driver = transporters.id WHERE vehicles.deleted='0' AND vehicles.customer = '$company'".$searchQuery." order by vehicles.deleted, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array( 
      "counter"=>$counter,
      "id"=>$row['id'],
      "veh_number"=>$row['veh_number'],
      "transporter_name"=>$row['transporter_name'],
      "attandence_1"=>$row['attandence_1'],
      "attandence_2"=>$row['attandence_2'],
      "deleted"=>$row['deleted']
    );

    $counter++;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data
);

echo json_encode($response);

?>