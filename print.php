<?php

require_once 'php/db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';

$mapOfWeights = array();
$mapOfHouses = array();

$totalCount = 0;
$totalGross = 0.0;
$totalCrate = 0.0;
$totalReduce = 0.0;
$totalNet = 0.0;

$totalSGross = 0.0;
$totalSCrate = 0.0;
$totalSReduce = 0.0;
$totalSNet = 0.0;

$totalAGross = 0.0;
$totalACrate = 0.0;
$totalAReduce = 0.0;
$totalANet = 0.0;

$totalCrates = 0;
$totalBirds = 0;
$totalMaleBirds = 0;
$totalSBirds = 0;
$totalABirds = 0;
$totalSCages = 0;
$totalACages = 0;
$totalMaleCages = 0;
$totalFemaleBirds = 0;
$totalFemaleCages = 0;
$totalMixedBirds = 0;
$totalMixedCages = 0;
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

function totalWeight($strings){ 
    $totalSum = 0;

    for ($i =0; $i < count($strings); $i++) {
        if (preg_match('/([\d.]+)/', $strings[$i]['grossWeight'], $matches)) {
            $value = floatval($matches[1]);
            $totalSum += $value;
        }
    }

    return $totalSum;
}

function rearrangeList($weightDetails) {
    global $mapOfHouses, $mapOfWeights, $totalSGross, $totalSCrate, $totalSReduce, $totalSNet, $totalSBirds, $totalSCages, $totalAGross, $totalACrate, $totalAReduce, $totalANet, $totalABirds, $totalACages, $totalGross, $totalCrate, $totalReduce, $totalNet, $totalCrates, $totalBirds, $totalMaleBirds, $totalMaleCages, $totalFemaleBirds, $totalFemaleCages, $totalMixedBirds, $totalMixedCages, $totalCount;

    if (!empty($weightDetails)) {
        $array1 = array(); // group
        $array2 = array(); // house
        $array3 = array(); // houses map

        foreach ($weightDetails as $element) {
            if (!in_array($element['groupNumber'], $array1)) {
                $mapOfWeights[] = array(
                    'groupNumber' => $element['groupNumber'],
                    'houseList' => array(),
                    'houses' => array(),
                    'weightList' => array()
                );
    
                array_push($array1, $element['groupNumber']);
            }
            
            $key1 = array_search($element['groupNumber'], $array1);
    
            if (!in_array($element['houseNumber'], $mapOfWeights[$key1]['houseList'])) {
                $mapOfWeights[$key1]['houses'][] = array(
                    'house' => $element['houseNumber'],
                    'weightList' => array(),
                );
    
                array_push($mapOfWeights[$key1]['houseList'], $element['houseNumber']);
            }
    
            if (!in_array($element['houseNumber'], $array3)) {
                $mapOfHouses[] = array(
                    'houseNumber' => $element['houseNumber'],
                    'weightList' => array()
                );
    
                array_push($array3, $element['houseNumber']);
            }
            
            $key3 = array_search($element['houseNumber'], $array3);
            $key2 = array_search($element['houseNumber'], $mapOfWeights[$key1]['houseList']);
            array_push($mapOfWeights[$key1]['houses'][$key2]['weightList'], $element);
            array_push($mapOfWeights[$key1]['weightList'], $element);
            array_push($mapOfHouses[$key3]['weightList'], $element);

            $totalGross += floatval($element['grossWeight']);
            $totalCrate += floatval($element['tareWeight']);
            $totalReduce += floatval($element['reduceWeight']);
            $totalNet += floatval($element['netWeight']);
            $totalCrates += intval($element['numberOfCages']);
            $totalBirds += intval($element['numberOfBirds']);

            if ($element['sex'] == 'Male') {
                $totalMaleBirds += intval($element['numberOfBirds']);
                $totalMaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Female') {
                $totalFemaleBirds += intval($element['numberOfBirds']);
                $totalFemaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Mixed') {
                $totalMixedBirds += intval($element['numberOfBirds']);
                $totalMixedCages += intval($element['numberOfCages']);
            }

            if ($element['grade'] == 'S') {
                $totalSBirds += intval($element['numberOfBirds']);
                $totalSCages += intval($element['numberOfCages']);
                $totalSGross += floatval($element['grossWeight']);
                $totalSCrate += floatval($element['tareWeight']);
                $totalSReduce += floatval($element['reduceWeight']);
                $totalSNet += floatval($element['netWeight']);
            } elseif ($element['grade'] == 'A') {
                $totalABirds += intval($element['numberOfBirds']);
                $totalACages += intval($element['numberOfCages']);
                $totalAGross += floatval($element['grossWeight']);
                $totalACrate += floatval($element['tareWeight']);
                $totalAReduce += floatval($element['reduceWeight']);
                $totalANet += floatval($element['netWeight']);
            }
            
            $totalCount++;
        }
    }
    
    // Now you can work with $mapOfWeights and the calculated totals as needed.
}


