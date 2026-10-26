<?php
// Include database connection
include('db.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';

// Fetch stats
$total_users_sql = "SELECT COUNT(*) AS total_users FROM nic_records";
$result = $conn->query($total_users_sql);
$total_users = ($result) ? $result->fetch_assoc()['total_users'] : 0;

$active_users_sql = "SELECT COUNT(*) AS active_users FROM nic_records WHERE date_created >= CURDATE() - INTERVAL 30 DAY";
$result = $conn->query($active_users_sql);
$active_users = ($result) ? $result->fetch_assoc()['active_users'] : 0;

$pending_approvals_sql = "SELECT COUNT(*) AS pending_approvals FROM nic_records WHERE photo_path IS NULL OR photo_path = ''";
$result = $conn->query($pending_approvals_sql);
$pending_approvals = ($result) ? $result->fetch_assoc()['pending_approvals'] : 0;

$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) AS cnt FROM nic_records WHERE DATE(date_created) = '$today'");
$created_today = ($result) ? $result->fetch_assoc()['cnt'] : 0;

$result = $conn->query("SELECT COUNT(*) AS cnt FROM nic_records WHERE DATE(date_updated) = '$today'");
$updated_today = ($result) ? $result->fetch_assoc()['cnt'] : 0;

// Recent records
$recent_records = $conn->query("SELECT id, nic_number, full_name, gender, date_created FROM nic_records ORDER BY id DESC LIMIT 6");

