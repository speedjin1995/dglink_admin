<?php

require_once 'php/db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';

$mapOfWeights = array();
$mapOfHouses = array();
$mapOfBirdsToCages = array();

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
    global $mapOfHouses, $mapOfWeights, $mapOfBirdsToCages, $totalSGross, $totalSCrate, $totalSReduce, $totalSNet, $totalSBirds, $totalSCages, $totalAGross, $totalACrate, $totalAReduce, $totalANet, $totalABirds, $totalACages, $totalGross, $totalCrate, $totalReduce, $totalNet, $totalCrates, $totalBirds, $totalMaleBirds, $totalMaleCages, $totalFemaleBirds, $totalFemaleCages, $totalMixedBirds, $totalMixedCages, $totalCount;

    if (!empty($weightDetails)) {
        $array1 = array(); // group
        $array2 = array(); // house
        $array3 = array(); // houses map
        $array4 = array(); // birds per cages

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

            if(!in_array($element['birdsPerCages'], $array4)){
                $mapOfBirdsToCages[] = array( 
                    'numberOfBirds' => $element['birdsPerCages'],
                    'count' => 0
                );

                array_push($array4, $element['birdsPerCages']);
            } 
            
            $keyB = array_search($element['birdsPerCages'], $array4); 
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


if(isset($_GET['userID'], $_GET['printType'])){
    $id = $_GET['userID'];
    $printType = $_GET['printType'];

    if ($printType == 'Grouped'){
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
                    $fileName = 'F-'.$row['po_no'].'_'.substr($row['customer'], 0, 15).'_'.$row['serial_no'];
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
                    $stmtcomp->close();

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

                            $select_stmt2->close();
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
                    
                    $message = '
        <html>
            <head>
                <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
                <style>
                    @page {
                        margin-left: .3in;
                        margin-right: .3in;
                        margin-top: 3in;
                        margin-bottom: 3in;

                        @top-center {
                            content: element(page-header);
                        }

                        @bottom-center {
                            content: element(page-footer);
                        }
                    }

                    .record {
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

                    .keep-with-next {
                        break-after: avoid-page;
                        page-break-after: avoid;
                    }

                    .avoid-break {
                        break-inside: avoid;
                        page-break-inside: avoid;
                    }

                    table.avoid-break {
                        break-inside: avoid;
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
                </style>
            </head>
            
            <body>';
            // HEADER SECTION - Fixed on every page
            $message .= '
                <section class="record">
                    <div class="page-header">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 60%;border-top: 0px;">
                                        <p>';
                                            $companyFontSize = (mb_strlen($companyNameUpper) > 20) ? '16px' : '20px';

                                            if ($showInlineReg) {
                                                $message .= '
                                                                <span style="font-weight: bold; font-size: ' . $companyFontSize . ';">' . $companyNameUpper . '</span>
                                                                <span style="font-size: 12px;"> (' . $compreg . ')</span><br>';
                                            } else {
                                                $message .= '
                                                                <span style="font-weight: bold; font-size: ' . $companyFontSize . ';">' . $companyNameUpper . '</span>
                                                                <span style="font-size: 12px;"> (' . $compreg . ')</span><br>';
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
                        </table>';
                        
                        $message .= '<table class="table">
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Supplier &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Serial No &nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['serial_no'].'</span>
                                        </p>
                                    </td>
                                    <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.$farmerName.'</span>
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No. &nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['lorry_no'].'</span>
                                        </p>
                                    </td>
                                    <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Product &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['product'].'</span>
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Driver &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.$row['driver_name'].'</span>
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 1 : </span>
                                            <span style="font-size: 12px;font-family: sans-serif;"></span>
                                        </p>
                                    </td>
                                    <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Crate Wt (kg) : </span>
                                            <span style="font-size: 12px;font-family: sans-serif;">'.($totalCrates > 0 ? (string)number_format(($totalCrate / $totalCrates), 2) : '0.00').'</span>
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
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 2 : </span>
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
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Remark &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['remark'].'</span>
                                        </p>
                                    </td>
                                    <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                                        <p>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Page No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;" class="page-number"></span>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;"> of </span>
                                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;" class="total-pages"></span>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table><br>
                        
                        <table class="table" style="margin-bottom: 10px;">
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
                        </table>
                    </div>
                    
                    <div class="page-footer">
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
                                                        $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($totalSBirds > 0 ? number_format(($totalSGross - $totalSCrate) / $totalSBirds, 2, '.', '') : '0.00') . '</td>';
                                                    }
                                                    
                                                    if($totalACages <= 0){
                                                        $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                                    }
                                                    else{
                                                        $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($totalABirds > 0 ? number_format(($totalAGross - $totalACrate) / $totalABirds, 2, '.', '') : '0.00') . '</td>';
                                                    }
                                                    
                                                    if($totalBirds <= 0){
                                                        $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                                    }
                                                    else{
                                                        $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($totalBirds > 0 ? number_format(($totalGross - $totalCrate) / $totalBirds, 2, '.', '') : '0.00') . '</td>';
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
                                        <div style="width:50%; padding-left: 100px;">
                                            <table class="table" style="width: 50%">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 28%;border-top:0px;padding: 0.3rem;font-size: 12px;font-family: sans-serif;">H</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Crates</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Nett(kg)</th>
                                                        <th style="width: 22%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Average</th>
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
                                                        $average = $birdsIn > 0 ? ($nettsIn / $birdsIn) : 0;
                                                        $message .= '<tr>
                                                            <td style="width: 28%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;text-align: center;">'.$group.'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$crateIn.'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$birdsIn.'</td>
                                                            <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$nettsIn.'</td>
                                                            <td style="width: 22%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($average, 2, '.', '').'</td>
                                                        </tr>';
                                                    }
                                                
                                                    $message .= '</tbody>
                                            </table>';

                                            if (count($mapOfHouses) > 3){
                                                $birdPerCageMargin = "45%";
                                            }else{
                                                $birdPerCageMargin = "80%";
                                            }
                                            
                                            $message.= '
                                            <table class="table" style="width: 70%; margin-top: '.$birdPerCageMargin.'; margin-left: 35px">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds/Cage</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Cages</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds</th>
                                                    </tr>';

                                                    if (count($mapOfBirdsToCages) > 0) {
                                                        $totalBirdsInCages = 0;
                                                        $totalCages = 0;

                                                        foreach ($mapOfBirdsToCages as $bc) {
                                                            $message .= '
                                                                <tr>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$bc['numberOfBirds'].'</td>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$bc['count'].'</td>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.((int)$bc['count'] * (int)$bc['numberOfBirds']).'</td>
                                                                </tr>
                                                            ';
                                                            $totalBirdsInCages += ((int)$bc['count'] * (int)$bc['numberOfBirds']);
                                                            $totalCages += (int)$bc['count'];

                                                        }

                                                        // Total row for birds/cages
                                                        $message .= '
                                                                <tr>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;"><b>Total</b></td>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalCages.'</td>
                                                                    <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$totalBirdsInCages.'</td>
                                                                </tr>
                                                            ';
                                                    }
                                                
                                                    $message .= '</tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="page-content">';
                    
                        if (!empty($mapOfWeights)) {
                            foreach ($mapOfWeights as $group) {
                                $message .= '<p class="keep-with-next" style="margin: 0px;"><u style="color: blue;">Group No. ' . $group['groupNumber'] . '</u></p>';
                        
                                if (isset($group['houses']) && is_array($group['houses'])) {
                                    foreach ($group['houses'] as $house) {
                                        $message .= '<div class="avoid-break">';
                                        $message .= '<p style="margin: 0px;">House ' . $house['house'] . '</p>';
                                        $message .= '<table class="table avoid-break">';
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
                                        $message .= '</div>';
                                    }
                                }
                        
                                //$message .= '</div><br>';
                            }
                        }
                    
                    $message .= '
                    </div>
                </section>
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
    }else if ($printType == 'Ungrouped'){
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
                    $fileName = 'F-'.$row['po_no'].'_'.substr($row['customer'], 0, 15).'_'.$row['serial_no'];
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

                    $stmtcomp->close();

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

                            $select_stmt2->close();
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
                    margin-left: .3in;
                    margin-right: .3in;
                    margin-top: 3in;
                    margin-bottom: 3in;

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
            </style>
        </head><body>';

        // Create separate page for each group
        $groupNumber = 0;
        foreach ($mapOfWeights as $groupIndex => $group) {
            $groupNumber++;
            $groupCrates = 0;
            $groupBirds = 0;
            $groupGross = 0.0;
            $groupTare = 0.0;
            $groupNet = 0.0;
            $groupSCages = 0;
            $groupACages = 0;
            $groupSBirds = 0;
            $groupABirds = 0;
            $groupSGross = 0.0;
            $groupAGross = 0.0;
            $groupSCrate = 0.0;
            $groupACrate = 0.0;
            $groupMaleCages = 0;
            $groupFemaleCages = 0;
            $groupMixedCages = 0;
            $groupMaleBirds = 0;
            $groupFemaleBirds = 0;
            $groupMixedBirds = 0;
            $groupMapOfBirdsToCages = array();
            $groupArray3 = array(); 

            // Calculate group totals
            foreach ($group['weightList'] as $element) {
                $groupCrates += intval($element['numberOfCages']);
                $groupBirds += intval($element['numberOfBirds']);
                $groupGross += floatval($element['grossWeight']);
                $groupTare += floatval($element['tareWeight']);

                if ($element['grade'] == 'S') {
                    $groupSCages += intval($element['numberOfCages']);
                    $groupSBirds += intval($element['numberOfBirds']);
                    $groupSGross += floatval($element['grossWeight']);
                    $groupSCrate += floatval($element['tareWeight']);
                } else if ($element['grade'] == 'A') {
                    $groupACages += intval($element['numberOfCages']);
                    $groupABirds += intval($element['numberOfBirds']);
                    $groupAGross += floatval($element['grossWeight']);
                    $groupACrate += floatval($element['tareWeight']);
                }

                if ($element['sex'] == 'Male') {
                    $groupMaleCages += intval($element['numberOfCages']);
                    $groupMaleBirds += intval($element['numberOfBirds']);
                } else if ($element['sex'] == 'Female') {
                    $groupFemaleCages += intval($element['numberOfCages']);
                    $groupFemaleBirds += intval($element['numberOfBirds']);
                } else if ($element['sex'] == 'Mixed') {
                    $groupMixedCages += intval($element['numberOfCages']);
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
                                        $companyFontSize = (mb_strlen($companyNameUpper) > 20) ? '16px' : '20px';

                                        if ($showInlineReg) {
                                            $message .= '
                                            <span style="font-weight: bold; font-size: ' . $companyFontSize . ';">' . $companyNameUpper . '</span>
                                            <span style="font-size: 12px;"> (' . $compreg . ')</span><br>';
                                        } else {
                                            $message .= '
                                            <span style="font-weight: bold; font-size: ' . $companyFontSize . ';">' . $companyNameUpper . '</span>
                                            <span style="font-size: 12px;"> (' . $compreg . ')</span><br>';
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
                    </table>';
                    
                    $message .= '<table class="table">
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Supplier &nbsp;&nbsp&nbsp;&nbsp;&nbsp: </span>
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Serial No. &nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$row['serial_no'].'</span>
                                    </p>
                                </td>
                                <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$farmerName.'</span>
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No. &nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$row['lorry_no'].'</span>
                                    </p>
                                </td>
                                <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Product &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$row['product'].'</span>
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Driver &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$row['driver_name'].'</span>
                                    </p>
                                </td>
                                <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Group Count&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.$groupCrates.'</span>
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 1 : </span>
                                        <span style="font-size: 12px;font-family: sans-serif;"></span>
                                    </p>
                                </td>
                                <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Crate Wt (kg) : </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.($groupCrates > 0 ? (string)number_format(($groupTare / $groupCrates), 2) : '0.00').'</span>
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
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Attendant 2 : </span>
                                        <span style="font-size: 12px;font-family: sans-serif;"></span>
                                    </p>
                                </td>
                                <td style="width: 30%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Nett Wt (kg) &nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;">'.(string)number_format($groupNet, 2).'</span>
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
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Remark &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">'.$row['remark'].'</span>
                                    </p>
                                </td>
                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                                    <p>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Page No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </span>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;" class="page-number"></span>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;"> of </span>
                                        <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;" class="total-pages"></span>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table" style="margin-bottom: 10px;">
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
                    </table>
                </div>';

            // Add page footer for this group
            $message .= '
                <div class="page-footer">
                    <hr>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 50%;border-top:0px;">
                                    <p style="font-size: 12px;font-family: sans-serif;"><b>SUMMARY - GROUP ' . $groupNumber . '</b></p>
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
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupSCages.'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupACages.'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupCrates.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Birds</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupSBirds.'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupABirds.'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Gross Wt (kg)</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupSGross, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupAGross, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupGross, 2, '.', '').'</td>
                                            </tr>';
                                            $message .= '<tr>
                                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Crates Wt (kg)</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupSCrate, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupACrate, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupTare, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Avg kg/Bird</td>';
                                                
                                                if($groupSBirds <= 0){
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                                }
                                                else{
                                                    $groupSNet = $groupSGross - $groupSCrate;
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($groupSBirds > 0 ? number_format($groupSNet / $groupSBirds, 2, '.', '') : '0.00') . '</td>';
                                                }
                                                
                                                if($groupABirds <= 0){
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                                }
                                                else{
                                                    $groupANet = $groupAGross - $groupACrate;
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($groupABirds > 0 ? number_format($groupANet / $groupABirds, 2, '.', '') : '0.00') . '</td>';
                                                }
                                                
                                                if($groupBirds <= 0){
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">0.00</td>';
                                                }
                                                else{
                                                    $message .= '<td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">' . ($groupBirds > 0 ? number_format($groupNet / $groupBirds, 2, '.', '') : '0.00') . '</td>';
                                                }
                                            $message.= '</tr>
                                            <tr>
                                                <td style="width: 40%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Nett Wt (kg)</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupSGross - $groupSCrate, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupAGross - $groupACrate, 2, '.', '').'</td>
                                                <td style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($groupGross - $groupTare, 2, '.', '').'</td>
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
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupMaleCages.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupFemaleCages.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupMixedCages.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupCrates.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;">Birds</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupMaleBirds.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupFemaleBirds.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupMixedBirds.'</td>
                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$groupBirds.'</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 50%;border-top:0px;">
                                    <p style="font-size: 12px;font-family: sans-serif;"><b>SUMMARY - BY HOUSE (GROUP ' . $groupNumber . ')</b></p>
                                        <div style="width:50%; padding-left: 100px;">
                                            <table class="table" style="width: 50%">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 28%;border-top:0px;padding: 0.3rem;font-size: 12px;font-family: sans-serif;">H</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Crates</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Nett(kg)</th>
                                                        <th style="width: 22%;border-top:0px;padding: 0.3rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Average</th>
                                                    </tr>';

                                                    // Display houses for this specific group only
                                                    if (isset($group['houses']) && is_array($group['houses'])) {
                                                        foreach ($group['houses'] as $house) {
                                                            $houseCrates = 0;
                                                            $houseBirds = 0;
                                                            $houseGross = 0.0;
                                                            $houseTare = 0.0;
                                                            $houseNet = 0.0;
                                                            $houseAvg = 0.0;

                                                            foreach ($house['weightList'] as $element){
                                                                $houseCrates += (int)$element['numberOfCages'];
                                                                $houseBirds += (int)$element['numberOfBirds'];
                                                                $houseGross += (float)$element['grossWeight'];
                                                                $houseTare += (float)$element['tareWeight'];
                                                            }

                                                            $houseNet = $houseGross - $houseTare;
                                                            $houseAvg = $houseBirds > 0 ? $houseNet / $houseBirds : 0;
                                                            $message .= '<tr>
                                                                <td style="width: 28%;border-top:0px;padding: 0 0.7rem;font-size: 12px;font-family: sans-serif;font-weight: bold;text-align: center;">'.$house['house'].'</td>
                                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$houseCrates.'</td>
                                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.$houseBirds.'</td>
                                                                <td style="width: 25%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($houseNet, 2).'</td>
                                                                <td style="width: 22%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;text-align: center;">'.number_format($houseAvg, 2, '.', '').'</td>
                                                            </tr>';
                                                        }
                                                    }
                                                
                                                    $message .= '</tbody>
                                            </table>';

                                            if (count($group['houses']) > 3){
                                                $birdPerCageMargin = "45%";
                                            }else{
                                                $birdPerCageMargin = "110%";
                                            }
                                            
                                            $message.= '
                                            <table class="table" style="width: 70%; margin-top: '.$birdPerCageMargin.'; margin-left: 35px">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds/Cage</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Cages</th>
                                                        <th style="width: 20%;border-top:0px;padding: 0 0.7rem;border: 1px solid #000000;font-size: 12px;font-family: sans-serif;background-color: silver;">Birds</th>
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
                                                
                                                    $message .= '</tbody>
                                            </table>
                                        </div>
                                    <div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>';

            // Add page content for this group
            $message .= '<div id="container">
                        <p style="margin: 0px;"><u style="color: blue;">Group No. ' . $group['groupNumber'] . '</u></p>';

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
                            $indexString .= '</tr>';
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
                        }
                    }

                    if ($count > 0) {
                        for ($k = 0; $k < (10 - $count); $k++) {
                            $indexString .= '<td style="border-top:0px;padding: 0 0.7rem;width: 10%;"><p><span style="font-size: 12px;font-family: sans-serif;"></span></p></td>';
                        }
                        $indexString .= '</tr>';
                    }

                    $message .= $indexString;
                    $message .= '</tbody></table><br>';
                }
            }
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