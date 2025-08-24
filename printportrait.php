<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'php/db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';
$compfax = '';
$compwebsite = '';

$mapOfWeights = array();
$mapOfBirdsToCages = array();

$totalGross = 0.0;
$totalCrate = 0.0;
$totalReduce = 0.0;
$totalNet = 0.0;
$totalCrates = 0;
$totalBirds = 0;
$totalMaleBirds = 0;
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
    global $mapOfWeights, $totalGross, $totalCrate, $totalReduce, $totalNet, $totalCrates, $totalBirds, $totalMaleBirds, $totalMaleCages, $totalFemaleBirds, $totalFemaleCages, $totalMixedBirds, $totalMixedCages, $mapOfBirdsToCages;

    if (!empty($weightDetails)) {
        $array1 = array(); // group
        $array2 = array(); // house
        $array3 = array();

        foreach ($weightDetails as $element) {
            if(!in_array($element['groupNumber'], $array1)){
                $mapOfWeights[] = array( 
                    'groupNumber' => $element['groupNumber'],
                    'weightList' => array()
                );

                array_push($array1, $element['groupNumber']);
            }

            $key = array_search($element['groupNumber'], $array1);
            array_push($mapOfWeights[$key]['weightList'], $element);
            

            $totalGross += floatval($element['grossWeight']);
            $totalCrate += floatval($element['tareWeight']);
            $totalReduce += floatval($element['reduceWeight']);
            $totalNet += floatval($element['netWeight']);
            $totalCrates += intval($element['numberOfCages']);
            $totalBirds += intval($element['numberOfBirds']);
            
            if(!in_array($element['birdsPerCages'], $array3)){
                $mapOfBirdsToCages[] = array( 
                    'numberOfBirds' => $element['birdsPerCages'],
                    'count' => 0
                );

                array_push($array3, $element['birdsPerCages']);
            }
            
            $keyB = array_search($element['birdsPerCages'], $array3);
            $mapOfBirdsToCages[$keyB]['count'] += (int)$element['numberOfCages'];

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
        }
    }
    
    // Now you can work with $mapOfWeights and the calculated totals as needed.
}


