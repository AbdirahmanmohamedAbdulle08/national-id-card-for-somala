<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Hubi username
    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("ERROR in SQL prepare: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // Login guulaysatay
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $row['username'];

            header("Location: dashboard.php");
            exit();
        } else {
            // Password khaldan
            header("Location: login.php?error=wrong_password");
            exit();
        }
    } else {
        // Username lama helin
        header("Location: login.php?error=user_not_found");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
