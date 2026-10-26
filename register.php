<?php
// Ku dar connection file
include('db.php');

// Hubi haddii admin horey u jiro
$check = $conn->query("SELECT COUNT(*) as total FROM admins");
$row = $check->fetch_assoc();
$admin_count = $row['total'];

// Haddii admin horey u jiro - ha oggolaan diiwaangelinta cusub
if ($admin_count > 0) {
    // Redirect login-ka - admin horey buu u jiraa
    echo "<!DOCTYPE html>
<html lang='so'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Xididan - NIC System</title>
    <link rel='icon' type='image/png' href='logo.png'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap' rel='stylesheet'>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0a0f2c 0%, #0d1f5c 40%, #0044cc 100%);
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            position: relative; overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(0,102,255,0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(51,153,255,0.1) 0%, transparent 50%);
        }
        .blocked-container {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            width: 100%; max-width: 440px;
            text-align: center; position: relative; z-index: 1;
            animation: fadeIn 0.6s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
        .lock-icon {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 8px 30px rgba(255,68,68,0.4);
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100% { box-shadow: 0 8px 30px rgba(255,68,68,0.4); } 50% { box-shadow: 0 8px 40px rgba(255,68,68,0.7); } }
        .lock-icon i { font-size: 38px; color: white; }
        h2 { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 12px; }
        p { color: rgba(255,255,255,0.7); font-size: 14px; line-height: 1.6; margin-bottom: 30px; }
        .btn-login {
            background: linear-gradient(135deg, #0044cc, #0066ff);
            color: white; border: none; padding: 14px 30px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            border-radius: 12px; width: 100%;
            transition: all 0.3s; text-decoration: none; display: block;
            box-shadow: 0 4px 15px rgba(0,68,204,0.4);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,68,204,0.6); }
        .btn-login i { margin-right: 8px; }
    </style>
</head>
<body>
    <div class='blocked-container'>
        <div class='lock-icon'><i class='fas fa-ban'></i></div>
        <h2>Diiwaangelinta Xididan</h2>
        <p>Admin account horey ayuu u jiray. Diiwaangelinta cusub waa la xidid si amniga nidaamka loo hubiyo.<br><br>
        Fadlan isticmaal account-kaaga jira si aad ugu gasho.</p>
        <a href='login.php' class='btn-login'>
            <i class='fas fa-sign-in-alt'></i> Gal Halkan
        </a>
    </div>
</body>
</html>";
    $conn->close();
    exit();
}

