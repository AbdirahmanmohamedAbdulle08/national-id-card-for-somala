<?php
// Ku xidh database-ka
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Soo qaado xogta
    $stmt = $conn->prepare("SELECT * FROM nic_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Template hore
        $template_front = imagecreatefromjpeg("front.jpg");

        $black = imagecolorallocate($template_front, 0, 0, 0);
        $font = __DIR__ . '/Open_Sans/OpenSans-Italic-VariableFont_wdth,wght.ttf';

        if (!file_exists($font)) {
            die("Font-ka lama helin: $font");
        }

        // Taariikhaha: issued today, expiry after 10 years
        $date_issued = date("Y-m-d");
        $date_expiry = date("Y-m-d", strtotime("+10 years"));

        // Qor qoraalka hore
        imagettftext($template_front, 16, 0, 210, 280, $black, $font, " " . $row['nic_number']);
        imagettftext($template_front, 16, 0, 210, 365, $black, $font, "" . $row['full_name']);
        imagettftext($template_front, 14, 0, 210, 440, $black, $font, " " . $row['gender']);

        // Taariikhda dhalashada
        $text = " " . $row['dob'];
        $font_size = 14;
        $angle = 0;
        $image_width = imagesx($template_front);
        $bbox = imagettfbbox($font_size, $angle, $font, $text);
        $text_width = $bbox[2] - $bbox[0];
        $margin = 390;
        $x_position = $image_width - $text_width - $margin;
        imagettftext($template_front, $font_size, $angle, $x_position, 440, $black, $font, $text);

        // Taariikhda bixinta
        imagettftext($template_front, 14, 0, 210, 520, $black, $font, "" . $date_issued);
        
        // Taariikhda dhicista
        $text = " " . $date_expiry;
        $font_size = 14;
        $angle = 0;
        $image_width = imagesx($template_front);
        $bbox = imagettfbbox($font_size, $angle, $font, $text);
        $text_width = $bbox[2] - $bbox[0];
        $margin = 390;
        $x_position = $image_width - $text_width - $margin;
        imagettftext($template_front, $font_size, $angle, $x_position, 520, $black, $font, $text);

        // Sawirka dhinaca midig
        $user_photo = imagecreatefromjpeg($row['photo_path']);
        imagecopyresampled($template_front, $user_photo, 810, 230, 0, 0, 180, 200, imagesx($user_photo), imagesy($user_photo));
// sawirka hoose 
 $user_photo = imagecreatefromjpeg($row['photo_path']);
        imagecopyresampled($template_front, $user_photo, 35, 560, 0, 0,110, 40, imagesx($user_photo), imagesy($user_photo));
        ///nic lawaad
      imagettftext($template_front, 16, 0, 20, 590, $black, $font, " " . $row['nic_number']);

        // Output image front
        header('Content-Type: image/jpeg');
        imagejpeg($template_front);

        // Nadiifi xusuusta
        imagedestroy($template_front);
        imagedestroy($user_photo);
    } else {
        echo "NIC not found.";
    }
} else {
    echo "No ID provided.";
}
?>
