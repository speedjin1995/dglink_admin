<?php
## Database configuration
require_once 'db_connect.php';

session_start();
$farms = array();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}
else{
    $userId = $_SESSION['userID'];
    $role_code = $_SESSION['role_code'];
    
    $stmt = $db->prepare("SELECT * from users where id = ?");
    $stmt->bind_param('s', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(($row = $result->fetch_assoc()) !== null){
        if($row['farms'] != null){
            $farms = json_decode($row['farms'], true);
        }
    }
}

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value
$totalRecords = 0;
$totalRecordwithFilter = 0;
$data = 0;
$empRecords = null;

## Search 
$searchQuery = "";
if($searchValue != ''){
  $searchQuery = " and (weighing.serial_no like '%".$searchValue."%' or 
  weighing.lorry_no like '%".$searchValue."%' )";
}

if($role_code == 'ADMIN'){
    ## Total number of records without filtering
    $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete'");
    $records = mysqli_fetch_assoc($sel);
    $totalRecords = $records['allcount'];
    
    ## Total number of record with filtering
    $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete'".$searchQuery);
    $records = mysqli_fetch_assoc($sel);
    $totalRecordwithFilter = $records['allcount'];
    
    ## Fetch records
    $empQuery = "select weighing.*, farms.name from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete'".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
    $empRecords = mysqli_query($db, $empQuery);
}
else{
    if(count($farms) > 0){
        $commaSeparatedString = implode(',', $farms);
        
        ## Total number of records without filtering
        //$defaultQuery = 'JSON_CONTAINS(weighing.weighted_by, \'["'.$userId.'"]\') > 0 OR JSON_CONTAINS(weighing.weighted_by, \'['.$userId.']\') AND weighing.farm_id IN ('.$commaSeparatedString.')';
        $defaultQuery = 'weighing.farm_id IN ('.$commaSeparatedString.')';
        $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery);
        $records = mysqli_fetch_assoc($sel);
        $totalRecords = $records['allcount'];
        
        ## Total number of record with filtering
        $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery.' '.$searchQuery);
        $records = mysqli_fetch_assoc($sel);
        $totalRecordwithFilter = $records['allcount'];
        
        ## Fetch records
        $empQuery = "select weighing.*, farms.name from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery.' '.$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
        $empRecords = mysqli_query($db, $empQuery);
    }
    /*else{
        ## Total number of records without filtering
        $defaultQuery = 'JSON_CONTAINS(weighing.weighted_by, \'["'.$userId.'"]\') > 0 OR JSON_CONTAINS(weighing.weighted_by, \'['.$userId.']\')';
        $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery);
        $records = mysqli_fetch_assoc($sel);
        $totalRecords = $records['allcount'];
        
        ## Total number of record with filtering
        $sel = mysqli_query($db,"select count(*) as allcount from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery.' '.$searchQuery);
        $records = mysqli_fetch_assoc($sel);
        $totalRecordwithFilter = $records['allcount'];
        
        ## Fetch records
        $empQuery = "select weighing.*, farms.name from weighing, farms WHERE weighing.farm_id = farms.id AND weighing.deleted = '0' AND weighing.status<>'Complete' AND ".$defaultQuery.' '.$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
        $empRecords = mysqli_query($db, $empQuery);
    }*/
}

$data = array();
$counter = 1;

if($empRecords != null){
    while($row = mysqli_fetch_assoc($empRecords)) {
      $data[] = array( 
        "no"=>$counter,
        "id"=>$row['id'],
        "booking_date"=>$row['booking_date'],
        "status"=>$row['status'],
        "serial_no"=>$row['serial_no'],
        "po_no"=>$row['po_no'],
        "group_no"=>$row['group_no'],
        "customer"=>$row['customer'],
        "supplier"=>$row['supplier'],
        "product"=>$row['product'],
        "driver_name"=>$row['driver_name'],
        "lorry_no"=>$row['lorry_no'],
        "farm_id"=>$row['name'],
        "average_cage"=>$row['average_cage'],
        "average_bird"=>$row['average_bird'],
        "minimum_weight"=>$row['minimum_weight'],
        "maximum_weight"=>$row['maximum_weight'],
        "max_crate"=>$row['max_crate'],
        "weight_data"=>json_decode($row['weight_data'], true),
        "created_datetime"=>$row['created_datetime'],
        "start_time"=>$row['start_time'],
        "end_time"=>$row['end_time']
      );
    
      $counter++;
    }
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