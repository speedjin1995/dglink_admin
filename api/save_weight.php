<?php
require_once 'db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

$post = json_decode(file_get_contents('php://input'), true);

$services = 'Save_Weight';
$requests = json_encode($post);

// Log API request
$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

try {
    // Validate required keys
    if (
        isset(
            $post['status'], $post['product'], $post['timestampData'],
            $post['vehicleNumber'], $post['driverName'], $post['farmId'],
            $post['averageCage'], $post['averageBird'], $post['capturedData'],
            $post['remark'], $post['startTime'], $post['endTime'], $post['cratesCount'],
            $post['numberOfCages'], $post['totalCagesWeight'], $post['weightDetails'],
            $post['cageDetails'], $post['assignedTo'], $post['company']
        )
    ) {
        // 🧩 Variable extraction
        $status = $post['status'];
        $product = $post['product'];
        $vehicleNumber = $post['vehicleNumber'];
        $driverName = $post['driverName'];
        $farmId = $post['farmId'];
        $averageCage = $post['averageCage'];
        $averageBird = $post['averageBird'];
        $capturedData = $post['capturedData'];
        $timestampData = $post['timestampData'];
        $weightDetails = $post['weightDetails'];
        $cageDetails = $post['cageDetails'];
        $cratesCount = $post['cratesCount'];
        $numberOfCages = $post['numberOfCages'];
        $totalCagesWeight = $post['totalCagesWeight'];
        $assignedTo = $post['assignedTo'];
        $company = $post['company'];

        $weighted_by = json_encode([$assignedTo]);
        $max_crates = 0;
        $insert = true;

        $currentDateTime = (new DateTime())->format("Y-m-d H:i:s");
        $remark = $post['remark'];
        $startTime = $post['startTime'];
        $endTime = $post['endTime'];

        $doNo = $post['doNo'] ?? null;
        $customerName = $post['customerName'] ?? null;
        $supplierName = $post['supplierName'] ?? null;
        $minWeight = $post['minWeight'] ?? null;
        $maxWeight = $post['maxWeight'] ?? null;
        $attandence1 = $post['attandence1'] ?? null;
        $attandence2 = $post['attandence2'] ?? null;
        $max_crates = isset($post['max_crates']) ? (int)$post['max_crates'] : 0;

        $serialNo = "";
        $today = date("Y-m-d 00:00:00");

        // Generate serialNo if empty
        if (empty($post['serialNo'])) {
            $serialNo = 'S' . date("Ymd");

            $select_stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM weighing WHERE booking_date >= ? AND company = ? AND deleted='0'");
            $select_stmt->bind_param('ss', $today, $company);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            $count = ($row = $result->fetch_assoc()) ? ((int)$row['cnt'] + 1) : 1;

            $serialNo .= str_pad($count, 4, '0', STR_PAD_LEFT);

            // Ensure unique serial
            do {
                $check = $db->prepare("SELECT COUNT(*) AS cnt FROM weighing WHERE serial_no = ? AND company = ?");
                $check->bind_param('ss', $serialNo, $company);
                $check->execute();
                $r = $check->get_result()->fetch_assoc();
                if ((int)$r['cnt'] === 0) break;

                $count++;
                $serialNo = 'S' . date("Ymd") . str_pad($count, 4, '0', STR_PAD_LEFT);
            } while (true);
        } else {
            $serialNo = $post['serialNo'];
        }

        // Check duplicate
        /*$select_stmt2 = $db->prepare("SELECT COUNT(*) AS cnt FROM weighing WHERE start_time = ? AND weighted_by = ? AND farm_id = ?");
        $select_stmt2->bind_param('sss', $startTime, $weighted_by, $farmId);
        $select_stmt2->execute();
        $row = $select_stmt2->get_result()->fetch_assoc();
        $insert = ((int)$row['cnt'] === 0);*/
        
        $select_stmt2 = $db->prepare("
            SELECT id, serial_no 
            FROM weighing 
            WHERE start_time = ? 
              AND weighted_by = ? 
              AND farm_id = ?
            LIMIT 1
        ");
        $select_stmt2->bind_param('sss', $startTime, $weighted_by, $farmId);
        $select_stmt2->execute();
        $existingRow = $select_stmt2->get_result()->fetch_assoc();
        
        $exists = ($existingRow !== null);

        $data = json_encode($weightDetails);
        $data2 = json_encode($timestampData);
        $data3 = json_encode($cageDetails);

        if (isset($post['id']) && $post['id'] !== null && $post['id'] !== '') {
            // 🧭 Update existing
            $id = $post['id'];
            $update_stmt = $db->prepare("UPDATE weighing 
                SET customer=?, supplier=?, product=?, driver_name=?, lorry_no=?, farm_id=?, 
                    average_cage=?, average_bird=?, minimum_weight=?, maximum_weight=?, 
                    weight_data=?, remark=?, start_time=?, weight_time=?, end_time=?, 
                    total_cage=?, number_of_cages=?, total_cages_weight=?, follower1=?, follower2=?, 
                    status=?, po_no=?, cage_data=?, company=?, weighted_by=? 
                WHERE id=?");
            $update_stmt->bind_param(
                'ssssssssssssssssssssssssss',
                $customerName, $supplierName, $product, $driverName, $vehicleNumber, $farmId,
                $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark, $startTime,
                $data2, $endTime, $cratesCount, $numberOfCages, $totalCagesWeight,
                $attandence1, $attandence2, $status, $doNo, $data3, $company, $weighted_by, $id
            );
            $update_stmt->execute();

            $response = json_encode([
                "status" => "success",
                "message" => "Updated Successfully!!",
                "serialNo" => $serialNo,
                "weightId" => $id
            ]);
        } else {
            // 🧭 Insert new
            if (!$exists) {
                $insert_stmt = $db->prepare("INSERT INTO weighing 
                    (serial_no, customer, supplier, product, driver_name, lorry_no, farm_id, 
                    average_cage, average_bird, minimum_weight, maximum_weight, weight_data, 
                    remark, start_time, weight_time, end_time, total_cage, number_of_cages, 
                    total_cages_weight, follower1, follower2, status, po_no, cage_data, 
                    booking_date, company, weighted_by, created_datetime)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param(
                    'ssssssssssssssssssssssssssss',
                    $serialNo, $customerName, $supplierName, $product, $driverName, $vehicleNumber,
                    $farmId, $averageCage, $averageBird, $minWeight, $maxWeight, $data, $remark,
                    $startTime, $data2, $endTime, $cratesCount, $numberOfCages, $totalCagesWeight,
                    $attandence1, $attandence2, $status, $doNo, $data3, $startTime,
                    $company, $weighted_by, $currentDateTime
                );
                $insert_stmt->execute();

                $id = $insert_stmt->insert_id;
                $response = json_encode([
                    "status" => "success",
                    "message" => "Added Successfully!!",
                    "serialNo" => $serialNo,
                    "weightId" => $id
                ]);
            } else {
                /*$response = json_encode([
                    "status" => "failed",
                    "message" => "Duplicate entry detected (same start time / user / farm)"
                ]);*/
                $response = json_encode([
                    "status"   => "success",
                    "message"  => "Already exists",
                    "serialNo" => $existingRow['serial_no'],
                    "weightId" => $existingRow['id'],
                    "duplicate" => true
                ]);
            }
        }

        // Log API response
        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();

        echo $response;
    } else {
        // Missing required fields
        $response = json_encode(["status" => "failed", "message" => "Please fill in all required fields"]);
        echo $response;
    }

    $db->close();

} catch (Exception $e) {
    // Catch any error
    $errorResponse = json_encode([
        "status" => "failed",
        "message" => "Internal Server Error: " . $e->getMessage(),
        "line" => $e->getLine()
    ]);

    if (isset($db) && $db->connect_errno === 0) {
        $stmtE = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        if (isset($invid)) {
            $stmtE->bind_param('ss', $errorResponse, $invid);
            $stmtE->execute();
            $stmtE->close();
        }
        $db->close();
    }

    echo $errorResponse;
}
?>