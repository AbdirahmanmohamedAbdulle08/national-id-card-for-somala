<?php
// File: generate_nic.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($data['full_name']) || !isset($data['nic_number'])) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

// Extract data
$fullName = $data['full_name'];
$dob = $data['dob'];
$gender = $data['gender'];
$maritalStatus = $data['marital_status'];
$address = $data['address'];
$nicNumber = $data['nic_number'];
$photoPath = $data['photo_path'];

// Save NIC text info to file (for demo purposes)
$filename = "generated_nics/" . $nicNumber . "_info.txt";
file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

// Return success response
$response = [
    "success" => true,
    "message" => "NIC data received and saved for $fullName",
    "nic_number" => $nicNumber,
    "file_saved" => $filename
];

echo json_encode($response);
?>