$conn->close();
$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nidaamka Kaarka Aqoonsiga Qaranka</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Sidebar & Layout Styles (Cache Buster) -->
    <link rel="stylesheet" href="sidebar.css?v=<?= time() ?>">
    
    <style>
        /* Modern Dashboard Specific Overrides & Beautification */
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: #f1f5f9;
        }

        .dashboard-container {
            padding: 0 4px;
        }

        /* Top Bar Styling */
        .dash-topbar {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 26px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dash-title-group h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .dash-title-group p {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .dash-top-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .pill-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .pill-date i {
            color: #0052cc;
        }

        .pill-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
        }

        .pulse-circle {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseGreen 2s infinite;
        }

        @keyframes pulseGreen {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .btn-register-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #0052cc 0%, #1e40af 100%);
            color: #ffffff !important;
            padding: 10px 22px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(0, 82, 204, 0.3);
            transition: all 0.25s ease;
        }

        .btn-register-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 82, 204, 0.45);
        }

        /* 5 Stats KPI Grid */
        .dash-stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }

        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .kpi-icon.blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .kpi-icon.green {
            background: #ecfdf5;
            color: #10b981;
        }

        .kpi-icon.amber {
            background: #fffbeb;
            color: #f59e0b;
        }

        .kpi-icon.sky {
            background: #f0f9ff;
            color: #0284c7;
        }

        .kpi-icon.purple {
            background: #faf5ff;
            color: #9333ea;
        }

        .kpi-info h4 {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .kpi-info p {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        /* 2-Column Content Row */
        .dash-content-grid {
            display: grid;
            grid-template-columns: 1.9fr 1.1fr;
            gap: 22px;
            margin-bottom: 24px;
        }

        .dash-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .dash-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dash-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dash-card-title i {
            color: #0052cc;
        }

        .link-view-all {
            color: #0052cc;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .link-view-all:hover {
            color: #003399;
            text-decoration: underline;
        }

        /* Recent Records Table */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .dash-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .dash-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
        }

        .dash-table tbody tr:hover {
            background-color: #f8fbff;
        }

        .dash-table tbody tr:last-child td {
            border-bottom: none;
        }

        .nic-badge-mono {
            font-family: 'JetBrains Mono', monospace;
            color: #0052cc;
            font-weight: 700;
            background: #eff6ff;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #dbeafe;
            display: inline-block;
        }

        .gender-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50px;
        }

        .gender-pill.male {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .gender-pill.female {
            background: #fdf2f8;
            color: #db2777;
        }

        .action-row-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            transition: all 0.2s;
        }

        .action-row-btn:hover {
            background: #0052cc;
            color: #ffffff;
        }

        /* Quick Action Grid in Right Column */
        .quick-action-list {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .q-action-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.25s ease;
        }

        .q-action-card:hover {
            background: #ffffff;
            border-color: #0052cc;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.12);
            color: #0052cc;
        }

        .q-action-left {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 14px;
        }

        .q-action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            background: #eff6ff;
            color: #0052cc;
        }

        .q-action-card:hover .q-action-icon {
            background: #0052cc;
            color: #ffffff;
        }

        /* Overview / Republic Tag Box */
        .system-info-box {
            margin: 0 20px 20px;
            background: linear-gradient(135deg, #09132b 0%, #1e3a8a 100%);
            border-radius: 14px;
            padding: 18px 20px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .system-info-box img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
            object-fit: cover;
        }

        .system-info-box h5 {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 2px;
            color: #ffffff;
        }

        .system-info-box p {
            font-size: 11px;
            color: #93c5fd;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dash-stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .dash-content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dash-stats-grid {
                grid-template-columns: 1fr;
            }
            .dash-topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<!-- Main Content Area -->
<div class="main-content" id="mainContent">
    <div class="dashboard-container">

        <!-- Top Bar -->
        <div class="dash-topbar">
            <div class="dash-title-group">
                <h1><i class="fas fa-chart-line" style="color:#0052cc;"></i> Dashboard-ka Maamulka</h1>
                <p>Nidaamka Rasmiga ah ee Kaarka Aqoonsiga Qaranka Jamhuuriyadda Federaalka Soomaaliya</p>
            </div>
            <div class="dash-top-actions">
                <div class="pill-date">
                    <i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?>
                </div>
                <div class="pill-live">
                    <span class="pulse-circle"></span> Live &amp; Online
                </div>
                <a href="Update.php" class="btn-register-action">
                    <i class="fas fa-user-plus"></i> Diiwaangeli Cusub
                </a>
            </div>
        </div>

        <!-- 5 Stats Cards Grid -->
        <div class="dash-stats-grid">
            <!-- 1. Total Users -->
            <div class="kpi-card">
                <div class="kpi-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-info">
                    <h4>Wadarta Guud</h4>
                    <p><?= number_format($total_users) ?></p>
                </div>
            </div>

            <!-- 2. Active in 30 days -->
            <div class="kpi-card">
                <div class="kpi-icon green">
                    <i class="fas fa-id-badge"></i>
                </div>
                <div class="kpi-info">
                    <h4>Active (30 Cisho)</h4>
                    <p><?= number_format($active_users) ?></p>
                </div>
            </div>

            <!-- 3. Pending Photos / Approvals -->
            <div class="kpi-card">
                <div class="kpi-icon amber">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="kpi-info">
                    <h4>Sugaya Sawir</h4>
                    <p><?= number_format($pending_approvals) ?></p>
                </div>
            </div>

            <!-- 4. Today Created -->
            <div class="kpi-card">
                <div class="kpi-icon sky">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="kpi-info">
                    <h4>Maanta Cusub</h4>
                    <p><?= number_format($created_today) ?></p>
                </div>
            </div>

            <!-- 5. Today Updated -->
            <div class="kpi-card">
                <div class="kpi-icon purple">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="kpi-info">
                    <h4>Maanta La Saxay</h4>
                    <p><?= number_format($updated_today) ?></p>
                </div>
            </div>
        </div>

        <!-- 2-Column Content Layout -->
        <div class="dash-content-grid">

            <!-- Left: Recent Registrations Table -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <i class="fas fa-clock-rotate-left"></i> Diiwaanada Ugu Dambeeyay ee Qaranka
                    </div>
                    <a href="manage_users.php" class="link-view-all">
                        Dhammaan Arag <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div style="overflow-x: auto;">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>NIC Number</th>
                                <th>Magaca Muwaadinka</th>
                                <th>Jinsiga</th>
                                <th>Taariikhda</th>
                                <th style="text-align:center;">Ficil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_records && $recent_records->num_rows > 0): ?>
                                <?php while ($row = $recent_records->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="nic-badge-mono"><?= htmlspecialchars($row['nic_number']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (strtolower($row['gender']) === 'male' || strtolower($row['gender']) === 'lab'): ?>
                                            <span class="gender-pill male"><i class="fas fa-mars"></i> Lab (M)</span>
                                        <?php else: ?>
                                            <span class="gender-pill female"><i class="fas fa-venus"></i> Dhedig (F)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color:#64748b; font-size:13px;"><?= date('d M Y', strtotime($row['date_created'])) ?></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <a href="generate_nationalid.php" class="action-row-btn" title="Samee / Daabac Kaarka">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">
                                        <i class="fas fa-folder-open" style="font-size:32px; margin-bottom: 8px; display:block;"></i>
                                        Weli ma jiraan xog muwaadiniin oo la diiwaangeliyay.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Quick Actions & System Info -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <i class="fas fa-bolt" style="color:#f59e0b;"></i> Ficilada Degdegga ah
                    </div>
                </div>

                <div class="quick-action-list">
                    <a href="Update.php" class="q-action-card">
                        <div class="q-action-left">
                            <div class="q-action-icon"><i class="fas fa-user-plus"></i></div>
                            <span>Diiwaangeli Muwaadin Cusub</span>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:12px;"></i>
                    </a>

                    <a href="manage_users.php" class="q-action-card">
                        <div class="q-action-left">
                            <div class="q-action-icon"><i class="fas fa-users-gear"></i></div>
                            <span>Maamul &amp; Raadi Xogta</span>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:12px;"></i>
                    </a>

                    <a href="generate_nationalid.php" class="q-action-card">
                        <div class="q-action-left">
                            <div class="q-action-icon"><i class="fas fa-id-card"></i></div>
                            <span>Daabac Kaarka Aqoonsiga</span>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:12px;"></i>
                    </a>

                    <a href="Delete.php" class="q-action-card">
                        <div class="q-action-left">
                            <div class="q-action-icon" style="background:#fef2f2; color:#ef4444;"><i class="fas fa-trash-alt"></i></div>
                            <span>Tirtir Diiwaanada</span>
                        </div>
                        <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:12px;"></i>
                    </a>

                    
                </div>

                <!-- National System Badge Box -->
                <div class="system-info-box">
                    <img src="logo.png" alt="Somalia National ID">
                    <div>
                        <h5>Jamhuuriyadda Federaalka</h5>
                        <p>Hey'adda Aqoonsiga Qaranka (NIC) &bull; Nidaamku wuxuu ku xiran yahay keydka xogta qaran.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>
