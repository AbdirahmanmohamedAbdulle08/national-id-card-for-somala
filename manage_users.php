<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['username'] ?? 'Admin';
$today = date('Y-m-d');

// Stats
$total_records   = (int) $conn->query("SELECT COUNT(*) FROM nic_records")->fetch_row()[0];
$created_today   = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE DATE(date_created) = '$today'")->fetch_row()[0];
$updated_today   = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE DATE(date_updated) = '$today'")->fetch_row()[0];
$male_count      = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE gender = 'Male' OR gender = 'Lab'")->fetch_row()[0];
$female_count    = $total_records - $male_count;

// Progress percentages for today circles
$max_val        = max($total_records, 1);
$created_pct    = min(($created_today  / $max_val) * 100, 100);
$updated_pct    = min(($updated_today  / $max_val) * 100, 100);

// Search / Filter
$search = trim($_GET['q'] ?? '');
$gender_filter = $_GET['gender'] ?? '';

$where_clauses = [];
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where_clauses[] = "(nic_number LIKE '%$s%' OR full_name LIKE '%$s%' OR phone_number LIKE '%$s%' OR address LIKE '%$s%')";
}
if ($gender_filter !== '') {
    $gf = $conn->real_escape_string($gender_filter);
    $where_clauses[] = "gender = '$gf'";
}

