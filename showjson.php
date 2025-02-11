<?php
// Open the file
$myfile = fopen("weight_Details.json", "r") or die("Unable to open file!");

// Read the file contents
$data = fread($myfile, filesize("weight_Details.json"));

// Close the file
fclose($myfile);

// Decode the JSON data
$decoded_data = json_decode($data, true);

// Check if decoding was successful
if ($decoded_data === null) {
    die("Error decoding JSON");
}

$weightdetails = array();

foreach ($decoded_data as $item) {
    // Check if 'symbols' key exists and it's an array
    if (isset($item['symbols']) && is_array($item['symbols'])) {
        $joined_text = '';
        if (count($item['symbols']) >= 6) {
            foreach ($item['symbols'] as $index => $symbol) {
                // Append the text of the symbol to $joined_text
                $joined_text .= $symbol['text'];
                // Add dot (.) after the first symbol
                if ($index === 1) {
                    $joined_text .= '.';
                }
            }
            
            str_replace('-24', '', $joined_text);
            $parts = explode('/', $joined_text);
            $weightdetails[] = array(
                "grossWeight" => $parts[0],
                "tareWeight" => "16.46", 
                "reduceWeight" => "0.0", 
                "netWeight" => (string)((float)$parts[0] - 16.46), 
                "birdsPerCages" => "12", 
                "numberOfBirds" => "24", 
                "numberOfCages" => "2", 
                "grade" => "S", 
                "sex" => "Mixed", 
                "houseNumber" => "1", 
                "groupNumber" => "1", 
                "remark" => ""
            );
        }
    }
}

echo json_encode($weightdetails);
?>
