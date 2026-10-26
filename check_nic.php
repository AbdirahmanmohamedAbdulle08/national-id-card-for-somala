<?php
header("Content-Type: application/json");
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic_number = $_POST['nic_number'] ?? '';

    if (!$nic_number) {
        echo json_encode(['status' => false, 'message' => 'NIC number is required']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM nic_records WHERE nic_number = ?");
    $stmt->bind_param("s", $nic_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($data = $result->fetch_assoc()) {
        echo json_encode([
            'status' => true,
            'data' => [
                'full_name' => $data['full_name'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'photo_path' => $data['photo_path'],
                'nic_number' => $data['nic_number']
            ]
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'NIC not found']);
    }

    $stmt->close();
    $conn->close();
}
?>
