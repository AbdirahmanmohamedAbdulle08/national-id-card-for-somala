<?php
// Ku xidh database-ka
include 'db.php';
include 'phpqrcode/qrlib.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Soo qaado xogta
    $stmt = $conn->prepare("SELECT * FROM nic_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Template-ka dambe
        $template_back = imagecreatefromjpeg("back.jpg");

        $black = imagecolorallocate($template_back, 0, 0, 0); // Text color
        $font = __DIR__ . '/Open_Sans/OpenSans-Italic-VariableFont_wdth,wght.ttf';

        if (!file_exists($font)) {
            die("Font-ka lama helin: $font");
        }

        // Sawirka Phone, Citizenship, Address
        
        // Add Address (Ciwaanka)
        imagettftext($template_back, 18, 0, 10, 230, $black, $font, " " . $row['address']); // Qoraalka ciwanka

        // Isku dar xogta NIC-ka oo qrcode ah
       // Isku dar xogta NIC-ka oo dhamaystiran
$nic_data = "NIC Number: " . $row['nic_number'] . "\n" .
            "Full Name: " . $row['full_name'] . "\n" .
            "Date of Birth: " . $row['dob'] . "\n" .
            "Gender: " . $row['gender'] . "\n" .
            "Marital Status: " . $row['marital_status'] . "\n" .
            "Citizenship: " . $row['citizenship'] . "\n" .
            "Mother's Name: " . $row['mother_name'] . "\n" .
            "Address: " . $row['address'] . "\n" .
            "Phone Number: " . $row['phone_number'] . "\n" .
            "Occupation: " . $row['occupation'] . "\n" .
            "Additional Info: " . $row['additional_info'] . "\n" .
            "Issued Date: " . date("Y-m-d") . "\n" .
            "Expiry Date: " . date("Y-m-d", strtotime("+10 years"));


        // Abuur QR Code
        $temp_qr = 'generated_qr/qrcode_' . $row['nic_number'] . '.png';
        QRcode::png($nic_data, $temp_qr, 'L', 4, 4);

        // Geli QR Code-ka sawirka
        $qr_code = imagecreatefrompng($temp_qr);
        imagecopyresampled($template_back, $qr_code, 850, 200, 0, 0, 125, 110, imagesx($qr_code), imagesy($qr_code));

        // Sawirka qofka (image)
        if (file_exists($row['photo_path'])) {
            $user_photo = imagecreatefromjpeg($row['photo_path']);
            imagecopyresampled($template_back, $user_photo, 20, 20, 0, 0, 121, 120, imagesx($user_photo), imagesy($user_photo));
        } else {
            // In case the photo path doesn't exist
            echo "Photo not found.";
            exit();
        }

        // Output image back
        header('Content-Type: image/jpeg');
        imagejpeg($template_back);

        // Nadiifi xusuusta
        imagedestroy($template_back);
        imagedestroy($qr_code);
        imagedestroy($user_photo);
        unlink($temp_qr);
    } else {
        echo "NIC not found.";
    }
} else {
    echo "No ID provided.";
}
?>