$where_sql = count($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Pagination
$per_page    = 10;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($current_page - 1) * $per_page;

$count_result = $conn->query("SELECT COUNT(*) FROM nic_records $where_sql");
$filtered_total = (int) $count_result->fetch_row()[0];
$total_pages  = (int) ceil($filtered_total / $per_page);

$records = $conn->query("SELECT id, nic_number, full_name, gender, dob, phone_number, address, date_created FROM nic_records $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset");

$conn->close();
$active_page = 'manage_users';
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maamul Users - NIC System</title>

    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css?v=<?= time() ?>">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: #f1f5f9;
        }

        .page-container { padding: 0 4px; }

        /* ── Top Bar ─────────────────────────────── */
        .dash-topbar {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 26px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
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

        .pill-date i { color: #0052cc; }

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
            box-shadow: 0 4px 14px rgba(0,82,204,0.3);
            transition: all 0.25s ease;
        }

        .btn-register-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,82,204,0.45);
        }

        /* ── KPI Stats ───────────────────────────── */
        .kpi-grid {
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
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

        .kpi-icon.blue   { background: #eff6ff;   color: #2563eb; }
        .kpi-icon.green  { background: #ecfdf5;   color: #10b981; }
        .kpi-icon.amber  { background: #fffbeb;   color: #f59e0b; }
        .kpi-icon.sky    { background: #f0f9ff;   color: #0284c7; }
        .kpi-icon.purple { background: #faf5ff;   color: #9333ea; }

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

        /* ── Progress / Donut Row ─────────────────── */
        .progress-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .progress-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .donut-wrap {
            position: relative;
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }

        .donut-svg {
            width: 80px;
            height: 80px;
            transform: rotate(-90deg);
        }

        .donut-bg    { fill: none; stroke: #e2e8f0; stroke-width: 9; }
        .donut-ring  { fill: none; stroke-width: 9; stroke-linecap: round; transition: stroke-dashoffset 0.8s ease; }
        .donut-ring.blue   { stroke: #2563eb; }
        .donut-ring.green  { stroke: #10b981; }
        .donut-ring.purple { stroke: #9333ea; }

        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
        }

        .progress-info h4 {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            font-family: 'Outfit', sans-serif;
        }

        .progress-info p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }

        /* ── Main Table Card ─────────────────────── */
        .table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .table-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-card-title i { color: #0052cc; }

        .table-count-badge {
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 50px;
            border: 1px solid #dbeafe;
        }

        /* ── Search & Filter Bar ─────────────────── */
        .search-filter-bar {
            padding: 16px 24px;
            background: #fafbfc;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-input-wrap {
            position: relative;
            flex-grow: 1;
            min-width: 220px;
        }

        .search-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: #0052cc;
            box-shadow: 0 0 0 3px rgba(0,82,204,0.1);
        }

        .filter-select {
            padding: 10px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .filter-select:focus {
            border-color: #0052cc;
        }

        .btn-search {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0052cc;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-search:hover { background: #003d99; }

        .btn-clear {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-clear:hover {
            border-color: #ef4444;
            color: #ef4444;
        }

        /* ── Table ───────────────────────────────── */
        .mu-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mu-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .mu-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
            font-size: 14px;
        }

        .mu-table tbody tr:hover { background-color: #f8fbff; }
        .mu-table tbody tr:last-child td { border-bottom: none; }

        .nic-badge {
            font-family: 'JetBrains Mono', monospace;
            color: #0052cc;
            font-weight: 700;
            font-size: 12.5px;
            background: #eff6ff;
            padding: 5px 10px;
            border-radius: 7px;
            border: 1px solid #dbeafe;
            display: inline-block;
            white-space: nowrap;
        }

        .gender-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 50px;
        }

        .gender-pill.male   { background: #eff6ff; color: #1d4ed8; }
        .gender-pill.female { background: #fdf2f8; color: #db2777; }

        .name-cell { font-weight: 700; color: #0f172a; }

        .meta-text { font-size: 12.5px; color: #64748b; }

        .action-btns { display: flex; gap: 6px; }

        .act-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .act-btn.view   { background: #eff6ff; color: #2563eb; }
        .act-btn.edit   { background: #fffbeb; color: #d97706; }
        .act-btn.print  { background: #ecfdf5; color: #059669; }
        .act-btn.delete { background: #fef2f2; color: #dc2626; }

        .act-btn:hover { filter: brightness(0.9); transform: scale(1.1); }

        /* ── Pagination ──────────────────────────── */
        .pagination-bar {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-info { font-size: 13px; color: #64748b; font-weight: 500; }

        .page-btns { display: flex; gap: 6px; }

        .page-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            background: #ffffff;
            transition: all 0.2s;
        }

        .page-btn:hover { border-color: #0052cc; color: #0052cc; }

        .page-btn.active {
            background: #0052cc;
            border-color: #0052cc;
            color: #ffffff;
        }

        .page-btn.disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* ── Empty State ─────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .empty-state small {
            font-size: 13px;
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(3, 1fr); }
            .progress-row { grid-template-columns: 1fr; }
        }

        @media (max-width: 900px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .dash-topbar { flex-direction: column; align-items: flex-start; }
            .mu-table th:nth-child(5),
            .mu-table td:nth-child(5),
            .mu-table th:nth-child(6),
            .mu-table td:nth-child(6) { display: none; }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content" id="mainContent">
<div class="page-container">

    <!-- ── Top Bar ──────────────────────────────── -->
    <div class="dash-topbar">
        <div class="dash-title-group">
            <h1><i class="fas fa-users" style="color:#0052cc;"></i> Maamul Users-ka Diiwaanka</h1>
            <p>Diiwaanka aqoonsiga muwaadiniinta Jamhuuriyadda Federaalka Soomaaliya</p>
        </div>
        <div class="dash-top-actions">
            <div class="pill-date">
                <i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?>
            </div>
            <a href="Update.php" class="btn-register-action">
                <i class="fas fa-user-plus"></i> Diiwaangeli Cusub
            </a>
        </div>
    </div>

    <!-- ── KPI Stats (5-col) ──────────────────── -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-database"></i></div>
            <div class="kpi-info">
                <h4>Wadarta Diiwaanka</h4>
                <p><?= number_format($total_records) ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-user-plus"></i></div>
            <div class="kpi-info">
                <h4>La Sameyey Maanta</h4>
                <p><?= number_format($created_today) ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon amber"><i class="fas fa-user-edit"></i></div>
            <div class="kpi-info">
                <h4>La Cusboonaysiiyey Maanta</h4>
                <p><?= number_format($updated_today) ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon sky"><i class="fas fa-mars"></i></div>
            <div class="kpi-info">
                <h4>Ragga (Lab)</h4>
                <p><?= number_format($male_count) ?></p>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon purple"><i class="fas fa-venus"></i></div>
            <div class="kpi-info">
                <h4>Dumarka (Dhedig)</h4>
                <p><?= number_format($female_count) ?></p>
            </div>
        </div>
    </div>

    <!-- ── Progress Donut Cards ───────────────── -->
    <div class="progress-row">
        <?php
        $circumference = 2 * M_PI * 30; // radius = 30
        $cPct = round($created_pct);
        $uPct = round($updated_pct);
        $gPct = $total_records > 0 ? round(($male_count / $total_records) * 100) : 0;
        $cOffset = $circumference * (1 - $cPct / 100);
        $uOffset = $circumference * (1 - $uPct / 100);
        $gOffset = $circumference * (1 - $gPct / 100);
        ?>

        <div class="progress-card">
            <div class="donut-wrap">
                <svg class="donut-svg" viewBox="0 0 70 70">
                    <circle class="donut-bg"   cx="35" cy="35" r="30"/>
                    <circle class="donut-ring blue" cx="35" cy="35" r="30"
                        stroke-dasharray="<?= $circumference ?>"
                        stroke-dashoffset="<?= $cOffset ?>"/>
                </svg>
                <div class="donut-center"><?= $cPct ?>%</div>
            </div>
            <div class="progress-info">
                <h4><?= number_format($created_today) ?> La Sameyey Maanta</h4>
                <p>Marka loo eego wadarta diiwaanka oo dhan (<?= number_format($total_records) ?> record)</p>
            </div>
        </div>

        <div class="progress-card">
            <div class="donut-wrap">
                <svg class="donut-svg" viewBox="0 0 70 70">
                    <circle class="donut-bg"   cx="35" cy="35" r="30"/>
                    <circle class="donut-ring green" cx="35" cy="35" r="30"
                        stroke-dasharray="<?= $circumference ?>"
                        stroke-dashoffset="<?= $uOffset ?>"/>
                </svg>
                <div class="donut-center"><?= $uPct ?>%</div>
            </div>
            <div class="progress-info">
                <h4><?= number_format($updated_today) ?> La Cusboonaysiiyey</h4>
                <p>Diiwaanada la saxay maanta marka loo eego dhammaanba.</p>
            </div>
        </div>

        <div class="progress-card">
            <div class="donut-wrap">
                <svg class="donut-svg" viewBox="0 0 70 70">
                    <circle class="donut-bg"   cx="35" cy="35" r="30"/>
                    <circle class="donut-ring purple" cx="35" cy="35" r="30"
                        stroke-dasharray="<?= $circumference ?>"
                        stroke-dashoffset="<?= $gOffset ?>"/>
                </svg>
                <div class="donut-center"><?= $gPct ?>%</div>
            </div>
            <div class="progress-info">
                <h4>Ragga <?= $gPct ?>% Guud</h4>
                <p><?= number_format($male_count) ?> Lab &bull; <?= number_format($female_count) ?> Dhedig ka mid ah <?= number_format($total_records) ?> muwaadin</p>
            </div>
        </div>
    </div>

    <!-- ── Records Table Card ─────────────────── -->
    <div class="table-card">

        <!-- Header -->
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-table-list"></i>
                Diiwaanka Aqoonsiga Muwaadiniinta
                <span class="table-count-badge"><?= number_format($filtered_total) ?> Record</span>
            </div>
            <a href="generate_nationalid.php" style="color:#0052cc; font-size:13px; font-weight:700; text-decoration:none;">
                <i class="fas fa-print"></i> Daabac Kaararka
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="search-filter-bar">
            <form method="GET" style="display:contents;">
                <div class="search-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" class="search-input" placeholder="Raadi NIC #, Magac, Telefoon, Cinwaanka..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select name="gender" class="filter-select">
                    <option value="">Jinsiga oo dhan</option>
                    <option value="Male" <?= $gender_filter === 'Male' ? 'selected' : '' ?>>Lab (Male)</option>
                    <option value="Female" <?= $gender_filter === 'Female' ? 'selected' : '' ?>>Dhedig (Female)</option>
                </select>
                <button type="submit" class="btn-search">
                    <i class="fas fa-magnifying-glass"></i> Raadi
                </button>
                <?php if ($search !== '' || $gender_filter !== ''): ?>
                <a href="manage_users.php" class="btn-clear">
                    <i class="fas fa-xmark"></i> Nadiifi
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table class="mu-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIC Number</th>
                        <th>Magaca Buuxa</th>
                        <th>Jinsiga</th>
                        <th>Taariikhda Dhalashada</th>
                        <th>Telefoon</th>
                        <th>Taariikhda La Diiwaangeliyay</th>
                        <th style="text-align:center;">Ficilada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records && $records->num_rows > 0): ?>
                        <?php $row_num = $offset + 1; while ($row = $records->fetch_assoc()): ?>
                        <tr>
                            <td><span class="meta-text"><?= $row_num++ ?></span></td>
                            <td><span class="nic-badge"><?= htmlspecialchars($row['nic_number']) ?></span></td>
                            <td class="name-cell"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td>
                                <?php $is_male = in_array(strtolower($row['gender']), ['male', 'lab']); ?>
                                <span class="gender-pill <?= $is_male ? 'male' : 'female' ?>">
                                    <i class="fas <?= $is_male ? 'fa-mars' : 'fa-venus' ?>"></i>
                                    <?= $is_male ? 'Lab (M)' : 'Dhedig (F)' ?>
                                </span>
                            </td>
                            <td><span class="meta-text"><?= $row['dob'] ? date('d M Y', strtotime($row['dob'])) : '—' ?></span></td>
                            <td><span class="meta-text"><?= $row['phone_number'] ? htmlspecialchars($row['phone_number']) : '—' ?></span></td>
                            <td><span class="meta-text"><?= date('d M Y', strtotime($row['date_created'])) ?></span></td>
                            <td>
                                <div class="action-btns" style="justify-content:center;">
                                    <a href="Update.php?id=<?= $row['id'] ?>" class="act-btn edit" title="Wax ka Bedel">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <a href="generate_nationalid.php?id=<?= $row['id'] ?>" class="act-btn print" title="Daabac Kaarka">
                                        <i class="fas fa-id-card"></i>
                                    </a>
                                    <a href="Delete.php?id=<?= $row['id'] ?>" class="act-btn delete" title="Tirtir"
                                       onclick="return confirm('Ma hubtaa inaad tirto diiwaankan?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    <p><?= $search ? "Natiijo lama helin «$search»" : 'Wali xog muwaadinin lama diiwaangelin' ?></p>
                                    <small><?= $search ? 'Isku day erayo kale.' : 'Diiwaangeli muwaadiniin cusub si xogtu u soo muuqato.' ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-bar">
            <div class="page-info">
                Muujinaya <?= number_format($offset + 1) ?>–<?= number_format(min($offset + $per_page, $filtered_total)) ?> ka mid ah <?= number_format($filtered_total) ?> record
            </div>
            <div class="page-btns">
                <?php
                $q_str = http_build_query(array_filter(['q' => $search, 'gender' => $gender_filter]));
                $q_pfx = $q_str ? $q_str . '&' : '';
                ?>
                <a href="?<?= $q_pfx ?>page=<?= max(1, $current_page - 1) ?>"
                   class="page-btn <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page   = min($total_pages, $current_page + 2);
                if ($start_page > 1) echo '<span style="padding:0 6px;color:#94a3b8;align-self:center;">…</span>';
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                <a href="?<?= $q_pfx ?>page=<?= $i ?>"
                   class="page-btn <?= $i === $current_page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                <?php if ($end_page < $total_pages) echo '<span style="padding:0 6px;color:#94a3b8;align-self:center;">…</span>'; ?>
                <a href="?<?= $q_pfx ?>page=<?= min($total_pages, $current_page + 1) ?>"
                   class="page-btn <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /.table-card -->

</div><!-- /.page-container -->
</div><!-- /.main-content -->

</body>
</html>
