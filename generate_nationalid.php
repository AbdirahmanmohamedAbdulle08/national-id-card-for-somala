<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$today         = date('Y-m-d');
$total_records = (int) $conn->query("SELECT COUNT(*) FROM nic_records")->fetch_row()[0];
$today_count   = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE DATE(date_created)='$today'")->fetch_row()[0];
$male_count    = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE gender='Male'")->fetch_row()[0];
$female_count  = $total_records - $male_count;

$records     = $conn->query("SELECT * FROM nic_records ORDER BY id DESC");
$active_page = 'generate_nic';
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samee NIC Card – Aqoonsiga Qaranka</title>
    <meta name="description" content="Samee oo daabac kaararka aqoonsiga qaranka (NIC) muwaadiniinta.">

    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="sidebar.css?v=<?= time() ?>">

<style>
/* ── Base ─────────────────────────────────────────────── */
body { font-family:'Plus Jakarta Sans',-apple-system,sans-serif; background:#f1f5f9; }
.page-wrap { padding:0 4px; }

/* ── Top Bar ──────────────────────────────────────────── */
.dash-topbar {
    background:#fff; border-radius:16px; padding:20px 26px;
    margin-bottom:24px; display:flex; justify-content:space-between;
    align-items:center; box-shadow:0 2px 10px rgba(0,0,0,.04);
    border:1px solid #e2e8f0; flex-wrap:wrap; gap:16px;
}
.dash-title-group h1 {
    font-family:'Outfit',sans-serif; font-size:24px; font-weight:800;
    color:#0f172a; display:flex; align-items:center; gap:10px; margin-bottom:4px;
}
.dash-title-group h1 i { color:#0052cc; }
.dash-title-group p { font-size:13px; color:#64748b; font-weight:500; }
.dash-top-actions { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.pill-date {
    display:inline-flex; align-items:center; gap:8px; background:#f8fafc;
    border:1px solid #e2e8f0; padding:8px 16px; border-radius:50px;
    font-size:13px; font-weight:600; color:#475569;
}
.pill-date i { color:#0052cc; }
.btn-new-rec {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#0052cc,#1e40af); color:#fff !important;
    padding:10px 20px; border-radius:12px; font-size:14px; font-weight:700;
    text-decoration:none; box-shadow:0 4px 14px rgba(0,82,204,.3);
    transition:all .2s;
}
.btn-new-rec:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,82,204,.45); }

/* ── Info Banner ──────────────────────────────────────── */
.id-banner {
    background:linear-gradient(135deg,#09143c 0%,#1e40af 100%);
    border-radius:16px; padding:22px 28px; margin-bottom:24px;
    display:flex; justify-content:space-between; align-items:center;
    flex-wrap:wrap; gap:16px; box-shadow:0 8px 28px rgba(9,20,60,.2);
}
.id-banner-left { display:flex; align-items:center; gap:16px; }
.id-banner-icon {
    width:52px; height:52px; border-radius:14px;
    background:rgba(255,255,255,.15); display:flex; align-items:center;
    justify-content:center; font-size:24px; color:#fff; flex-shrink:0;
}
.id-banner-text h3 { font-family:'Outfit',sans-serif; font-size:17px; font-weight:800; color:#fff; margin-bottom:3px; }
.id-banner-text p  { font-size:13px; color:rgba(255,255,255,.7); }
.id-banner-pills { display:flex; gap:12px; flex-wrap:wrap; }
.id-banner-pill {
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
    padding:8px 18px; border-radius:50px; font-size:13px; font-weight:700;
    color:#fff; display:flex; align-items:center; gap:7px; white-space:nowrap;
}
.id-banner-pill span { font-family:'Outfit',sans-serif; font-size:18px; color:#93c5fd; }

/* ── KPI Grid ─────────────────────────────────────────── */
.kpi-grid {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:18px; margin-bottom:24px;
}
.kpi-card {
    background:#fff; border-radius:16px; padding:22px 20px;
    border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,.03);
    display:flex; align-items:center; gap:16px;
    transition:transform .25s ease, box-shadow .25s ease;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.07); }
.kpi-icon {
    width:50px; height:50px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.kpi-icon.blue   { background:#eff6ff; color:#2563eb; }
.kpi-icon.green  { background:#ecfdf5; color:#10b981; }
.kpi-icon.sky    { background:#f0f9ff; color:#0284c7; }
.kpi-icon.pink   { background:#fdf2f8; color:#db2777; }
.kpi-info h4 { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
.kpi-info p  { font-family:'Outfit',sans-serif; font-size:26px; font-weight:800; color:#0f172a; line-height:1.1; }

/* ── Table Card ───────────────────────────────────────── */
.tbl-card {
    background:#fff; border-radius:16px; border:1px solid #e2e8f0;
    box-shadow:0 2px 10px rgba(0,0,0,.04); overflow:hidden;
}
.tbl-card-hdr {
    padding:18px 24px; border-bottom:1px solid #f1f5f9;
    display:flex; justify-content:space-between; align-items:center;
    flex-wrap:wrap; gap:12px;
}
.tbl-card-title {
    font-family:'Outfit',sans-serif; font-size:17px; font-weight:800;
    color:#0f172a; display:flex; align-items:center; gap:10px;
}
.tbl-card-title i { color:#0052cc; }
.rec-badge {
    background:#eff6ff; color:#1d4ed8; font-size:12px;
    font-weight:700; padding:4px 12px; border-radius:50px; border:1px solid #dbeafe;
}

/* ── DataTable ────────────────────────────────────────── */
.dt-wrap { padding:0 24px 24px; }
.dt-wrap .dataTables_wrapper .dataTables_filter input {
    border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 14px;
    font-family:inherit; font-size:13px; outline:none; transition:border-color .2s;
}
.dt-wrap .dataTables_wrapper .dataTables_filter input:focus { border-color:#0052cc; }
.dt-wrap .dataTables_wrapper .dataTables_length select {
    border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 10px;
    font-family:inherit; font-size:13px; cursor:pointer;
}
table.dataTable thead th {
    background:#f8fafc; color:#475569; font-weight:700; font-size:12px;
    text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #e2e8f0 !important;
    white-space:nowrap; padding:14px 16px !important;
}
table.dataTable tbody td {
    padding:13px 16px !important; border-bottom:1px solid #f1f5f9 !important;
    font-size:14px; vertical-align:middle; color:#1e293b;
}
table.dataTable tbody tr:hover td { background:#f8fbff; }
.nic-chip {
    font-family:'JetBrains Mono',monospace; color:#0052cc; font-weight:700;
    font-size:12px; background:#eff6ff; padding:5px 10px;
    border-radius:7px; border:1px solid #dbeafe; white-space:nowrap; display:inline-block;
}
.gpill { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:700; padding:4px 10px; border-radius:50px; }
.gpill.m { background:#eff6ff; color:#1d4ed8; }
.gpill.f { background:#fdf2f8; color:#db2777; }
.tbl-avatar {
    width:38px; height:38px; border-radius:50%; object-fit:cover;
    border:2px solid #e2e8f0; cursor:pointer; transition:transform .2s;
}
.tbl-avatar:hover { transform:scale(1.15); }
.tbl-avatar-ph {
    width:38px; height:38px; border-radius:50%; background:#f1f5f9;
    display:flex; align-items:center; justify-content:center;
    color:#94a3b8; font-size:15px; border:2px solid #e2e8f0;
}
.btn-gen {
    display:inline-flex; align-items:center; gap:7px;
    background:linear-gradient(135deg,#0052cc,#1e40af); color:#fff;
    border:none; padding:9px 16px; border-radius:10px; font-size:13px;
    font-weight:700; font-family:inherit; cursor:pointer;
    box-shadow:0 3px 10px rgba(0,82,204,.25);
    transition:all .2s ease;
}
.btn-gen:hover { transform:translateY(-2px); box-shadow:0 5px 16px rgba(0,82,204,.4); }
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding:4px 10px; border-radius:7px; border:1px solid #e2e8f0;
    font-size:13px; cursor:pointer; margin:0 2px;
}
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background:#0052cc !important; color:#fff !important; border-color:#0052cc !important;
}
.dt-wrap .dataTables_wrapper .dataTables_info { font-size:13px; color:#64748b; padding-top:12px; }

/* ════════════════════════════════════════════
   CARD PREVIEW MODAL
════════════════════════════════════════════ */
.card-modal-overlay {
    position:fixed; inset:0; z-index:9500;
    background:rgba(7,15,35,0); backdrop-filter:blur(0px);
    display:flex; align-items:center; justify-content:center;
    padding:20px; pointer-events:none;
    transition:background .35s ease, backdrop-filter .35s ease;
}
.card-modal-overlay.active {
    background:rgba(7,15,35,.78); backdrop-filter:blur(8px);
    pointer-events:all;
}
.card-modal {
    background:#fff; border-radius:22px; width:100%; max-width:800px;
    max-height:94vh; overflow:hidden; display:flex; flex-direction:column;
    box-shadow:0 32px 80px rgba(0,0,0,.28), 0 0 0 1px rgba(255,255,255,.08);
    transform:translateY(50px) scale(.94); opacity:0;
    transition:transform .4s cubic-bezier(.34,1.56,.64,1), opacity .3s ease;
}
.card-modal-overlay.active .card-modal { transform:translateY(0) scale(1); opacity:1; }

/* Modal header */
.cm-header {
    padding:20px 26px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between;
    background:linear-gradient(135deg,#09143c,#1e40af);
    flex-shrink:0;
}
.cm-hdr-left { display:flex; align-items:center; gap:14px; }
.cm-hdr-icon {
    width:44px; height:44px; border-radius:12px;
    background:rgba(255,255,255,.15); display:flex;
    align-items:center; justify-content:center; font-size:20px; color:#fff;
}
.cm-hdr-text h3 { font-family:'Outfit',sans-serif; font-size:17px; font-weight:800; color:#fff; margin-bottom:2px; }
.cm-hdr-text p  { font-size:12px; color:rgba(255,255,255,.6); }
.cm-close {
    width:36px; height:36px; background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.2); border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#fff; font-size:15px; transition:all .2s;
}
.cm-close:hover { background:rgba(239,68,68,.35); border-color:rgba(239,68,68,.5); }

/* Tabs inside modal */
.cm-tabs {
    display:flex; background:#f8fafc; border-bottom:1px solid #e2e8f0;
    flex-shrink:0;
}
.cm-tab {
    flex:1; padding:14px; font-size:13px; font-weight:700; color:#64748b;
    border:none; background:none; cursor:pointer; display:flex;
    align-items:center; justify-content:center; gap:8px;
    border-bottom:3px solid transparent; transition:all .2s; font-family:inherit;
}
.cm-tab.active { color:#0052cc; border-bottom-color:#0052cc; background:#fff; }
.cm-tab:hover:not(.active) { color:#334155; }

/* Card display area */
.cm-body { overflow-y:auto; padding:28px; flex:1; display:flex; flex-direction:column; align-items:center; }
.cm-body::-webkit-scrollbar { width:5px; }
.cm-body::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }

.card-panel { display:none; width:100%; flex-direction:column; align-items:center; gap:16px; }
.card-panel.active { display:flex; animation:fadeIn .3s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

.nic-card-wrap {
    width:100%; max-width:640px;
    border-radius:14px; overflow:hidden;
    box-shadow:0 12px 40px rgba(0,0,0,.18), 0 0 0 1px rgba(0,0,0,.06);
    transition:transform .3s ease, box-shadow .3s ease;
    position:relative;
}
.nic-card-wrap:hover {
    transform:perspective(800px) rotateY(2deg) scale(1.01);
    box-shadow:0 20px 60px rgba(0,0,0,.22);
}
.nic-card-wrap img { width:100%; height:auto; display:block; }

/* Shimmer loading */
.card-skeleton {
    width:100%; max-width:640px; aspect-ratio:85.6/54;
    border-radius:14px; background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size:200% 100%;
    animation:shimmer 1.4s infinite;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.card-caption {
    font-size:13px; color:#64748b; font-weight:600;
    display:flex; align-items:center; gap:8px;
}
.card-caption i { color:#0052cc; }

/* Footer */
.cm-footer {
    padding:16px 26px; border-top:1px solid #f1f5f9;
    display:flex; justify-content:space-between; align-items:center;
    gap:12px; flex-wrap:wrap; flex-shrink:0; background:#fafbfc;
}
.cm-info-pill {
    display:flex; align-items:center; gap:8px;
    font-size:13px; font-weight:600; color:#475569;
}
.cm-info-pill code {
    font-family:'JetBrains Mono',monospace; font-size:12px;
    background:#eff6ff; color:#1d4ed8; padding:3px 8px;
    border-radius:6px; border:1px solid #dbeafe;
}
.cm-footer-actions { display:flex; gap:10px; }
.btn-print-front {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#0052cc,#1e40af); color:#fff;
    border:none; padding:10px 20px; border-radius:10px; font-size:14px;
    font-weight:700; text-decoration:none; font-family:inherit; cursor:pointer;
    box-shadow:0 4px 14px rgba(0,82,204,.28); transition:all .25s;
}
.btn-print-front:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,82,204,.4); }
.btn-print-back {
    display:inline-flex; align-items:center; gap:8px;
    background:#f8fafc; color:#475569; border:1.5px solid #e2e8f0;
    padding:10px 20px; border-radius:10px; font-size:14px;
    font-weight:700; text-decoration:none; font-family:inherit; cursor:pointer;
    transition:all .2s;
}
.btn-print-back:hover { border-color:#0052cc; color:#0052cc; }

/* ── Responsive ───────────────────────────────────────── */
@media (max-width:1000px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:600px) {
    .kpi-grid { grid-template-columns:1fr; }
    .cm-footer { flex-direction:column; }
    .cm-footer-actions { width:100%; }
    .btn-print-front, .btn-print-back { flex:1; justify-content:center; }
}
</style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content" id="mainContent">
<div class="page-wrap">

    <!-- ── Top Bar ──────────────────────────────────────── -->
    <div class="dash-topbar">
        <div class="dash-title-group">
            <h1><i class="fas fa-id-badge"></i> Samee Kaararka NIC</h1>
            <p>Daabac wajiga hore iyo dhabarka kaararka aqoonsiga qaranka</p>
        </div>
        <div class="dash-top-actions">
            <div class="pill-date"><i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?></div>
            <a href="Update.php" class="btn-new-rec">
                <i class="fas fa-user-plus"></i> Diiwaangeli Cusub
            </a>
        </div>
    </div>

    <!-- ── Info Banner ──────────────────────────────────── -->
    <div class="id-banner">
        <div class="id-banner-left">
            <div class="id-banner-icon"><i class="fas fa-id-card"></i></div>
            <div class="id-banner-text">
                <h3>Daabacaadda Kaararka Aqoonsiga Qaranka</h3>
                <p>Guji "Samee Kaarka" si aad u aragto preview-ga — ka dibna daabac wajiga hore ama dhabarka.</p>
            </div>
        </div>
        <div class="id-banner-pills">
            <div class="id-banner-pill">
                <i class="fas fa-database"></i> Wadarta: <span><?= number_format($total_records) ?></span>
            </div>
            <div class="id-banner-pill">
                <i class="fas fa-calendar-check"></i> Maanta: <span><?= number_format($today_count) ?></span>
            </div>
        </div>
    </div>

    <!-- ── KPI Cards ────────────────────────────────────── -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-id-card"></i></div>
            <div class="kpi-info"><h4>Wadarta Diiwaanka</h4><p><?= number_format($total_records) ?></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-user-plus"></i></div>
            <div class="kpi-info"><h4>La Sameyey Maanta</h4><p><?= number_format($today_count) ?></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon sky"><i class="fas fa-mars"></i></div>
            <div class="kpi-info"><h4>Ragga (Lab)</h4><p><?= number_format($male_count) ?></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon pink"><i class="fas fa-venus"></i></div>
            <div class="kpi-info"><h4>Dumarka (Dhedig)</h4><p><?= number_format($female_count) ?></p></div>
        </div>
    </div>

    <!-- ── Table Card ───────────────────────────────────── -->
    <div class="tbl-card">
        <div class="tbl-card-hdr">
            <div class="tbl-card-title">
                <i class="fas fa-address-card"></i>
                Dhammaan Xogaha Kaarka Loo Sameyn Karo
                <span class="rec-badge"><?= number_format($total_records) ?> Kaar</span>
            </div>
        </div>

        <div class="dt-wrap">
            <table id="nicTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIC Number</th>
                        <th>Magaca Buuxa</th>
                        <th>Jinsiga</th>
                        <th>Telefoon</th>
                        <th>Sawir</th>
                        <th>Taariikhda</th>
                        <th style="text-align:center;">Samee Kaarka</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records && $records->num_rows > 0): ?>
                    <?php while ($row = $records->fetch_assoc()): ?>
                    <tr>
                        <td><span style="font-size:12px;color:#94a3b8;font-weight:700;">#<?= $row['id'] ?></span></td>
                        <td><span class="nic-chip"><?= htmlspecialchars($row['nic_number']) ?></span></td>
                        <td style="font-weight:700;color:#0f172a;"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td>
                            <?php $is_m = in_array(strtolower($row['gender']),['male','lab']); ?>
                            <span class="gpill <?= $is_m ? 'm' : 'f' ?>">
                                <i class="fas <?= $is_m ? 'fa-mars' : 'fa-venus' ?>"></i>
                                <?= $is_m ? 'Lab (M)' : 'Dhedig (F)' ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:#64748b;"><?= htmlspecialchars($row['phone_number'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($row['photo_path']) && file_exists($row['photo_path'])): ?>
                                <img class="tbl-avatar" src="<?= htmlspecialchars($row['photo_path']) ?>"
                                     alt="Photo"
                                     onclick="previewPhoto('<?= htmlspecialchars($row['photo_path']) ?>','<?= addslashes(htmlspecialchars($row['full_name'])) ?>')"
                                     title="Weynee">
                            <?php else: ?>
                                <div class="tbl-avatar-ph"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#64748b;"><?= date('d M Y', strtotime($row['date_created'])) ?></td>
                        <td style="text-align:center;">
                            <button type="button" class="btn-gen"
                                onclick="openCardModal(<?= $row['id'] ?>,'<?= addslashes(htmlspecialchars($row['full_name'])) ?>','<?= addslashes(htmlspecialchars($row['nic_number'])) ?>')">
                                <i class="fas fa-id-card"></i> Samee Kaarka
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.page-wrap -->
</div><!-- /.main-content -->

<!-- ══════════════════════════════════════════════════════
     NIC CARD PREVIEW MODAL
══════════════════════════════════════════════════════════ -->
<div class="card-modal-overlay" id="cardModalOverlay" onclick="handleOverlayClick(event)">
    <div class="card-modal" id="cardModal">

        <!-- Header -->
        <div class="cm-header">
            <div class="cm-hdr-left">
                <div class="cm-hdr-icon"><i class="fas fa-id-card"></i></div>
                <div class="cm-hdr-text">
                    <h3 id="cmTitle">Preview Kaarka NIC</h3>
                    <p id="cmSubtitle">Kaarka Aqoonsiga Qaranka Soomaaliya</p>
                </div>
            </div>
            <button class="cm-close" onclick="closeCardModal()" title="Xir (Esc)">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <!-- Tabs -->
        <div class="cm-tabs">
            <button class="cm-tab active" id="tabFrontBtn" onclick="showCardTab('front',this)" type="button">
                <i class="fas fa-id-card"></i> Wajiga Hore (Front)
            </button>
            <button class="cm-tab" id="tabBackBtn" onclick="showCardTab('back',this)" type="button">
                <i class="fas fa-rotate-right"></i> Dhabarka (Back)
            </button>
        </div>

        <!-- Body -->
        <div class="cm-body">

            <!-- Front Panel -->
            <div class="card-panel active" id="panelFront">
                <div class="card-skeleton" id="skelFront"></div>
                <div class="nic-card-wrap" id="wrapFront" style="display:none;">
                    <img id="imgFront" src="" alt="NIC Front">
                </div>
                <div class="card-caption">
                    <i class="fas fa-info-circle"></i>
                    Wajiga hore wuxuu muujinayaa: magaca, NIC-ka, jinsiga, taariikhda dhashay, iyo sawirka.
                </div>
            </div>

            <!-- Back Panel -->
            <div class="card-panel" id="panelBack">
                <div class="card-skeleton" id="skelBack"></div>
                <div class="nic-card-wrap" id="wrapBack" style="display:none;">
                    <img id="imgBack" src="" alt="NIC Back">
                </div>
                <div class="card-caption">
                    <i class="fas fa-info-circle"></i>
                    Dhabarka wuxuu muujinayaa: cinwaanka, macluumaadka dheeraadka ah, iyo QR code-ka.
                </div>
            </div>

        </div><!-- /.cm-body -->

        <!-- Footer -->
        <div class="cm-footer">
            <div class="cm-info-pill">
                <i class="fas fa-id-badge" style="color:#0052cc;"></i>
                NIC: <code id="cmNicCode">—</code>
            </div>
            <div class="cm-footer-actions">
                <button type="button" class="btn-print-back" onclick="printCard('back')">
                    <i class="fas fa-qrcode"></i> Daabac Dhabarka
                </button>
                <button type="button" class="btn-print-front" onclick="printCard('front')">
                    <i class="fas fa-print"></i> Daabac Wajiga Hore
                </button>
            </div>
        </div>

    </div><!-- /.card-modal -->
</div><!-- /.card-modal-overlay -->

<!-- ── Scripts ──────────────────────────────────────────── -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
/* ── DataTable ──────────────────────────────────────────── */
$(document).ready(function () {
    $('#nicTable').DataTable({
        pageLength: 10,
        lengthMenu: [5,10,25,50],
        order: [[0,'desc']],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Raadi magac, NIC, telefoon...',
            lengthMenu: 'Muuji _MENU_',
            info: 'Waxaa la muujinayaa _START_–_END_ ka mid ah _TOTAL_ record',
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next:     '<i class="fas fa-chevron-right"></i>'
            },
            emptyTable: 'Wali ma jiraan diiwaano'
        }
    });
});

/* ── Card Modal state ───────────────────────────────────── */
let currentId  = null;
let frontLoaded = false;
let backLoaded  = false;

function openCardModal(id, name, nic) {
    currentId   = id;
    frontLoaded = false;
    backLoaded  = false;

    // Update header info
    document.getElementById('cmTitle').innerText    = name;
    document.getElementById('cmSubtitle').innerText = 'Kaarka Aqoonsiga — ' + nic;
    document.getElementById('cmNicCode').innerText  = nic;

    // Reset panels to loading state
    resetPanel('Front');
    resetPanel('Back');

    // Show modal
    document.getElementById('cardModalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';

    // Default to front tab
    showCardTab('front', document.getElementById('tabFrontBtn'));

    // Load front card image
    loadCardImage('front', id);
}

function resetPanel(side) {
    const skel = document.getElementById('skel' + side);
    const wrap = document.getElementById('wrap' + side);
    const img  = document.getElementById('img' + side);
    skel.style.display = 'block';
    wrap.style.display = 'none';
    img.src = '';
}

function loadCardImage(side, id) {
    const url  = side === 'front' ? `front.php?id=${id}` : `backup.php?id=${id}`;
    const img  = document.getElementById('img' + capitalize(side));
    const skel = document.getElementById('skel' + capitalize(side));
    const wrap = document.getElementById('wrap' + capitalize(side));

    img.onload = function() {
        skel.style.display = 'none';
        wrap.style.display = 'block';
        if (side === 'front') frontLoaded = true;
        else backLoaded = true;
    };
    img.onerror = function() {
        skel.style.display = 'none';
        wrap.innerHTML = `<div style="padding:40px;text-align:center;color:#94a3b8;">
            <i class="fas fa-image-slash" style="font-size:36px;display:block;margin-bottom:12px;color:#cbd5e1;"></i>
            <p style="font-size:14px;">Kaarku ma muuqdo — hubi sawirka iyo data-base.</p>
        </div>`;
        wrap.style.display = 'block';
    };
    img.src = url + '&t=' + Date.now(); // cache buster
}

function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function showCardTab(side, btn) {
    // Update tab buttons
    document.querySelectorAll('.cm-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    // Show correct panel
    document.querySelectorAll('.card-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel' + capitalize(side)).classList.add('active');

    // Lazy-load back card only when tab is first opened
    if (side === 'back' && !backLoaded && currentId) {
        loadCardImage('back', currentId);
    }
}

function closeCardModal() {
    document.getElementById('cardModalOverlay').classList.remove('active');
    document.body.style.overflow = '';
    currentId = null;
}

function handleOverlayClick(e) {
    if (e.target.id === 'cardModalOverlay') closeCardModal();
}

function printCard(side) {
    if (!currentId) return;
    const url = side === 'front' ? `front.php?id=${currentId}` : `backup.php?id=${currentId}`;
    const win = window.open('', '_blank', 'width=900,height=600');
    win.document.write(`
        <!DOCTYPE html><html><head>
        <title>Daabac Kaarka NIC</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { background:#fff; display:flex; align-items:center; justify-content:center; min-height:100vh; }
            img  { max-width:100%; height:auto; display:block; }
            @media print { body { margin:0; } }
        </style></head><body>
        <img src="${url}&t=${Date.now()}" onload="setTimeout(()=>{ window.print(); window.close(); }, 300)">
        </body></html>`);
    win.document.close();
}

/* ── Photo lightbox ─────────────────────────────────────── */
function previewPhoto(url, name) {
    const ov = document.createElement('div');
    ov.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(7,15,35,.85);display:flex;align-items:center;justify-content:center;cursor:zoom-out;backdrop-filter:blur(6px);';
    const img = document.createElement('img');
    img.src   = url;
    img.alt   = name;
    img.style.cssText = 'max-width:90vw;max-height:88vh;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.5);';
    ov.appendChild(img);
    ov.onclick = () => document.body.removeChild(ov);
    document.body.appendChild(ov);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCardModal(); });
</script>
</body>
</html>