if(isset($_GET['userID'], $_GET['printType'])){
    $id = $_GET['userID'];
    $printType = $_GET['printType'];

    if ($printType == 'Grouped') {
        if ($select_stmt = $db->prepare("select * FROM weighing WHERE id=?")) {
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
                    $fileName = $row['po_no']."_".substr($row['customer'], 0, 15)."_".$row['serial_no'];
                    $assigned_seconds = strtotime ( $row['start_time'] );
                    $completed_seconds = strtotime ( $row['end_time'] );
                    $duration = $completed_seconds - $assigned_seconds;
                    //$time = date ( 'j g:i:s', $duration );
                    $minutes = floor($duration / 60);
                    $seconds = $duration % 60;
                    
                    // Format minutes and seconds
                    $time = sprintf('%d m %d s', $minutes, $seconds);
                    $weightData = json_decode($row['weight_data'], true);
                    $totalWeight = totalWeight($weightData);
                    rearrangeList($weightData);
                    $weightTime = json_decode($row['weight_time'], true);
                    $userName = "Pri Name";
                    
                    $stmtcomp = $db->prepare("SELECT * FROM companies WHERE id=?");
                    $stmtcomp->bind_param('s', $row['company']);
                    $stmtcomp->execute();
                    $resultc = $stmtcomp->get_result();
                            
                    if ($rowc = $resultc->fetch_assoc()) {
                        $compname = $rowc['name'];
                        $compreg = $rowc['reg_no'] ?? '';
                        $compaddress = $rowc['address'];
                        $compaddress2 = $rowc['address2'] ?? '';
                        $compaddress3 = $rowc['address3'] ?? '';
                        $compaddress4 = $rowc['address4'] ?? '';
                        $compphone = $rowc['phone'] ?? '';
                        $compfax = $rowc['fax'] ?? '';
                        $compiemail = $rowc['email'] ?? '';
                        $compwebsite = $rowc['website'] ?? '';
                    }

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
                    
                    $companyNameUpper = strtoupper($compname);
                    $showInlineReg = strlen($compname) <= 20;

                    $farmerName = '';
                    if($row['farm_id'] != null){
                        if ($farm_stmt = $db->prepare("select * FROM farms WHERE id=?")) {
                            $farm_stmt->bind_param('s', $row['farm_id']);

                            if ($farm_stmt->execute()) {
                                $farmResult = $farm_stmt->get_result();

                                if ($farm_row= $farmResult->fetch_assoc()) { 
                                    $farmerName = $farm_row['name'];
                                }
                            }

                            $farm_stmt->close();
                        }
                    }

                    $message = '<html>
        <head>
            <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
            <style>
                @page {
                    margin-left: .4in;
                    margin-right: .4in;
                    margin-top: 2.8in;
                    margin-bottom: 2in;

                    @top-center {
                        content: element(page-header);
                    }

                    @bottom-center {
                        content: element(page-footer);
                    }
                }

                .page-header {
                    position: running(page-header);
                    font-size: 12px;
                }

                .page-footer {
                    position: running(page-footer);
                    font-size: 12px;
                }

                .page-number::after {
                    content: counter(page);
                }

                .total-pages::after {
                    content: counter(pages);
                }

                .page-number, .total-pages {
                    font-weight: bold;
                    color: #000;
                    display: inline;
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
                    border: 1px dashed black;
                    border-collapse: collapse;
                } 
                
                .table-bordered th, .table-bordered td {
                    border: 1px dashed black;
                    font-family: sans-serif;
                    font-size: 12px;
                    height: 22px
                } 

                .table-full {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 0 0.7rem;
                } 
                
                .table-full th, .table-full td {
                    border: 1px solid black;
                    font-family: sans-serif;
                    padding: 0 0.7rem;
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
            </style>
        </head>
        
        <body>
            <div class="page-header">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="width: 60%;border-top: 0px;">
                                <p>';
                                    if ($showInlineReg) {
                                        $message .= '
                                                        <span style="font-weight: bold; font-size: 20px;">' . $companyNameUpper . '</span>
                                                        <span style="font-size: 12px;"> ' . $compreg . '</span><br>';
                                    } else {
                                        $message .= '
                                                        <span style="font-weight: bold; font-size: 20px;">' . $companyNameUpper . '</span><br>
                                                        <span style="font-size: 12px;">' . $compreg . '</span><br>';
                                    }
                                    
                                    // Address & contact info
                                    $message .= '
                                    <span style="font-size: 14px;">' . $compaddress . ' ' . ($compaddress2 ?? '') . '</span><br>
                                    <span style="font-size: 14px;">' . ($compaddress3 ?? '') . ' ' . ($compaddress4 ?? '') . '</span><br>
                                    <span style="font-size: 14px;">Tel: ' . ($compphone ?? '') . '  Fax: ' . ($compfax ?? '') . '</span><br>
                                    <span style="font-size: 14px;">Email: ' . ($compiemail ?? '') . '</span><br>';
                if (!empty($compwebsite)) {
                    $message .= '<span style="font-size: 14px;">Website: ' . $compwebsite . '</span>';
                }
                $message .= '
                                </p>
                            </td>
                            <td style="vertical-align: top; text-align: right;border-top: 0px;">
                                <p style="margin-left: 50px;">
                                    <span style="font-size: 20px; font-weight: bold;">DELIVERY ORDER</span><br>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table><br>';
                
                $message .= '<table class="table">
                    <tbody>
                        <tr>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">';

                            $message .= '<p>
                                <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Customer : </span>
                                <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;'.$row['customer'].'</span>
                            </p></td>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;"></td>
                        </tr>
                        <tr>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">';

                            $message .= '<p>
                                <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Serial No : </span>
                                <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['serial_no'].'</span>
                            </p>';
                                
                            $message .= '</td>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">DO No.: </span>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;color: red;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['po_no'].'</span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Farm : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$farmerName.'</span>
                                </p>
                            </td>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Date : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['start_time'].'</span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Total Crates : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">'.$totalCrates.'</span>
                                </p>
                            </td>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Lorry No : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['lorry_no'].'</span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Remarks : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">'.$row['remark'].'</span>
                                </p>
                            </td>
                            <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                                <p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Average Crate Wt. : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">'.number_format($row['average_cage'], 2, '.', '').'</span>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table><br>
            </div>

            <div class="page-footer">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="width: 40%;">
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Total Gross Wt.</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.number_format($totalWeight, 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Total Crate Wt.</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.number_format($totalCrate, 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Total Net Wt. </b></td>
                                            <td style="text-align: center;font-size: 14px;">'.number_format(($totalWeight - $totalCrate), 2, '.', '').'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Unit Price</b></td>
                                            <td style="text-align: center;font-size: 14px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Amount</b></td>
                                            <td style="text-align: center;font-size: 14px;"></td>
                                        </tr>
                                    </tbody>
                                </table><br>
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Birds/Cage</b></td>
                                            <td style="text-align: center;font-size: 14px;"><b>Cages</b></td>
                                            <td style="text-align: center;font-size: 14px;"><b>Birds</b></td>
                                        </tr>';
                                    
                                        $totalBirdsInCages = 0;
                                        $totalCages = 0;
                                        for ($bc = 0; $bc < count($mapOfBirdsToCages); $bc++) {
                                            $message .= '<tr>';
                                            $message .= '<td style="text-align: center;font-size: 14px;">' . $mapOfBirdsToCages[$bc]['numberOfBirds'] . '</td>';
                                            $message .= '<td style="text-align: center;font-size: 14px;">' . $mapOfBirdsToCages[$bc]['count'] . '</td>';
                                            $message .= '<td style="text-align: center;font-size: 14px;">' . ((int)$mapOfBirdsToCages[$bc]['count'] * (int)$mapOfBirdsToCages[$bc]['numberOfBirds']) . '</td>';
                                            $message .= '</tr>';
                                            $totalBirdsInCages += ((int)$mapOfBirdsToCages[$bc]['count'] * (int)$mapOfBirdsToCages[$bc]['numberOfBirds']);
                                            $totalCages += (int)$mapOfBirdsToCages[$bc]['count'];
                                        }
                                        
                                        $message .= '<tr>';
                                        $message .= '<td style="text-align: center;font-size: 14px;"><b>Total</b></td>';
                                        $message .= '<td style="text-align: center;font-size: 14px;"><b>'.$totalCages.'</b></td>';
                                        $message .= '<td style="text-align: center;font-size: 14px;"><b>' . $totalBirdsInCages . '</b></td>';
                                        $message .= '</tr>';
                                        
                                    $message .= '</tbody>
                                </table>
                            </td>
                            <td style="width: 30%;">
                                <table class="table-full" style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Mix.</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.$totalMixedBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Male</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.$totalMaleBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Female</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.$totalFemaleBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Total Birds</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.$totalBirds.'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Avg. Bird Wt.</b></td>
                                            <td style="text-align: center;font-size: 14px;">'.number_format((($totalWeight - $totalCrate)/$totalBirds), 2, '.', '').'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="width: 30%;">
                                <table class="table-full" style="width: 90%;">
                                    <tbody>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Loading Start</b></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;">'.$row['start_time'].'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;"><b>Loading End</b></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;">'.$row['end_time'].'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;font-size: 14px;">'.$time.'</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td> 
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="container">';
                
                $message .= '<table class="table-bordered"><tbody>';
                $count = 1;
                $rowCount = 0;
                $rowTotal = 0;
                $allTotal = 0;
                $indexString = '<tr>';
                
                $count = 1;
                $rows = 1;
                $rowCount = 0;
                $rowTotal = 0;
                $allTotal = 0;
                $indexString = '<tr>';
                
                for ($i = 0; $i < count($weightData); $i++) {
                    $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;">'.$weightData[$i]['grossWeight'].'</td>';
                    $rowTotal += (float)$weightData[$i]['grossWeight'];
                    $allTotal += (float)$weightData[$i]['grossWeight'];

                    if($count % 10 == 0){
                        $indexString .= '<td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td></tr>';
                        $rowTotal = 0;
                        $rowCount = 0;
                        $rows++;

                        if($count < count($weightData)){
                            $indexString .= '<tr>';
                        }
                    }
                    else{
                        $rowCount++;
                    }
                    
                    $count++;
                }

                if ($rowCount > 0) {
                    for ($k = 0; $k < (10 - $rowCount); $k++) {
                        if($k == ((10 - $rowCount) - 1)){
                            $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td>';
                        }
                        else{
                            $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td></td>';
                        }
                        
                        $count++;
                    }
                    $indexString .= '</tr>';
                    $rows++;
                    $rowCount = 0;
                }
                
                for ($r = 0; $r <= (25 - $rows); $r++) {
                    $indexString .= '<tr>';
                    
                    for ($k = 0; $k < (10 - $rowCount); $k++) {
                        if($k == ((10 - $rowCount) - 1)){
                            $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"></td>';
                        }
                        else{
                            $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td></td>';
                        }
                        
                        $count++;
                    }
                    $indexString .= '</tr>';
                    $rowCount = 0;
                }
                
                $message .= $indexString;
                $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.number_format($allTotal, 2, '.', '').'</th></tfoot></table>';

                /*for ($j = 0; $j < count($mapOfWeights); $j++) {
                    $message .= '<p style="margin: 0px;"><u style="color: blue;">Group No. ' . $mapOfWeights[$j]['groupNumber'] . '</u></p>';
                    $message .= '<table class="table-bordered"><tbody>';
                    $weightData = $mapOfWeights[$j]['weightList'];

                    $count = 1;
                    $rowCount = 0;
                    $rowTotal = 0;
                    $allTotal = 0;
                    $indexString = '<tr>';
                    
                    for ($i = 0; $i < count($weightData); $i++) {
                        $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;">'.$weightData[$i]['grossWeight'].'</td>';
                        $rowTotal += (float)$weightData[$i]['grossWeight'];
                        $allTotal += (float)$weightData[$i]['grossWeight'];

                        if($count % 10 == 0){
                            $indexString .= '<td style="width: 10%;text-align: center;"><b>'.$rowTotal.'</b></td></tr>';
                            $rowTotal = 0;
                            $rowCount = 0;

                            if($count < count($weightData)){
                                $indexString .= '<tr>';
                            }
                        }
                        else{
                            $rowCount++;
                        }
                        
                        $count++;
                    }

                    if ($rowCount > 0) {
                        for ($k = 0; $k < (10 - $rowCount); $k++) {
                            if($k == ((10 - $rowCount) - 1)){
                                $indexString .= '<td style="width: 4%;text-align: center;"></td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 1, '.', '').'</b></td>';
                            }
                            else{
                                $indexString .= '<td></td><td></td>';
                            }
                        }
                        $indexString .= '</tr>';
                    }
                    
                    $message .= $indexString;
                    $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.$allTotal.'</th></tfoot></table><br>';
                }*/
                
                $message .= '</div>
                <!--button id="print-button" onclick="printPreview()">Print Preview</button-->
            </body>
        </html>';

                    echo $message;
                    echo '
                        <script src="plugins/jquery/jquery.min.js"></script>
                        <script src="plugins/jquery-validation/jquery.validate.min.js"></script>

                        <script>
                            $(document).ready(function () {
                                PagedPolyfill.preview().then(() => {
                                    const buttonWrapper = document.createElement("div");
                                    buttonWrapper.className = "print-button-wrapper";
                                    buttonWrapper.setAttribute("data-pagedjs-ignore", "");
                                    buttonWrapper.style.position = "fixed";
                                    buttonWrapper.style.bottom = "20px";
                                    buttonWrapper.style.left = "50%";
                                    buttonWrapper.style.transform = "translateX(-50%)";
                                    buttonWrapper.style.zIndex = "9999";

                                    const printButton = document.createElement("button");
                                    printButton.textContent = "🖨️ Print Preview";
                                    printButton.style.background = "#007bff"; // Bootstrap blue
                                    printButton.style.color = "#fff";
                                    printButton.style.border = "none";
                                    printButton.style.padding = "10px 20px";
                                    printButton.style.borderRadius = "6px";
                                    printButton.style.cursor = "pointer";
                                    printButton.style.fontSize = "14px";
                                    printButton.style.fontWeight = "500";
                                    printButton.style.fontFamily = "Segoe UI, sans-serif";
                                    printButton.style.boxShadow = "0 2px 6px rgba(0,0,0,0.15)";
                                    printButton.style.transition = "background 0.3s ease";

                                    printButton.onmouseover = () => {
                                        printButton.style.background = "#0056b3"; // darker on hover
                                    };
                                    printButton.onmouseout = () => {
                                        printButton.style.background = "#007bff";
                                    };

                                    printButton.onclick = function () {
                                        buttonWrapper.style.display = "none";
                                        setTimeout(() => {
                                            document.title = "'.$fileName.'";
                                            window.print();
                                            window.close();
                                        }, 100);
                                    };

                                    buttonWrapper.appendChild(printButton);
                                    document.body.appendChild(buttonWrapper);
                                });
                            });
                        </script>
                    ';
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
    }else if ($printType == 'Ungrouped') {
        if ($select_stmt = $db->prepare("select * FROM weighing WHERE id=?")) {
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
                    $fileName = $row['po_no']."_".substr($row['customer'], 0, 15)."_".$row['serial_no'];
                    $assigned_seconds = strtotime ( $row['start_time'] );
                    $completed_seconds = strtotime ( $row['end_time'] );
                    $duration = $completed_seconds - $assigned_seconds;
                    $minutes = floor($duration / 60);
                    $seconds = $duration % 60;
                    
                    // Format minutes and seconds
                    $time = sprintf('%d m %d s', $minutes, $seconds);
                    $weightData = json_decode($row['weight_data'], true);
                    $totalWeight = totalWeight($weightData);
                    rearrangeList($weightData);
                    $weightTime = json_decode($row['weight_time'], true);
                    $userName = "Pri Name";
                    
                    $stmtcomp = $db->prepare("SELECT * FROM companies WHERE id=?");
                    $stmtcomp->bind_param('s', $row['company']);
                    $stmtcomp->execute();
                    $resultc = $stmtcomp->get_result();
                            
                    if ($rowc = $resultc->fetch_assoc()) {
                        $compname = $rowc['name'];
                        $compreg = $rowc['reg_no'] ?? '';
                        $compaddress = $rowc['address'];
                        $compaddress2 = $rowc['address2'] ?? '';
                        $compaddress3 = $rowc['address3'] ?? '';
                        $compaddress4 = $rowc['address4'] ?? '';
                        $compphone = $rowc['phone'] ?? '';
                        $compfax = $rowc['fax'] ?? '';
                        $compiemail = $rowc['email'] ?? '';
                        $compwebsite = $rowc['website'] ?? '';
                    }

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
                    
                    $companyNameUpper = strtoupper($compname);
                    $showInlineReg = strlen($compname) <= 20;

                    $farmerName = '';
                    if($row['farm_id'] != null){
                        if ($farm_stmt = $db->prepare("select * FROM farms WHERE id=?")) {
                            $farm_stmt->bind_param('s', $row['farm_id']);

                            if ($farm_stmt->execute()) {
                                $farmResult = $farm_stmt->get_result();

                                if ($farm_row= $farmResult->fetch_assoc()) { 
                                    $farmerName = $farm_row['name'];
                                }
                            }

                            $farm_stmt->close();
                        }
                    }

                    $message = '<html>
        <head>
            <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
            <style>
                @page {
                    margin-left: .4in;
                    margin-right: .4in;
                    margin-top: 2.8in;
                    margin-bottom: 2in;

                    @top-center {
                        content: element(page-header);
                    }

                    @bottom-center {
                        content: element(page-footer);
                    }
                }

                .group-page {
                    page-break-after: always;
                }

                .page-header {
                    position: running(page-header);
                    font-size: 12px;
                }

                .page-footer {
                    position: running(page-footer);
                    font-size: 12px;
                }

                .page-number::after {
                    content: counter(page);
                }

                .total-pages::after {
                    content: counter(pages);
                }

                .page-number, .total-pages {
                    font-weight: bold;
                    color: #000;
                    display: inline;
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
                    border: 1px dashed black;
                    border-collapse: collapse;
                } 
                
                .table-bordered th, .table-bordered td {
                    border: 1px dashed black;
                    font-family: sans-serif;
                    font-size: 12px;
                    height: 22px
                } 

                .table-full {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 0 0.7rem;
                } 
                
                .table-full th, .table-full td {
                    border: 1px solid black;
                    font-family: sans-serif;
                    padding: 0 0.7rem;
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
            </style>
        </head><body>';

        // Create separate page for each group (similar to the other print.php)
        for ($j = 0; $j < count($mapOfWeights); $j++) {
            $groupNumber = $mapOfWeights[$j]['groupNumber'];
            $groupWeightData = $mapOfWeights[$j]['weightList'];
            
            // Calculate group totals
            $groupCrates = 0;
            $groupBirds = 0;
            $groupGross = 0.0;
            $groupTare = 0.0;
            $groupNet = 0.0;
            $groupMaleBirds = 0;
            $groupFemaleBirds = 0;
            $groupMixedBirds = 0;
            $groupMapOfBirdsToCages = array();
            $groupArray3 = array(); 

            foreach ($groupWeightData as $element) { 
                $groupCrates += intval($element['numberOfCages']);
                $groupBirds += intval($element['numberOfBirds']);
                $groupGross += floatval($element['grossWeight']);
                $groupTare += floatval($element['tareWeight']);
                
                if ($element['sex'] == 'Male') {
                    $groupMaleBirds += intval($element['numberOfBirds']);
                } elseif ($element['sex'] == 'Female') {
                    $groupFemaleBirds += intval($element['numberOfBirds']);
                } elseif ($element['sex'] == 'Mixed') {
                    $groupMixedBirds += intval($element['numberOfBirds']);
                }

                // Calculate group-specific birds per cage mapping
                if($element['birdsPerCages'] != null){
                    if(!in_array($element['birdsPerCages'], $groupArray3)){
                        $groupMapOfBirdsToCages[] = array( 
                            'numberOfBirds' => $element['birdsPerCages'],
                            'count' => 0
                        );
                        array_push($groupArray3, $element['birdsPerCages']);
                    }
                }
                else{
                    $birdsPerCages = (string)((int)$element['numberOfBirds'] / (int)$element['numberOfCages']);
                    
                    if(!in_array($birdsPerCages, $groupArray3)){
                        $groupMapOfBirdsToCages[] = array( 
                            'numberOfBirds' => $birdsPerCages,
                            'count' => 0
                        );
                        array_push($groupArray3, $birdsPerCages);
                    }
                }
                
                if($element['birdsPerCages'] != null){
                        $keyB = array_search($element['birdsPerCages'], $groupArray3);
                }
                else{
                        $birdsPerCages = (string)((int)$element['numberOfBirds'] / (int)$element['numberOfCages']);
                        $keyB = array_search($birdsPerCages, $groupArray3);
                }
                
                $groupMapOfBirdsToCages[$keyB]['count'] += (int)$element['numberOfCages'];
            }
            
            $groupNet = $groupGross - $groupTare;

            // Start new page section
            $message .= '<section class="group-page">';
            
            // Add page header for this group
            $message .= '
                <div class="page-header">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 60%;border-top: 0px;">
                                    <p>';
                                        if ($showInlineReg) {
                                            $message .= '
                                                            <span style="font-weight: bold; font-size: 20px;">' . $companyNameUpper . '</span>
                                                            <span style="font-size: 12px;"> ' . $compreg . '</span><br>';
                                        } else {
                                            $message .= '
                                                            <span style="font-weight: bold; font-size: 20px;">' . $companyNameUpper . '</span><br>
                                                            <span style="font-size: 12px;">' . $compreg . '</span><br>';
                                        }
                                        
                                        // Address & contact info
                                        $message .= '
                                        <span style="font-size: 14px;">' . $compaddress . ' ' . ($compaddress2 ?? '') . '</span><br>
                                        <span style="font-size: 14px;">' . ($compaddress3 ?? '') . ' ' . ($compaddress4 ?? '') . '</span><br>
                                        <span style="font-size: 14px;">Tel: ' . ($compphone ?? '') . '  Fax: ' . ($compfax ?? '') . '</span><br>
                                        <span style="font-size: 14px;">Email: ' . ($compiemail ?? '') . '</span><br>';
                    if (!empty($compwebsite)) {
                        $message .= '<span style="font-size: 14px;">Website: ' . $compwebsite . '</span>';
                    }
                    $message .= '
                                    </p>
                                </td>
                                <td style="vertical-align: top; text-align: right;border-top: 0px;">
                                    <p style="margin-left: 50px;">
                                        <span style="font-size: 20px; font-weight: bold;">DELIVERY ORDER</span><br>
                                        <span style="font-size: 14px; font-weight: bold; color: blue;">Group ' . $groupNumber . ' of ' . count($mapOfWeights) . '</span>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table><br>';
                    
                    $message .= '<table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">';

                                $message .= '<p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Customer : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;'.$row['customer'].'</span>
                                </p></td>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;"></td>
                            </tr>
                            <tr>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">';

                                $message .= '<p>
                                    <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Serial No : </span>
                                    <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['serial_no'].'</span>
                                </p>';
                                    
                                $message .= '</td>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">DO No.: </span>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;color: red;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['po_no'].'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Farm : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$farmerName.'</span>
                                    </p>
                                </td>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Date : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['start_time'].'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Group Crates : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">'.$groupCrates.'</span>
                                    </p>
                                </td>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Lorry No : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['lorry_no'].'</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Remarks : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">'.$row['remark'].'</span>
                                    </p>
                                </td>
                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 14px;font-family: sans-serif;font-weight: bold;">Average Crate Wt. : </span>
                                        <span style="font-size: 14px;font-family: sans-serif;">'.number_format($row['average_cage'], 2, '.', '').'</span>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table><br>
                </div>';

            // Add page footer for this group
            $message .= '
                <div class="page-footer">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 40%;">
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Group Gross Wt.</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.number_format($groupGross, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Group Crate Wt.</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.number_format($groupTare, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Group Net Wt. </b></td>
                                                <td style="text-align: center;font-size: 14px;">'.number_format($groupNet, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Unit Price</b></td>
                                                <td style="text-align: center;font-size: 14px;"></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Amount</b></td>
                                                <td style="text-align: center;font-size: 14px;"></td>
                                            </tr>
                                        </tbody>
                                    </table><br>
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Birds/Cage</b></td>
                                                <td style="text-align: center;font-size: 14px;"><b>Cages</b></td>
                                                <td style="text-align: center;font-size: 14px;"><b>Birds</b></td>
                                            </tr>';

                                            if (count($groupMapOfBirdsToCages) > 0) {
                                                $groupTotalBirdsInCages = 0;
                                                $groupTotalCages = 0;

                                                foreach ($groupMapOfBirdsToCages as $bc) {
                                                    $message .= '
                                                        <tr>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$bc['numberOfBirds'].'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$bc['count'].'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.((int)$bc['count'] * (int)$bc['numberOfBirds']).'</td>
                                                        </tr>
                                                    ';
                                                    $groupTotalBirdsInCages += ((int)$bc['count'] * (int)$bc['numberOfBirds']);
                                                    $groupTotalCages += (int)$bc['count'];

                                                }
                                                
                                                // Total row for birds/cages
                                                $message .= '
                                                        <tr>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;"><b>Total</b></td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupTotalCages.'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupTotalBirdsInCages.'</td>
                                                        </tr>
                                                    ';
                                            }

                                            $message .= '
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 30%;">
                                    <table class="table-full" style="width: 100%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Mix.</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.$groupMixedBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Male</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.$groupMaleBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Female</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.$groupFemaleBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Group Birds</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.$groupBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Avg. Bird Wt.</b></td>
                                                <td style="text-align: center;font-size: 14px;">'.number_format(($groupNet/$groupBirds), 2, '.', '').'</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 30%;">
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Loading Start</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;">'.$row['start_time'].'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;"><b>Loading End</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;">'.$row['end_time'].'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;font-size: 14px;">'.$time.'</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td> 
                            </tr>
                        </tbody>
                    </table>
                </div>';

            // Add page content for this group
            $message .= '<div id="container">';
            $message .= '<p style="margin: 0px;"><u style="color: blue;">Group No. ' . $groupNumber . '</u></p>';
            $message .= '<table class="table-bordered"><tbody>';

            $count = 1;
            $rows = 1;
            $rowCount = 0;
            $rowTotal = 0;
            $allTotal = 0;
            $indexString = '<tr>';
            
            for ($i = 0; $i < count($groupWeightData); $i++) {
                $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;">'.$groupWeightData[$i]['grossWeight'].'</td>';
                $rowTotal += (float)$groupWeightData[$i]['grossWeight'];
                $allTotal += (float)$groupWeightData[$i]['grossWeight'];

                if($count % 10 == 0){
                    $indexString .= '<td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td></tr>';
                    $rowTotal = 0;
                    $rowCount = 0;
                    $rows++;

                    if($count < count($groupWeightData)){
                        $indexString .= '<tr>';
                    }
                }
                else{
                    $rowCount++;
                }
                
                $count++;
            }

            if ($rowCount > 0) {
                for ($k = 0; $k < (10 - $rowCount); $k++) {
                    if($k == ((10 - $rowCount) - 1)){
                        $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td>';
                    }
                    else{
                        $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td></td>';
                    }
                    
                    $count++;
                }
                $indexString .= '</tr>';
                $rows++;
                $rowCount = 0;
            }
            
            for ($r = 0; $r <= (25 - $rows); $r++) {
                $indexString .= '<tr>';
                
                for ($k = 0; $k < (10 - $rowCount); $k++) {
                    if($k == ((10 - $rowCount) - 1)){
                        $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"></td>';
                    }
                    else{
                        $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td></td>';
                    }
                    
                    $count++;
                }
                $indexString .= '</tr>';
                $rowCount = 0;
            }
            
            $message .= $indexString;
            $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.number_format($allTotal, 2, '.', '').'</th></tfoot></table>';
            $message .= '</div>';
            $message .= '</section>';
        }

        $message .= '</body></html>';
                    
        echo $message;
        echo '
            <script src="plugins/jquery/jquery.min.js"></script>
            <script src="plugins/jquery-validation/jquery.validate.min.js"></script>

            <script>
                $(document).ready(function () {
                    PagedPolyfill.preview().then(() => {
                        const buttonWrapper = document.createElement("div");
                        buttonWrapper.className = "print-button-wrapper";
                        buttonWrapper.setAttribute("data-pagedjs-ignore", "");
                        buttonWrapper.style.position = "fixed";
                        buttonWrapper.style.bottom = "20px";
                        buttonWrapper.style.left = "50%";
                        buttonWrapper.style.transform = "translateX(-50%)";
                        buttonWrapper.style.zIndex = "9999";

                        const printButton = document.createElement("button");
                        printButton.textContent = "🖨️ Print Preview";
                        printButton.style.background = "#007bff"; // Bootstrap blue
                        printButton.style.color = "#fff";
                        printButton.style.border = "none";
                        printButton.style.padding = "10px 20px";
                        printButton.style.borderRadius = "6px";
                        printButton.style.cursor = "pointer";
                        printButton.style.fontSize = "14px";
                        printButton.style.fontWeight = "500";
                        printButton.style.fontFamily = "Segoe UI, sans-serif";
                        printButton.style.boxShadow = "0 2px 6px rgba(0,0,0,0.15)";
                        printButton.style.transition = "background 0.3s ease";

                        printButton.onmouseover = () => {
                            printButton.style.background = "#0056b3"; // darker on hover
                        };
                        printButton.onmouseout = () => {
                            printButton.style.background = "#007bff";
                        };

                        printButton.onclick = function () {
                            buttonWrapper.style.display = "none";
                            setTimeout(() => {
                                document.title = "'.$fileName.'";
                                window.print();
                                window.close();
                            }, 100);
                        };

                        buttonWrapper.appendChild(printButton);
                        document.body.appendChild(buttonWrapper);
                    });
                });
            </script>
        ';
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