// Haddii la diro form-ka (admin ma jiro, OK)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $error_msg = "Furayaasha isma lahan!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            $success_msg = "Waa lagu guuleystay! Account-kaaga waa la sameeyay.";
            $redirect_login = true;
        } else {
            $error_msg = "Waxaa dhacay khalad. Fadlan mar kale isku day.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Is Diiwaangeli - NIC System</title>
    <link rel="icon" type="image/png" href="logo.png">
    <meta name="description" content="Samee admin account cusub si aad u maamusho nidaamka aqoonsiga qaranka.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0a0f2c 0%, #0d1f5c 40%, #0044cc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 20% 50%, rgba(0,102,255,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(51,153,255,0.08) 0%, transparent 50%),
                        radial-gradient(ellipse at 50% 80%, rgba(0,44,204,0.15) 0%, transparent 40%);
            animation: bgMove 8s ease-in-out infinite alternate;
        }

        @keyframes bgMove {
            from { transform: translate(0,0) scale(1); }
            to { transform: translate(20px, -20px) scale(1.05); }
        }

        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) rotate(720deg); opacity: 0; }
        }

        .register-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 20px;
            animation: slideUp 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* System Logo / Brand */
        .brand-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ffffff20, #ffffff10);
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .brand-logo i {
            font-size: 30px;
            color: #fff;
        }

        .brand-name {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .card-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .icon-circle {
            width: 76px;
            height: 76px;
            background: linear-gradient(135deg, #0066ff, #3399ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 30px rgba(0, 102, 255, 0.5);
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { box-shadow: 0 8px 30px rgba(0, 102, 255, 0.5); transform: scale(1); }
            50% { box-shadow: 0 8px 45px rgba(0, 102, 255, 0.8); transform: scale(1.03); }
        }

        .icon-circle i {
            font-size: 32px;
            color: white;
        }

        .card-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .card-header .subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.55);
        }

        /* Alert messages */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.15);
            border: 1px solid rgba(255, 68, 68, 0.4);
            color: #ff8080;
        }

        .alert-success {
            background: rgba(0, 200, 100, 0.15);
            border: 1px solid rgba(0, 200, 100, 0.4);
            color: #66ffaa;
        }

        /* Input Groups */
        .input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .input-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group .input-icon {
            position: absolute;
            left: 14px;
            bottom: 14px;
            color: rgba(255,255,255,0.35);
            font-size: 15px;
            transition: color 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            background: rgba(255, 255, 255, 0.07);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            font-size: 14px;
            color: #ffffff;
            transition: all 0.3s;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .input-group input:focus {
            border-color: rgba(0, 102, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.2);
        }

        .input-group input:focus + .input-icon,
        .input-group:focus-within .input-icon {
            color: #3399ff;
        }

        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 14px;
            bottom: 14px;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.3s;
        }
        .toggle-pw:hover { color: #3399ff; }

        /* Divider */
        .form-divider {
            height: 1px;
            background: rgba(255,255,255,0.08);
            margin: 20px 0;
        }

        /* Submit Button */
        .btn-register {
            background: linear-gradient(135deg, #0055ff, #0077ff);
            color: white;
            border: none;
            padding: 15px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s;
            letter-spacing: 0.3px;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 6px 24px rgba(0, 85, 255, 0.45);
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }

        .btn-register:hover::before { left: 100%; }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(0, 85, 255, 0.6);
        }

        .btn-register:active { transform: translateY(0); }
        .btn-register i { margin-right: 8px; }

        /* Login link */
        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .login-link a {
            color: #5599ff;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-link a:hover { color: #88bbff; text-decoration: underline; }

        /* First admin badge */
        .first-admin-badge {
            background: linear-gradient(135deg, rgba(255,200,0,0.15), rgba(255,150,0,0.1));
            border: 1px solid rgba(255,200,0,0.3);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #ffd700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .first-admin-badge i { font-size: 14px; }
    </style>
</head>
<body>

<!-- Floating particles -->
<div class="particle" style="width:80px;height:80px;left:5%;animation-duration:15s;animation-delay:0s;"></div>
<div class="particle" style="width:50px;height:50px;left:25%;animation-duration:20s;animation-delay:3s;"></div>
<div class="particle" style="width:100px;height:100px;left:70%;animation-duration:18s;animation-delay:6s;"></div>
<div class="particle" style="width:60px;height:60px;left:85%;animation-duration:12s;animation-delay:9s;"></div>
<div class="particle" style="width:40px;height:40px;left:50%;animation-duration:25s;animation-delay:2s;"></div>

<div class="register-wrapper">
    <!-- System Brand -->
    <div class="brand-header">
        <div class="brand-logo" style="background: transparent; box-shadow: 0 4px 16px rgba(0, 82, 204, 0.4); width: 64px; height: 64px; border-radius: 50%; overflow: hidden;">
            <img src="logo.png" alt="NIC Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>
        <div class="brand-name">NIC System</div>
    </div>

    <div class="register-card">
        <div class="card-header">
            <div class="icon-circle">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1><i class="fas fa-id-badge" style="margin-right:8px; color:#5599ff;"></i>Is Diiwaangeli</h1>
            <p class="subtitle">Samee Admin Account Cusub</p>
        </div>

        <!-- First Admin Notice -->
        <div class="first-admin-badge">
            <i class="fas fa-star"></i>
            Waxaad noqonaysaa admin-ka ugu horeeya ee nidaamkan!
        </div>

        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error_msg) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_msg) ?>
        </div>
        <?php endif; ?>

        <?php if (empty($redirect_login)): ?>
        <form method="POST" action="" id="registerForm">
            <div class="input-group">
                <label class="input-label">Username</label>
                <input type="text" name="username" id="username" placeholder="Geli magacaaga isticmaalaha" required autocomplete="username">
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="input-group">
                <label class="input-label">Email</label>
                <input type="email" name="email" id="email" placeholder="Geli cinwaankaaga emaylka" required autocomplete="email">
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="input-group">
                <label class="input-label">Password</label>
                <input type="password" name="password" id="password" placeholder="Geli furaha sirta" required autocomplete="new-password">
                <i class="fas fa-lock input-icon"></i>
                <span class="toggle-pw" onclick="togglePw('password', this)" title="Muuji/Qari">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <div class="input-group">
                <label class="input-label">Xaqiiji Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Ku celceli furaha sirta" required autocomplete="new-password">
                <i class="fas fa-shield-halved input-icon"></i>
                <span class="toggle-pw" onclick="togglePw('confirm_password', this)" title="Muuji/Qari">
                    <i class="fas fa-eye"></i>
                </span>
            </div>

            <div class="form-divider"></div>

            <button type="submit" class="btn-register" id="registerBtn">
                <i class="fas fa-user-check"></i> Samee Account
            </button>
        </form>
        <?php else: ?>
        <script>
            setTimeout(function() { window.location.href = 'login.php'; }, 2000);
        </script>
        <p style="text-align:center; color:rgba(255,255,255,0.6); font-size:13px; margin-top:10px;">
            <i class="fas fa-spinner fa-spin"></i> Ku socota login-ka...
        </p>
        <?php endif; ?>

        <p class="login-link">
            Horey account u leedahay? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Gal Halkan</a>
        </p>
    </div>
</div>

<script>
function togglePw(fieldId, icon) {
    const input = document.getElementById(fieldId);
    const i = icon.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        i.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        i.className = 'fas fa-eye';
    }
}

// Form validation before submit
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    const pw = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;
    if (pw !== cpw) {
        e.preventDefault();
        alert('Furayaasha isma lahan! Fadlan hubi.');
    }
});
</script>

</body>
</html>