if(isset($_GET['userID'])){
    $id = $_GET['userID'];

    if ($select_stmt = $db->prepare("select weighing.*, farms.name FROM weighing, farms WHERE weighing.farm_id = farms.id AND weighing.id=?")) {
        $select_stmt->bind_param('s', $id);

        if (! $select_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong went execute"
                )); 
        }
        else{
            $result = $select_stmt->get_result();

            if ($row = $result->fetch_assoc()) { 
                $assigned_seconds = strtotime ( $row['start_time'] );
                $completed_seconds = strtotime ( $row['end_time'] );
                $duration = $completed_seconds - $assigned_seconds;

                // Convert duration to minutes and seconds
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;
                
                // Format as "xxx mins and xxx secs"
                $time = sprintf('%d mins and %d secs', $minutes, $seconds);
                $weightData = json_decode($row['weight_data'], true);
                $totalWeight = totalWeight($weightData);
                rearrangeList($weightData);
                $weightTime = json_decode($row['weight_time'], true);
                $cage_data = json_decode($row['cage_data'], true);
                $userName = "-";
                $pages = ceil($totalCount / 180);
                $page = 1;

                if($row['weighted_by'] != null){
                    if ($select_stmt2 = $db->prepare("select * FROM users WHERE id=?")) {
                        $uid = json_decode($row['weighted_by'], true)[0];
                        $select_stmt2->bind_param('s', $uid);
    
                        if ($select_stmt2->execute()) {
                            $result2 = $select_stmt2->get_result();
    
                            if ($row2= $result2->fetch_assoc()) { 
                                $userName = $row2['name'];
                            }
                        }
                    }
                }
                
                
                $message = '<html>
    <head>
        <style>
            @media print {
                @page {
                    margin-left: 0.4in;
                    margin-right: 0.4in;
                    margin-top: 0.1in;
                    margin-bottom: 0.1in;
                }
            }
            
            body{
                width: 21cm;
                height: 29.7cm;
                margin: 30mm 45mm 30mm 45mm;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                
            } 
            
            .table th, .table td {
                padding: 0.70rem;
                vertical-align: top;
                border-top: 1px solid #dee2e6;
                
            } 
            
            .table-bordered {
                border: 1px solid #000000;
            } 
            
            .table-bordered th, .table-bordered td {
                border: 1px solid #000000;
                font-family: sans-serif;
            } 
            
            .row {
                display: flex;
                flex-wrap: wrap;
                margin-top: 20px;
            } 
            
            .col-md-3{
                position: relative;
                width: 25%;
            }
            
            .col-md-9{
                position: relative;
                width: 75%;
            }
            
            .col-md-7{
                position: relative;
                width: 58.333333%;
            }
            
            .col-md-5{
                position: relative;
                width: 41.666667%;
            }
            
            .col-md-6{
                position: relative;
                width: 50%;
            }
            
            .col-md-4{
                position: relative;
                width: 33.333333%;
            }
            
            .col-md-8{
                position: relative;
                width: 66.666667%;
            }
            
            #footer {
                padding: 10px 10px 0px 10px;
                bottom: 0;
                width: 100%;
                height: auto;
            }
        </style>
    </head><body>';
    
    //$noOfRows = 0;
    //for($p=0; $p<$pages; $p++){
        $message .= '<div id="preview-container"><div id="header">
            <table class="table">
                <tbody>
                    <tr>
                        <td style="width: 100%;border-top:0px;text-align:center;"><img src="https://ccb.syncweigh.com/assets/header.png" width="100%" height="auto" /></td>
                    </tr>
                </tbody>
            </table><br>
            
            <table class="table">
                <tbody>
                    <tr>
                        <td colspan="2" style="width: 60%;border-top:0px;padding: 0 0.7rem;">';

                        if(strpos($row['serial_no'], 'S') !== false){
                            $message .= '<p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Customer &nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['customer'].'</span>
                            </p>';
                        }
                        else{
                            $message .= '<p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Supplier : </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['supplier'].'</span>
                            </p>';
                        }
                            
                        $message .= '</td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">DO No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;color: red;">'.$row['po_no'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No. &nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['lorry_no'].'</span>
                            </p>
                        </td>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['name'].'</span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['start_time'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Driver &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['driver_name'].'</span>
                            </p>
                        </td>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farmer &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;"></span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Issued By &nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$userName.'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 1 : </span>
                                <span style="font-size: 12px;font-family: sans-serif;"></span>
                            </p>
                        </td>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Total Count&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$totalCrates.'</span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">First Record : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['start_time'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 2 : </span>
                                <span style="font-size: 12px;font-family: sans-serif;"></span>
                            </p>
                        </td>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Crate Wt (kg) : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.(string)number_format(($totalCrate / $totalCrates), 2).'</span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Last Record : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['end_time'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 3 : </span>
                                <span style="font-size: 12px;font-family: sans-serif;"></span>
                            </p>
                        </td>
                        <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Nett Wt (kg) &nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.(string)number_format(($totalGross - $totalCrate), 2).'</span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Duration &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$time.'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width: 60%;border-top:0px;padding: 0 0.7rem;">
                            <p>&nbsp;</p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>&nbsp;</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width: 60%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Remark &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['remark'].'</span>
                            </p>
                        </td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Page No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$page.' of '.$pages.'</span>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table><br>
            
            <table class="table">
                <tbody>
                    <tr style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;font-family: sans-serif;">
                        <td style="width: 20%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Crate No.  </span>
                            </p>
                        </td>
                        <td colspan="10" style="width: 80%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Weight (kg) / Sample Crate </span>
                            </p>
                        </td>
                    </tr>';
                    
                    $countCage = 1;
                    $indexCount2 = 11;
                    $indexStringCage = '<tr><td style="border-top:0px;padding: 0 0.7rem;width: 20%;">
                        <p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">1</span>
                        </p>
                    </td>';
                    
                    foreach ($cage_data as $cage) {
                        if ($countCage < 10) {
                            $indexStringCage .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;">
                                <p>
                                    <span style="font-size: 12px;font-family: sans-serif;">' . str_replace('kg', '', $cage['data']) .  '/' . $cage['number'] . '</span>
                                </p>
                            </td>';
                            $countCage++;
                        }
                        else {
                            $indexStringCage .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;">
                                <p>
                                    <span style="font-size: 12px;font-family: sans-serif;">' . str_replace('kg', '', $cage['data']) . '/' . $cage['number'] . '</span>
                                </p>
                            </td></tr>'; // Move this line outside of the else block
                            $countCage = 1;
                        }
                    }
    
                    if ($countCage > 0) {
                        for ($k = 0; $k <= (10 - $countCage); $k++) {
                            $indexStringCage .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;"><p><span style="font-size: 12px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p></td>';
                        }
                        $indexStringCage .= '</tr>';
                    }
    
                    $message .= $indexStringCage;
                $message .= '</tbody>
            </table></div><br><div id="container">';
            
            if (!empty($mapOfWeights)) {
                foreach ($mapOfWeights as $group) {
                    $message .= '<p style="margin: 0px;"><u style="color: blue;">Group No. ' . $group['groupNumber'] . '</u></p>';
            
                    if (isset($group['houses']) && is_array($group['houses'])) {
                        foreach ($group['houses'] as $house) {
                            $message .= '<p style="margin: 0px;">House ' . $house['house'] . '</p>';
                            $message .= '<table class="table">';
                            $message .= '<tbody>';
                            $message .= '<tr  style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;font-family: sans-serif;">';
                            $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;"><p>
                                    <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Grade ' . $house['weightList'][0]['grade'] . '</span>
                                </p></td>';
                            $message .= '<td colspan="10" style="width: 80%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Weight (kg) / Bird (Nos)</span>
                                </p>
                            </td>
                        </tr>';
            
                            $count = 0;
                            $newRow = false;
                            $reachNewPage = false;
                            $totalCount = 0;
                            $indexCount2 = 11;
                            $oldWeight = "";
                            $indexString = '<tr><td style="border-top:0px;padding: 0 0.7rem;width: 20%;">
                                <p>
                                    <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">1</span>
                                </p>
                            </td>';
                            
                            foreach ($house['weightList'] as $element) {
                                if ($count < 10) {
                                    $indexString .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;">' . $element['grossWeight'] . '/' . $element['numberOfBirds'] . '</span>
                                        </p>
                                    </td>';
                                    $count++;
                                    $newRow = false;
                                }
                                else {
                                    $indexString .= '</tr>'; // Move this line outside of the else block
                                    $count = 0;
                                    $newRow = true;
                                    $oldWeight = $element['grossWeight'] . '/' . $element['numberOfBirds'];
                                    $indexString .= '<tr><td style="border-top:0px;padding: 0 0.7rem;width: 20%;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">' . $indexCount2 . '</span>
                                        </p>
                                    </td>';
                                    $indexCount2 += 10;
                                    $indexString .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;">' . $oldWeight . '</span>
                                        </p>
                                    </td>';
                                    $count++;
                                    //$noOfRows+=10;
                                }
                                
                                /*if($noOfRows >= 150){
                                    $reachNewPage = true;
                                    break;
                                }*/
                                
                                $totalCount++;
                            }
                            
                            /*if($reachNewPage){
                                break;
                            }*/
            
                            if ($count > 0) {
                                for ($k = 0; $k < (10 - $count); $k++) {
                                    $indexString .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;"><p><span style="font-size: 12px;font-family: sans-serif;"></span></p></td>';
                                }
                                $indexString .= '</tr>';
                                //$noOfRows++;
                            }
                            
                            /*($noOfRows >= 150){
                                break;
                            }*/
            
                            $message .= $indexString;
                            $message .= '</tbody></table><br>';
                        }
                    }
            
                    //$message .= '</div><br>';
                }
            }
            
            $message .= '</div><div id="footer">
                <hr>
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="width: 50%;border-top:0px;">
                                <p style="font-size: 12px;font-family: sans-serif;"><b>SUMMARY - TOTAL</b></p>
                                <table class="table" style="width: 95%">
                                    <tbody>
                                        <tr>
                                            <th style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;"></th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">S</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">A</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Total</th>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Crates</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalSCages.'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalACages.'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalCrates.'</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Birds</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalSBirds.'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalABirds.'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Gross Wt (kg)</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalSGross, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalAGross, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalGross, 2, '.', '').'</td>
                                        </tr>';
                                        $message .= '<tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Crates Wt (kg)</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalSCrate, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalACrate, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalCrate, 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Avg kg/Bird</td>';
                                            
                                            if($totalSCages <= 0){
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                            }
                                            else{
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format(($totalSGross - $totalSCrate)/$totalSBirds, 2, '.', '').'</td>';
                                            }
                                            
                                            if($totalACages <= 0){
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                            }
                                            else{
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format(($totalAGross - $totalACrate)/$totalABirds, 2, '.', '').'</td>';
                                            }
                                            
                                            if($totalBirds <= 0){
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                            }
                                            else{
                                                $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format(($totalGross - $totalCrate)/$totalBirds, 2, '.', '').'</td>';
                                            }
                                        $message.= '</tr>
                                        <tr>
                                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Nett Wt (kg)</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalSGross - $totalSCrate, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalAGross - $totalACrate, 2, '.', '').'</td>
                                            <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($totalGross - $totalCrate, 2, '.', '').'</td>
                                        </tr>
                                    </tbody>
                                </table><br>

                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;"></th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Male</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Female</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Mixed</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Total</th>
                                        </tr>
                                        <tr>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Crates</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalMaleCages.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalFemaleCages.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalMixedCages.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalCrates.'</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Birds</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalMaleBirds.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalFemaleBirds.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalMixedBirds.'</td>
                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalBirds.'</td>
                                        </tr>
                                    </tbody>
                                </table>';
                                $message .= '</td>
                            <td style="width: 50%;border-top:0px;">
                                <p style="font-size: 12px;font-family: sans-serif;"><b>SUMMARY - BY HOUSE</b></p>
                                <table class="table" style="width: 95%">
                                    <tbody>
                                        <tr>
                                            <th style="width: 28%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;">H</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Crates</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds</th>
                                            <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Nett(kg)</th>
                                            <th style="width: 22%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Average</th>
                                        </tr>';

                                        for($j=0; $j<count($mapOfHouses); $j++){
                                            $group = $mapOfHouses[$j]['houseNumber'];
                                            $crateIn = 0;
                                            $birdsIn = 0;
                                            $grossIn = 0.0;
                                            $taresIn = 0.0;
                                            $nettsIn = 0.0;
                                            $average = 0.0;

                                            foreach ($mapOfHouses[$j]['weightList'] as $element){
                                                $crateIn += (int)$element['numberOfCages'];
                                                $birdsIn += (int)$element['numberOfBirds'];
                                                $grossIn += (float)$element['grossWeight'];
                                                $taresIn += (float)$element['tareWeight'];
                                            }

                                            $nettsIn = $grossIn - $taresIn;
                                            $average = $nettsIn / $birdsIn;
                                            $message .= '<tr>
                                                <td style="width: 28%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;text-align: center;">'.$group.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$crateIn.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$birdsIn.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$nettsIn.'</td>
                                                <td style="width: 22%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($average, 2, '.', '').'</td>
                                            </tr>';
                                        }
                                    
                                        $message .= '</tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>';
            
        /*$page++;
        $noOfRows = 0;
        
        if($p != ($pages - 1)){
            $message .= '<p style="page-break-after: always;">&nbsp;</p>';
        }
    }*/
    
    
        //</div>
        //<button id="print-button" onclick="printPreview()">Print Preview</button>
    $message .= '</div><button id="print-button" onclick="printPreview()">Print Preview</button></body></html>';
                
                echo $message;
                /*echo "<script>
                    setTimeout(function(){
                        document.getElementById('footer').style.position = 'fixed';
                        document.body.style.width = 'auto';
                        document.body.style.height = 'auto';
                        document.body.style.margin = '0';
                        document.title = 'F-" . $row['po_no'] . "_" . substr($row['customer'], 0, 15) . "_" . $row['serial_no'] . "';
                        window.print();
                        window.close();
                    }, 1000);
                </script>";*/
                echo "<script>
                    function printPreview() {
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write('<html><title>F-".$row['po_no']."_".substr($row['customer'], 0, 15)."_".$row['serial_no']."</title><style>@media print{@page{margin-left:.3in;margin-right:.3in;margin-top:.1in;margin-bottom:.1in}}table{width:100%;border-collapse:collapse}.table td,.table th{padding:.7rem;vertical-align:top;border-top:1px solid #dee2e6}.table-bordered{border:1px solid #000}.table-bordered td,.table-bordered th{border:1px solid #000;font-family:sans-serif}.row{display:flex;flex-wrap:wrap;margin-top:20px}.col-md-3{position:relative;width:25%}.col-md-9{position:relative;width:75%}.col-md-7{position:relative;width:58.333333%}.col-md-5{position:relative;width:41.666667%}.col-md-6{position:relative;width:50%}.col-md-4{position:relative;width:33.333333%}.col-md-8{position:relative;width:66.666667%}#container {margin-top: 380px; height: 50%; width: 100%; position: relative; display: block; page-break-inside: avoid; page-break-after: auto;}#header, #footer{position: fixed;left: 0;right: 0;padding: 10px;z-index: 1;}#header{top: 0;}#footer{bottom: 0;}table {width: 100%; page-break-inside: auto; page-break-after: always;}tr {page-break-inside: avoid; page-break-after: auto;}thead {display: table-header-group;}tfoot {display: table-footer-group;}</style><body>');
                        printWindow.document.write(document.getElementById('header').outerHTML);
                        printWindow.document.write(document.getElementById('container').outerHTML);
                        printWindow.document.write(document.getElementById('footer').outerHTML);
                        printWindow.document.write('</body></html>');
                        printWindow.document.close();
                        printWindow.print();
                    }
               </script>";
            }
            else{
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Data Not Found"
                    )); 
            }
        }
    }
    else{
        echo json_encode(
            array(
                "status" => "failed",
                "message" => "Something went wrong"
            )); 
    }
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    ); 
}

?>