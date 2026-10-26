<?php
include('db.php');
session_start();

// Haddii admin horey u galay, u dir dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Hubi haddii admin jiro
$check = $conn->query("SELECT COUNT(*) as total FROM admins");
$row = $check->fetch_assoc();
$admin_count = $row['total'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Nidaamka Kaarka Aqoonsiga Qaranka</title>
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0052cc;
            --primary-dark: #003d99;
            --primary-light: #2684ff;
            --somali-blue: #4189e8;
            --bg-dark: #081026;
            --bg-darker: #040817;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 14px;
            --radius-lg: 24px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-darker) 0%, var(--bg-dark) 50%, #0d2159 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Circles in Background */
        .ambient-glow-1 {
            position: absolute;
            top: 10%;
            left: 15%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(0, 82, 204, 0.25) 0%, rgba(0, 82, 204, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            bottom: 10%;
            right: 15%;
            width: 460px;
            height: 460px;
            background: radial-gradient(circle, rgba(65, 137, 232, 0.2) 0%, rgba(65, 137, 232, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
        }

        /* Top Back to Home Button */
        .back-home-btn {
            position: absolute;
            top: 28px;
            left: 28px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.25s ease;
            z-index: 10;
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            transform: translateX(-4px);
        }

        /* Login Card Container */
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 44px 38px 36px;
            text-align: center;
            position: relative;
            z-index: 2;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo Wrap */
        .login-logo-container {
            position: relative;
            width: 92px;
            height: 92px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 3px;
            box-shadow: 0 8px 24px rgba(0, 82, 204, 0.28),
                        0 0 0 3px rgba(0, 82, 204, 0.15);
            transition: transform 0.3s ease;
        }

        .login-logo-container:hover {
            transform: scale(1.05);
        }

        .login-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        /* Republic & Title Branding */
        .gov-sub-heading {
            font-size: 11px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 6px;
            display: block;
        }

        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }

        .login-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Alerts */
        .alert-box {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-error i {
            font-size: 18px;
            color: #ef4444;
            flex-shrink: 0;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-warning i {
            font-size: 18px;
            color: #f59e0b;
            flex-shrink: 0;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .input-field {
            width: 100%;
            padding: 14px 44px 14px 48px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 15px;
            font-family: inherit;
            color: var(--text-main);
            background: #f8fafc;
            outline: none;
            transition: all 0.25s ease;
        }

        .input-field:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 82, 204, 0.12);
        }

        .input-field:focus ~ .input-icon-left {
            color: var(--primary);
        }

        /* Password Toggle Button */
        .toggle-pw-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 16px;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .toggle-pw-btn:hover {
            color: var(--primary);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(0, 82, 204, 0.35);
            transition: all 0.25s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(0, 82, 204, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* Card Footer */
        .card-footer-info {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .security-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .security-tag i {
            color: #10b981;
        }

        .system-version {
            font-size: 11px;
            color: #94a3b8;
        }

        .register-callout {
            font-size: 13px;
            color: #475569;
        }

        .register-callout a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .register-callout a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 34px 24px 28px;
            }
            .back-home-btn {
                top: 16px;
                left: 16px;
                padding: 8px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Lighting -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <!-- Back to Landing Page -->
   

    <!-- Login Container -->
    <div class="login-card">
        <!-- Logo -->
        <div class="login-logo-container">
            <img src="logo.png" alt="Somalia National ID Card Logo">
        </div>

        <span class="gov-sub-heading">Jamhuuriyadda Federaalka Soomaaliya</span>
        <h1 class="login-title">Albaabka Maamulka</h1>
        <p class="login-desc">Geli aqoonsigaaga si aad u maamusho nidaamka NIC</p>

        <!-- Initial Setup Warning if 0 admins -->
        <?php if ($admin_count === 0): ?>
        <div class="alert-box alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Nidaamku waa cusub yahay!</strong> Ma jirto account admin ah. Fadlan samee account-ka koowaad.
            </div>
        </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (isset($_GET['error'])): ?>
        <div class="alert-box alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <div>
                <?php
                    $err = $_GET['error'];
                    if ($err === 'wrong_password')   echo "Password-ka aad gelisay waa khaldan yahay. Fadlan dib u hubi.";
                    elseif ($err === 'user_not_found') echo "Username-ka aad gelisay lama helin. Fadlan hubi.";
                    else echo "Waxaa dhacay khalad aan la filayn. Fadlan mar kale isku day.";
                ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login_process.php" method="POST" id="loginForm">
            <!-- Username -->
            <div class="form-group">
                <label for="username" class="form-label">Magaca Isticmaalaha (Username)</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-shield input-icon-left"></i>
                    <input type="text" name="username" id="username" class="input-field" placeholder="Geli username-kaaga" required autocomplete="username" autofocus>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Furaha Sirta ah (Password)</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon-left"></i>
                    <input type="password" name="password" id="password" class="input-field" placeholder="Geli password-kaaga" required autocomplete="current-password">
                    <button type="button" class="toggle-pw-btn" onclick="togglePw()" title="Muuji / Qari Furaha">
                        <i class="fas fa-eye" id="pwEye"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Gal Nidaamka
            </button>
        </form>

        <!-- Card Footer -->
        <div class="card-footer-info">
            <?php if ($admin_count === 0): ?>
            <p class="register-callout">
                Ma lihid account? <a href="register.php"><i class="fas fa-user-plus"></i> Samee Account Hadda</a>
            </p>
            <?php endif; ?>

            <div class="security-tag">
                <i class="fas fa-shield-halved"></i> Nidaam Sugan oo la Ilaaliyay &bull; 256-bit Encryption
            </div>

            <div class="system-version">
                NIC System v2.0 &bull; Jamhuuriyadda Federaalka Soomaaliya &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>

    <!-- Interactive Script -->
    <script>
    function togglePw() {
        const input = document.getElementById('password');
        const eye   = document.getElementById('pwEye');
        if (input.type === 'password') {
            input.type = 'text';
            eye.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            eye.className = 'fas fa-eye';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Xaqiijinaya...';
        btn.disabled = true;
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Gal Nidaamka';
            btn.disabled = false;
        }, 6000);
    });
    </script>
</body>
</html>
