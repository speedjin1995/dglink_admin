<?php

require_once 'db_connect.php';
// // Load the database configuration file 
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 
 
// Excel file name for download 
$fileName = "Weight-data_" . date('Y-m-d') . ".xls";
 
// Column names 
$fields = array('SERIAL NO', 'ORDER NO', 'BOOKING DATE TIME', 'CUSTOMER', 'PRODUCT NO', 'VEHICLE NO', 'DRIVER NAME', 'FARM', 'WEIGHTED BY', 'START WEIGHT DATE', 'END WEIGHT DATE', 
                'GROSS WEIGHT', 'CAGE WEIGHT', 'NET WEIGHT', 'NUMBER OF BIRDS', 'NUMBER OF CAGES', 'GRADE', 'GENDER', 'HOUSE NUMBER', 'GROUP NUMBER', 'WEIGHT TIME', 'REMARKS'); 


// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 

## Search 
$searchQuery = " ";

if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
    $fromDate = DateTime::createFromFormat('d/m/Y', $_GET['fromDate']);
    $fromDateTime = date_format($fromDate,"Y-m-d 00:00:00");
    $searchQuery = " and created_datetime >= '".$fromDateTime."'";
}

if($_GET['toDate'] != null && $_GET['toDate'] != ''){
    $toDate = DateTime::createFromFormat('d/m/Y', $_GET['toDate']);
    $toDateTime = date_format($toDate,"Y-m-d 23:59:59");
    $searchQuery .= " and created_datetime <= '".$toDateTime."'";
}

if($_GET['farm'] != null && $_GET['farm'] != '' && $_GET['farm'] != '-'){
    $searchQuery .= " and farm_id = '".$_GET['farm']."'";
}

if($_GET['customer'] != null && $_GET['customer'] != '' && $_GET['customer'] != '-'){
    $searchQuery .= " and customer = '".$_GET['customer']."'";
}

// Fetch records from database
$query = $db->query("select * FROM weighing WHERE deleted = '0' AND start_time IS NOT NULL AND end_time IS NOT NULL".$searchQuery."");

echo $query->num_rows;
if($query->num_rows > 0){ 
    // Output each row of the data 
    while($row = $query->fetch_assoc()){ 
        $cid = json_decode($row['weighted_by'], true)[0];
        $weight_data = json_decode($row['weight_data'], true);
        $weight_time = json_decode($row['weight_time'], true);
        $weighted_by = '';
        $farm = '';
            
        if ($update_stmt = $db->prepare("SELECT * FROM users WHERE id=?")) {
            $update_stmt->bind_param('s', $cid);
        
            // Execute the prepared query.
            if ($update_stmt->execute()) {
                $result = $update_stmt->get_result();
                
                if ($row2 = $result->fetch_assoc()) {
                    $weighted_by = $row2['name'];
                }
            }
        }
        
        if ($update_stmt2 = $db->prepare("SELECT * FROM farms WHERE id=?")) {
            $update_stmt2->bind_param('s', $row['farm_id']);
        
            // Execute the prepared query.
            if ($update_stmt2->execute()) {
                $result2 = $update_stmt2->get_result();
                
                if ($row1 = $result2->fetch_assoc()) {
                    $farm = $row1['name'];
                }
            }
        }
        
        $groupList = array();
        $groupCheck = array();
        
        for($i=0; $i<count($weight_data); $i++){
            if(!in_array($weight_data[$i]['groupNumber'], $groupCheck)){
                $groupList[] = array(
                    "groupNo" => $weight_data[$i]['groupNumber'],
                    "totalGross" => 0,
                    "totalTare" => 0,
                    "totalTare" => 0,
                    "totalCage" => 0,
                    "totalBird" => 0,
                    "houseNumber" => $weight_data[$i]['houseNumber'],
                    "grade" => $weight_data[$i]['grade'],
                    "sex" => $weight_data[$i]['sex']
                );
                
                array_push($groupCheck, $weight_data[$i]['groupNumber']);
            }
            
            $key = array_search($weight_data[$i]['groupNumber'], $groupCheck);
            $groupList[$key]['totalGross'] += (float)$weight_data[$i]['grossWeight'];
            $groupList[$key]['totalTare'] += (float)$weight_data[$i]['tareWeight'];
            $groupList[$key]['totalCage'] += (int)$weight_data[$i]['numberOfCages'];
            $groupList[$key]['totalBird'] += (int)$weight_data[$i]['numberOfBirds'];
        }
        
        for($j=0; $j<count($groupList); $j++){
            $totalNet = $groupList[$j]['totalGross'] - $groupList[$j]['totalTare'];
            $assigned_seconds = strtotime ( $row['start_time'] );
            $completed_seconds = strtotime ( $row['end_time'] );
            $duration = $completed_seconds - $assigned_seconds;
            $minutes = floor($duration / 60);
            $seconds = $duration % 60;
            $time = sprintf('%d mins %d secs', $minutes, $seconds);
            
            $lineData = array($row['serial_no'], $row['po_no'], $row['booking_date'], $row['customer'], $row['product'], $row['lorry_no'], $row['driver_name'], $farm,
            $weighted_by, $row['start_time'], $row['end_time'], $groupList[$j]['totalGross'], $groupList[$j]['totalTare'], $totalNet, $groupList[$j]['totalBird'], $groupList[$j]['totalCage'], $groupList[$j]['grade'], $groupList[$j]['sex'], $groupList[$j]['houseNumber'], $groupList[$j]['groupNo'], $time, $row['remark']);
        }
        
        
        array_walk($lineData, 'filterData'); 
        $excelData .= implode("\t", array_values($lineData)) . "\n"; 
    } 
}else{ 
    $excelData .= 'No records found...'. "\n"; 
} 
 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 
 
// Render excel data 
echo $excelData; 
 
exit;
?>
