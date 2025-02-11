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
   $searchQuery = " AND (name like '%".$searchValue."%' OR farms_code like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from farms WHERE customer = '$company'");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from farms WHERE customer = '$company'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from farms WHERE customer = '$company'".$searchQuery." order by deleted, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
  $states = '';

  if($row['states']!=null && $row['states']!=''){
    $id = $row['states'];

    if ($update_stmt = $db->prepare("SELECT * FROM states WHERE id=?")) {
      $update_stmt->bind_param('s', $id);
      
      // Execute the prepared query.
      if ($update_stmt->execute()) {
        $result1 = $update_stmt->get_result();
        
        if ($row1 = $result1->fetch_assoc()) {
          $states = $row1['states'];
        }
      }
    }
  }

  $data[] = array( 
    "counter"=>$counter,
    "id"=>$row['id'],
    "farms_code"=>$row['farms_code'],
    "name"=>$row['name'],
    "address"=>$row['address'],
    "address2"=>$row['address2'],
    "address3"=>$row['address3'],
    "address4"=>$row['address4'],
    "states"=>$states,
    "suppliers"=>$row['suppliers'],
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