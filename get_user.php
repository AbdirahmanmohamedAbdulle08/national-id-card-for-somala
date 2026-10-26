<?php
// get_user.php
include 'db.php';

if (isset($_GET['full_name'])) {
    $name = $_GET['full_name'];
    $stmt = $conn->prepare("SELECT dob, gender, marital_status, citizenship, mother_name, address, phone_number, occupation, additional_info FROM nic_records WHERE full_name = ? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
}
$conn->close();
