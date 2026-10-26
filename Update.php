<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include 'db.php';

/* ─── Helpers ─────────────────────────────────────────────────────── */
function generateNICNumber($conn) {
    $row    = $conn->query("SELECT MAX(id) AS max_id FROM nic_records")->fetch_assoc();
    $nextId = $row['max_id'] ? $row['max_id'] + 1 : 1;
    return 'SOM' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
}

/* ─── State ───────────────────────────────────────────────────────── */
$id = $full_name = $dob = $gender = $marital_status = '';
$citizenship = $mother_name = $address = $phone_number = '';
$occupation  = $additional_info = $photo_path = '';
$swal_type   = $swal_title = $swal_msg = '';
$auto_open_modal = false;
$data = [];

/* ─── GET: open modal for edit ────────────────────────────────────── */
if (!empty($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM nic_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if ($data) {
        extract($data);
        $auto_open_modal = true;
    }
    $stmt->close();
} elseif (isset($_GET['open']) && $_GET['open'] === 'modal') {
    $auto_open_modal = true;
}

/* ─── POST: save ──────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name       = trim($_POST['full_name']       ?? '');
    $dob             = trim($_POST['dob']             ?? '');
    $gender          = trim($_POST['gender']          ?? '');
    $marital_status  = trim($_POST['marital_status']  ?? '');
    $citizenship     = trim($_POST['citizenship']     ?? '');
    $mother_name     = trim($_POST['mother_name']     ?? '');
    $address         = trim($_POST['address']         ?? '');
    $phone_number    = trim($_POST['phone_number']    ?? '');
    $occupation      = trim($_POST['occupation']      ?? '');
    $additional_info = trim($_POST['additional_info'] ?? '');

    $new_photo = false;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext     = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('nic_') . "." . $ext;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $filename)) {
                $photo_path = $target_dir . $filename;
                $new_photo  = true;
            }
        }
    }

    if (!empty($_POST['id'])) {
        // UPDATE
        $id           = (int)$_POST['id'];
        $date_updated = date("Y-m-d H:i:s");
        if (!$new_photo) {
            $prev       = $conn->query("SELECT photo_path FROM nic_records WHERE id = $id")->fetch_assoc();
            $photo_path = $prev['photo_path'] ?? '';
        }
        $sql  = "UPDATE nic_records SET full_name=?, dob=?, gender=?, marital_status=?, citizenship=?,
                 mother_name=?, address=?, phone_number=?, occupation=?, additional_info=?,
                 photo_path=?, date_updated=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssi",
            $full_name,$dob,$gender,$marital_status,$citizenship,
            $mother_name,$address,$phone_number,$occupation,
            $additional_info,$photo_path,$date_updated,$id);
        if ($stmt->execute()) {
            $swal_type  = 'success';
            $swal_title = 'Si Guul Leh Loo Cusboonaysiiyay!';
            $swal_msg   = htmlspecialchars($full_name) . ' — xogtiisa si buuxda ayaa loo keydiyay.';
        } else {
            $swal_type  = 'error';
            $swal_title = 'Khalad Ayaa Dhacay!';
            $swal_msg   = 'Fadlan mar kale isku day.';
        }
        $stmt->close();
        $id = $full_name = $dob = $gender = $marital_status = '';
        $citizenship = $mother_name = $address = $phone_number = '';
        $occupation  = $additional_info = $photo_path = '';
    } else {
        // INSERT
        $nic_number   = generateNICNumber($conn);
        $date_created = date("Y-m-d H:i:s");
        $sql  = "INSERT INTO nic_records
                 (full_name,dob,gender,marital_status,citizenship,mother_name,
                  address,phone_number,occupation,additional_info,nic_number,photo_path,date_created)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssss",
            $full_name,$dob,$gender,$marital_status,$citizenship,
            $mother_name,$address,$phone_number,$occupation,
            $additional_info,$nic_number,$photo_path,$date_created);
        if ($stmt->execute()) {
            $swal_type  = 'success';
            $swal_title = 'Si Guul Leh Ayaa Loo Diiwaangeliyay!';
            $swal_msg   = 'NIC Number cusub: ' . $nic_number;
            $id = $full_name = $dob = $gender = $marital_status = '';
            $citizenship = $mother_name = $address = $phone_number = '';
            $occupation  = $additional_info = $photo_path = '';
        } else {
            $swal_type  = 'error';
            $swal_title = 'Khalad Ayaa Dhacay!';
            $swal_msg   = 'Diiwaangelinta lama dhamaystirin. Isku day mar kale.';
        }
        $stmt->close();
    }
}

/* ─── Page stats ──────────────────────────────────────────────────── */
$today         = date('Y-m-d');
$total_records = (int) $conn->query("SELECT COUNT(*) FROM nic_records")->fetch_row()[0];
$male_count    = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE gender='Male'")->fetch_row()[0];
$female_count  = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE gender='Female'")->fetch_row()[0];
$today_count   = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE DATE(date_created)='$today'")->fetch_row()[0];

/* ─── Fetch table records ─────────────────────────────────────────── */
$records   = $conn->query("SELECT * FROM nic_records ORDER BY id DESC");
$active_page = 'update';
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diiwaanka NIC – Diiwaangeli & Wax Ka Bedel</title>
    <meta name="description" content="NIC System – Diiwaangelinta iyo maamulka xogta aqoonsiga muwaadiniinta.">

    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="sidebar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
/* ── Base ───────────────────────────────────────────────────────── */
body { font-family:'Plus Jakarta Sans',-apple-system,sans-serif; background:#f1f5f9; }
.page-wrap { padding:0 4px; }

/* ── Top Bar ────────────────────────────────────────────────────── */
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
.dash-title-group p { font-size:13px; color:#64748b; font-weight:500; }
.dash-top-actions { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.pill-date {
    display:inline-flex; align-items:center; gap:8px; background:#f8fafc;
    border:1px solid #e2e8f0; padding:8px 16px; border-radius:50px;
    font-size:13px; font-weight:600; color:#475569;
}
.pill-date i { color:#0052cc; }

/* ── KPI Cards ──────────────────────────────────────────────────── */
.kpi-grid {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:18px; margin-bottom:24px;
}
.kpi-card {
    background:#fff; border-radius:16px; padding:22px 20px;
    border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,.03);
    display:flex; align-items:center; gap:16px;
    transition:transform .25s ease,box-shadow .25s ease;
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

/* ── Table Card ─────────────────────────────────────────────────── */
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
.btn-new {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#0052cc,#1e40af); color:#fff !important;
    padding:10px 20px; border-radius:12px; font-size:14px; font-weight:700;
    border:none; cursor:pointer; text-decoration:none;
    box-shadow:0 4px 14px rgba(0,82,204,.3);
    transition:all .2s ease; font-family:inherit;
}
.btn-new:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,82,204,.45); }

/* ── DataTable wrapper ──────────────────────────────────────────── */
.dt-wrap { padding:0 24px 24px; }
.dt-wrap .dataTables_wrapper .dataTables_filter input {
    border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 14px;
    font-family:inherit; font-size:13px; outline:none;
    transition:border-color .2s;
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
.tbl-avatar { width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0; cursor:pointer; transition:transform .2s; }
.tbl-avatar:hover { transform:scale(1.15); }
.tbl-avatar-ph { width:38px; height:38px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:15px; border:2px solid #e2e8f0; }
.act-btn {
    width:30px; height:30px; border-radius:8px; display:inline-flex;
    align-items:center; justify-content:center; font-size:13px;
    border:none; cursor:pointer; text-decoration:none;
    transition:all .2s;
}
.act-btn.edit   { background:#fffbeb; color:#d97706; }
.act-btn.print  { background:#ecfdf5; color:#059669; }
.act-btn:hover  { filter:brightness(.9); transform:scale(1.1); }
.act-grp { display:flex; gap:6px; justify-content:center; }

/* ── Modal Overlay ──────────────────────────────────────────────── */
.modal-overlay {
    position:fixed; inset:0; z-index:9000;
    background:rgba(15,23,42,0); backdrop-filter:blur(0px);
    display:flex; align-items:center; justify-content:center;
    padding:20px; pointer-events:none;
    transition:background .3s ease, backdrop-filter .3s ease;
}
.modal-overlay.active {
    background:rgba(15,23,42,.65); backdrop-filter:blur(6px);
    pointer-events:all;
}

/* ── Modal Box ──────────────────────────────────────────────────── */
.modal-box {
    background:#fff; border-radius:20px; width:100%; max-width:820px;
    max-height:92vh; display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 25px 60px rgba(0,0,0,.2), 0 0 0 1px rgba(255,255,255,.1);
    transform:translateY(40px) scale(.96); opacity:0;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s ease;
}
.modal-overlay.active .modal-box { transform:translateY(0) scale(1); opacity:1; }

/* ── Modal Header ───────────────────────────────────────────────── */
.modal-hdr {
    padding:22px 28px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between;
    flex-shrink:0; background:linear-gradient(135deg,#0a1834 0%,#1e3a8a 100%);
    border-radius:20px 20px 0 0;
}
.modal-hdr-info { display:flex; align-items:center; gap:14px; }
.modal-icon-wrap {
    width:46px; height:46px; background:rgba(255,255,255,.15); border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff;
}
.modal-hdr-text h3 { font-family:'Outfit',sans-serif; font-size:18px; font-weight:800; color:#fff; margin-bottom:2px; }
.modal-hdr-text p  { font-size:12px; color:rgba(255,255,255,.6); }
.modal-close {
    width:36px; height:36px; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
    border-radius:10px; display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#fff; font-size:15px; transition:all .2s;
}
.modal-close:hover { background:rgba(239,68,68,.3); border-color:rgba(239,68,68,.5); }

/* ── Wizard Stepper ─────────────────────────────────────────────── */
.wizard-stepper {
    display:flex; align-items:center; padding:20px 28px;
    background:#f8fafc; border-bottom:1px solid #e2e8f0; flex-shrink:0;
    gap:0;
}
.wz-step {
    display:flex; align-items:center; gap:10px; flex:1;
    position:relative;
}
.wz-step:not(:last-child)::after {
    content:''; flex:1; height:2px;
    background:#e2e8f0; margin:0 12px; border-radius:2px;
    transition:background .4s ease;
}
.wz-step.done:not(:last-child)::after { background:#10b981; }
.wz-step.active:not(:last-child)::after { background:#dbeafe; }
.wz-circle {
    width:36px; height:36px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:800; border:2px solid #e2e8f0;
    background:#fff; color:#94a3b8; transition:all .3s ease;
}
.wz-step.active .wz-circle {
    background:#0052cc; border-color:#0052cc; color:#fff;
    box-shadow:0 0 0 4px rgba(0,82,204,.15);
}
.wz-step.done .wz-circle {
    background:#10b981; border-color:#10b981; color:#fff;
}
.wz-label {
    display:flex; flex-direction:column;
}
.wz-label span { font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; }
.wz-label strong { font-size:13px; font-weight:700; color:#334155; }
.wz-step.active .wz-label span   { color:#0052cc; }
.wz-step.active .wz-label strong  { color:#0f172a; }
.wz-step.done   .wz-label span   { color:#10b981; }
.tab-panel { display:none; }
.tab-panel.active { display:block; animation:fadeSlideIn .25s ease; }
@keyframes fadeSlideIn {
    from { opacity:0; transform:translateX(14px); }
    to   { opacity:1; transform:translateX(0); }
}

/* ── Modal Body ─────────────────────────────────────────────────── */
.modal-body { overflow-y:auto; flex:1; padding:28px; }
.modal-body::-webkit-scrollbar { width:5px; }
.modal-body::-webkit-scrollbar-track { background:#f8fafc; }
.modal-body::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }

/* ── Form Grid ──────────────────────────────────────────────────── */
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.form-full  { grid-column:1/-1; }

.form-group label {
    display:flex; align-items:center; gap:7px;
    font-size:12px; font-weight:700; color:#374151;
    text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;
}
.form-group label i { color:#0052cc; }
.form-group input,
.form-group select,
.form-group textarea {
    width:100%; padding:11px 14px; border:1.5px solid #e2e8f0;
    border-radius:10px; font-size:14px; font-family:inherit;
    color:#0f172a; background:#fff; outline:none;
    transition:border-color .2s, box-shadow .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color:#0052cc;
    box-shadow:0 0 0 3px rgba(0,82,204,.1);
}
.form-group textarea { resize:vertical; min-height:90px; }

/* ── Photo Upload ───────────────────────────────────────────────── */
.photo-drop {
    border:2px dashed #cbd5e1; border-radius:14px; padding:28px 20px;
    text-align:center; cursor:pointer; background:#fafbfc;
    transition:border-color .2s, background .2s;
}
.photo-drop:hover { border-color:#0052cc; background:#f0f5ff; }
.photo-drop input[type="file"] { display:none; }
.photo-drop-icon { font-size:36px; color:#cbd5e1; margin-bottom:10px; }
.photo-drop h4   { font-size:14px; font-weight:700; color:#334155; margin-bottom:4px; }
.photo-drop p    { font-size:12px; color:#94a3b8; }
.photo-preview {
    display:none; position:relative; width:120px; height:140px;
    margin:0 auto; border-radius:10px; overflow:hidden;
    border:3px solid #0052cc;
}
.photo-preview img { width:100%; height:100%; object-fit:cover; }
.photo-preview-remove {
    position:absolute; top:4px; right:4px; width:22px; height:22px;
    background:rgba(239,68,68,.9); border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:10px; cursor:pointer; border:none;
}

/* ── Section Divider ────────────────────────────────────────────── */
.form-section-label {
    display:flex; align-items:center; gap:10px;
    font-size:11px; font-weight:800; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.8px;
    margin:4px 0 2px; grid-column:1/-1;
}
.form-section-label::after { content:''; flex:1; height:1px; background:#f1f5f9; }

/* ── Modal Footer (Wizard) ──────────────────────────────────────── */
.modal-ftr {
    padding:18px 28px; border-top:1px solid #f1f5f9;
    display:flex; justify-content:space-between; align-items:center;
    gap:12px; flex-shrink:0;
}
.modal-ftr-left  { display:flex; gap:10px; }
.modal-ftr-right { display:flex; gap:10px; }
.btn-cancel {
    display:inline-flex; align-items:center; gap:8px;
    background:#f8fafc; color:#64748b; border:1.5px solid #e2e8f0;
    padding:10px 20px; border-radius:10px; font-size:14px;
    font-weight:600; font-family:inherit; cursor:pointer;
    transition:all .2s;
}
.btn-cancel:hover { border-color:#ef4444; color:#ef4444; }
.btn-back {
    display:inline-flex; align-items:center; gap:8px;
    background:#f1f5f9; color:#334155; border:1.5px solid #e2e8f0;
    padding:10px 20px; border-radius:10px; font-size:14px;
    font-weight:600; font-family:inherit; cursor:pointer;
    transition:all .2s;
}
.btn-back:hover { background:#e2e8f0; }
.btn-next {
    display:inline-flex; align-items:center; gap:8px;
    background:#0f172a; color:#fff; border:none;
    padding:11px 24px; border-radius:10px; font-size:14px;
    font-weight:700; font-family:inherit; cursor:pointer;
    transition:all .2s ease;
}
.btn-next:hover { background:#1e293b; transform:translateX(2px); }
.btn-save {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#059669,#047857); color:#fff;
    border:none; padding:11px 26px; border-radius:10px; font-size:14px;
    font-weight:700; font-family:inherit; cursor:pointer;
    box-shadow:0 4px 14px rgba(5,150,105,.3);
    transition:all .25s ease;
}
.btn-save:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(5,150,105,.45); }
/* Step indicator in footer */
.step-dots { display:flex; gap:6px; align-items:center; }
.step-dot {
    width:8px; height:8px; border-radius:50%; background:#e2e8f0;
    transition:all .3s ease;
}
.step-dot.active { background:#0052cc; width:22px; border-radius:4px; }
.step-dot.done   { background:#10b981; }

/* ── DataTables pagination ─────────────────────────────────────── */
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding:4px 10px; border-radius:7px; border:1px solid #e2e8f0;
    font-size:13px; cursor:pointer; margin:0 2px;
}
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background:#0052cc !important; color:#fff !important; border-color:#0052cc !important;
}
.dt-wrap .dataTables_wrapper .dataTables_info { font-size:13px; color:#64748b; padding-top:12px; }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width:900px) {
    .kpi-grid { grid-template-columns:repeat(2,1fr); }
    .form-grid { grid-template-columns:1fr; }
    .form-full { grid-column:1; }
}
@media (max-width:600px) {
    .kpi-grid { grid-template-columns:1fr; }
    .modal-box { border-radius:14px; max-height:98vh; }
}
</style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content" id="mainContent">
<div class="page-wrap">

    <!-- ── Top Bar ──────────────────────────────────────────────── -->
    <div class="dash-topbar">
        <div class="dash-title-group">
            <h1><i class="fas fa-id-card-alt" style="color:#0052cc;"></i> Diiwaanka Aqoonsiga Qaranka</h1>
            <p>Maamulka Xogta Muwaadiniinta Jamhuuriyadda Federaalka Soomaaliya</p>
        </div>
        <div class="dash-top-actions">
            <div class="pill-date"><i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?></div>
            <button type="button" class="btn-new" onclick="openCreateModal()">
                <i class="fas fa-user-plus"></i> Diiwaangeli NIC Cusub
            </button>
        </div>
    </div>

    <!-- ── KPI Cards ────────────────────────────────────────────── -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-database"></i></div>
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

    <!-- ── Records Table Card ───────────────────────────────────── -->
    <div class="tbl-card">
        <div class="tbl-card-hdr">
            <div class="tbl-card-title">
                <i class="fas fa-table-list"></i>
                Dhammaan Muwaadiniinta Diiwaangashan
                <span class="rec-badge"><?= number_format($total_records) ?> Record</span>
            </div>
            <button type="button" class="btn-new" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Ku Dar Cusub
            </button>
        </div>

        <div class="dt-wrap">
            <table id="nicTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIC Number</th>
                        <th>Magaca Buuxa</th>
                        <th>Taar. Dhashay</th>
                        <th>Jinsiga</th>
                        <th>Telefoon</th>
                        <th>Sawir</th>
                        <th>Taariikhda</th>
                        <th style="text-align:center;">Ficilada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records && $records->num_rows > 0): ?>
                    <?php while ($row = $records->fetch_assoc()): ?>
                    <tr>
                        <td><span style="font-size:12px;color:#94a3b8;font-weight:700;">#<?= $row['id'] ?></span></td>
                        <td><span class="nic-chip"><?= htmlspecialchars($row['nic_number']) ?></span></td>
                        <td style="font-weight:700;color:#0f172a;"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td style="font-size:13px;color:#64748b;"><?= $row['dob'] ? date('d M Y', strtotime($row['dob'])) : '—' ?></td>
                        <td>
                            <?php $is_m = in_array(strtolower($row['gender']),['male','lab']); ?>
                            <span class="gpill <?= $is_m ? 'm' : 'f' ?>">
                                <i class="fas <?= $is_m ? 'fa-mars' : 'fa-venus' ?>"></i>
                                <?= $is_m ? 'Lab (M)' : 'Dhedig (F)' ?>
                            </span>
                        </td>
                        <td style="font-size:13px;color:#64748b;"><?= htmlspecialchars($row['phone_number'] ?: '—') ?></td>
                        <td>
                            <?php if (!empty($row['photo_path']) && file_exists($row['photo_path'])): ?>
                                <img class="tbl-avatar" src="<?= htmlspecialchars($row['photo_path']) ?>"
                                     alt="Photo" onclick="previewImage('<?= htmlspecialchars($row['photo_path']) ?>','<?= addslashes(htmlspecialchars($row['full_name'])) ?>')" title="Weynee">
                            <?php else: ?>
                                <div class="tbl-avatar-ph"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#64748b;"><?= date('d M Y', strtotime($row['date_created'])) ?></td>
                        <td>
                            <div class="act-grp">
                                <button type="button" class="act-btn edit"
                                    onclick='openEditModal(<?= json_encode($row, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'
                                    title="Wax ka bedel">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                                <a href="generate_nationalid.php?id=<?= $row['id'] ?>" class="act-btn print" title="Daabac Kaarka">
                                    <i class="fas fa-id-card"></i>
                                </a>
                            </div>
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

<!-- ══════════════════════════════════════════════════════════════
     MODAL POPUP
══════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="nicModalOverlay" onclick="handleBackdropClick(event)">
    <div class="modal-box" id="nicModalBox">

        <!-- Modal Header -->
        <div class="modal-hdr">
            <div class="modal-hdr-info">
                <div class="modal-icon-wrap"><i class="fas fa-id-card-alt" id="modalIcon"></i></div>
                <div class="modal-hdr-text">
                    <h3 id="modalTitle">Diiwaangeli NIC Cusub</h3>
                    <p id="modalSubtitle">Buuxi xogaha qofka si buuxda</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeNicModal()" title="Xir (Esc)">
                <i class="fas fa-xmark"></i>
            </button>
        </div>


        <!-- Wizard Stepper (display-only, controlled by JS) -->
        <div class="wizard-stepper" id="wizardStepper">
            <div class="wz-step active" id="wz1">
                <div class="wz-circle"><span id="wz1-icon">1</span></div>
                <div class="wz-label">
                    <span>Tallaabada 1</span>
                    <strong>Xogaha Shakhsiga</strong>
                </div>
            </div>
            <div class="wz-step" id="wz2">
                <div class="wz-circle"><span id="wz2-icon">2</span></div>
                <div class="wz-label">
                    <span>Tallaabada 2</span>
                    <strong>Cinwaanka &amp; Xiriirka</strong>
                </div>
            </div>
            <div class="wz-step" id="wz3">
                <div class="wz-circle"><span id="wz3-icon">3</span></div>
                <div class="wz-label">
                    <span>Tallaabada 3</span>
                    <strong>Sawirka</strong>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" id="nicForm" style="display:flex;flex-direction:column;flex:1;overflow:hidden;">
            <input type="hidden" name="id" id="form_id" value="">

            <div class="modal-body">

                <!-- Tab 1: Personal Info -->
                <div class="tab-panel active" id="tab1">
                    <div class="form-grid">
                        <div class="form-section-label"><i class="fas fa-circle-info"></i> Macluumaadka Aasaasiga</div>

                        <div class="form-group form-full">
                            <label><i class="fas fa-user"></i> Magaca Buuxa (Full Name) *</label>
                            <input type="text" name="full_name" id="form_full_name" placeholder="Geli magaca oo seddexan ama afar ah" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> Taariikhda Dhashay *</label>
                            <input type="date" name="dob" id="form_dob" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Jinsiga (Gender) *</label>
                            <select name="gender" id="form_gender" required>
                                <option value="">-- Dooro Jinsiga --</option>
                                <option value="Male">Lab (Male)</option>
                                <option value="Female">Dhedig (Female)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-ring"></i> Xaaladda Guurka *</label>
                            <select name="marital_status" id="form_marital_status" required>
                                <option value="">-- Dooro Xaaladda --</option>
                                <option value="Doob">Doob (Single)</option>
                                <option value="Xaas">Xaas (Married)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-flag"></i> Muwaatinnimada *</label>
                            <select name="citizenship" id="form_citizenship" required>
                                <option value="">-- Dooro Muwaatinnimada --</option>
                                <option value="Muwaadin">Muwaadin (Somali Citizen)</option>
                                <option value="Soo Galooti">Soo Galooti (Resident)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-female"></i> Magaca Hooyada</label>
                            <input type="text" name="mother_name" id="form_mother_name" placeholder="Geli magaca hooyada">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> Nooca Shaqada</label>
                            <input type="text" name="occupation" id="form_occupation" placeholder="Arday, Ganacsade, Dhaqtar...">
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Contact & Address -->
                <div class="tab-panel" id="tab2">
                    <div class="form-grid">
                        <div class="form-section-label"><i class="fas fa-location-dot"></i> Cinwaanka & Xiriirka</div>

                        <div class="form-group form-full">
                            <label><i class="fas fa-map-marker-alt"></i> Cinwaanka / Degmada (Address)</label>
                            <input type="text" name="address" id="form_address" placeholder="Tusaale: Hodan, Muqdisho">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-phone-alt"></i> Lambarka Telefoonka</label>
                            <input type="text" name="phone_number" id="form_phone_number" placeholder="+252 61 XXXXXXX">
                        </div>

                        <div class="form-group form-full">
                            <label><i class="fas fa-comment-alt"></i> Macluumaad Dheeraad ah</label>
                            <textarea name="additional_info" id="form_additional_info" placeholder="Faahfaahin kale oo muhiim ah haddii ay jirto..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Photo -->
                <div class="tab-panel" id="tab3">
                    <div class="form-section-label" style="margin-bottom:18px;"><i class="fas fa-camera"></i> Sawirka Qofka</div>

                    <!-- Upload Drop Zone -->
                    <div class="photo-drop" id="photoDrop" onclick="document.getElementById('photoInput').click()">
                        <input id="photoInput" type="file" name="photo" accept="image/*">
                        <div id="photoDropContent">
                            <div class="photo-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <h4>Guji ama Soo jiid Sawirka</h4>
                            <p>JPG, PNG, WEBP — ugu badan 5MB</p>
                        </div>
                        <div class="photo-preview" id="photoPreview">
                            <img id="photoPreviewImg" src="" alt="Preview">
                            <button type="button" class="photo-preview-remove" onclick="removePhoto(event)" title="Ka saar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p style="font-size:12px;color:#94a3b8;text-align:center;margin-top:12px;">
                        <i class="fas fa-info-circle"></i> Sawirka waxaa la isticmaali doonaa kaadhka aqoonsiga
                    </p>
                </div>

            </div><!-- /.modal-body -->

            <!-- Wizard Footer -->
            <div class="modal-ftr">
                <div class="modal-ftr-left">
                    <button type="button" class="btn-cancel" onclick="closeNicModal()">
                        <i class="fas fa-times"></i> Jooji
                    </button>
                    <button type="button" class="btn-back" id="btnBack" style="display:none;" onclick="wizardBack()">
                        <i class="fas fa-chevron-left"></i> Xiga Hore
                    </button>
                </div>
                <!-- Step dots -->
                <div class="step-dots">
                    <div class="step-dot active" id="dot1"></div>
                    <div class="step-dot" id="dot2"></div>
                    <div class="step-dot" id="dot3"></div>
                </div>
                <div class="modal-ftr-right">
                    <button type="button" class="btn-next" id="btnNext" onclick="wizardNext()">
                        Xiga <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="submit" class="btn-save" id="btnSave" style="display:none;">
                        <i class="fas fa-check-circle"></i> <span id="submitBtnText">Diiwaangeli Hadda</span>
                    </button>
                </div>
            </div>
        </form>

    </div><!-- /.modal-box -->
</div><!-- /.modal-overlay -->

<!-- ── Scripts ──────────────────────────────────────────────────── -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ── DataTable ─────────────────────────────────────── */
$(document).ready(function () {
    $('#nicTable').DataTable({
        pageLength: 10,
        lengthMenu: [5,10,25,50],
        order: [[0,'desc']],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Raadi magac, NIC, telefoon...',
            lengthMenu: 'Muuji _MENU_',
            info: 'Waxaa la muujinayaa _START_–_END_ ka mid ah _TOTAL_ muwaadin',
            paginate: {
                previous: '<i class="fas fa-chevron-left"></i>',
                next: '<i class="fas fa-chevron-right"></i>'
            },
            emptyTable: 'Wali ma jiraan diiwaano la geliyay'
        }
    });

    <?php if ($auto_open_modal): ?>
        <?php if (!empty($id) && !empty($data)): ?>
            openEditModal(<?= json_encode($data, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>);
        <?php else: ?>
            openCreateModal();
        <?php endif; ?>
    <?php endif; ?>
});

/* ── Wizard State ──────────────────────────────────── */
let currentStep = 1;
const TOTAL_STEPS = 3;

function gotoStep(step) {
    currentStep = step;
    // Show correct panel
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const p = document.getElementById('tab' + i);
        if (p) p.classList.toggle('active', i === step);
    }
    // Update stepper visuals
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const wz   = document.getElementById('wz' + i);
        const icon = document.getElementById('wz' + i + '-icon');
        const dot  = document.getElementById('dot' + i);
        wz.classList.remove('active','done');
        dot.classList.remove('active','done');
        if (i < step) {
            wz.classList.add('done'); dot.classList.add('done');
            icon.className = 'fas fa-check'; icon.textContent = '';
        } else if (i === step) {
            wz.classList.add('active'); dot.classList.add('active');
            icon.className = ''; icon.textContent = i;
        } else {
            icon.className = ''; icon.textContent = i;
        }
    }
    // Footer buttons
    document.getElementById('btnBack').style.display = step > 1            ? 'inline-flex' : 'none';
    document.getElementById('btnNext').style.display = step < TOTAL_STEPS  ? 'inline-flex' : 'none';
    document.getElementById('btnSave').style.display = step === TOTAL_STEPS ? 'inline-flex' : 'none';
}

function validateStep1() {
    const req = ['form_full_name','form_dob','form_gender','form_marital_status','form_citizenship'];
    let ok = true;
    req.forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            ok = false;
            el.style.borderColor = '#ef4444';
            el.style.boxShadow   = '0 0 0 3px rgba(239,68,68,.12)';
            el.addEventListener('input', () => { el.style.borderColor=''; el.style.boxShadow=''; }, {once:true});
        }
    });
    if (!ok) Swal.fire({
        icon:'warning', title:'Fadlan buuxi!',
        text:'Goobaha khasabka ah (*) waa in la buuxiyaa.',
        confirmButtonColor:'#0052cc', confirmButtonText:'Ogow',
        timer:3000, timerProgressBar:true
    });
    return ok;
}

function wizardNext() { if (currentStep === 1 && !validateStep1()) return; if (currentStep < TOTAL_STEPS) gotoStep(currentStep + 1); }
function wizardBack() { if (currentStep > 1) gotoStep(currentStep - 1); }

/* ── Modal open/close ──────────────────────────────── */
function openCreateModal() {
    document.getElementById('nicForm').reset();
    document.getElementById('form_id').value = '';
    document.getElementById('modalTitle').innerText    = 'Diiwaangeli NIC Cusub';
    document.getElementById('modalSubtitle').innerText = 'Raac tallaabadaha si buuxda';
    document.getElementById('modalIcon').className     = 'fas fa-user-plus';
    document.getElementById('submitBtnText').innerText = 'Diiwaangeli Hadda';
    resetPhotoPreview(); clearFieldErrors(); gotoStep(1); showModal();
}

function openEditModal(rec) {
    if (!rec) return;
    document.getElementById('nicForm').reset();
    document.getElementById('form_id').value             = rec.id || '';
    document.getElementById('form_full_name').value      = rec.full_name || '';
    document.getElementById('form_dob').value            = rec.dob || '';
    document.getElementById('form_gender').value         = rec.gender || '';
    document.getElementById('form_marital_status').value = rec.marital_status || '';
    document.getElementById('form_citizenship').value    = rec.citizenship || '';
    document.getElementById('form_mother_name').value    = rec.mother_name || '';
    document.getElementById('form_address').value        = rec.address || '';
    document.getElementById('form_phone_number').value   = rec.phone_number || '';
    document.getElementById('form_occupation').value     = rec.occupation || '';
    document.getElementById('form_additional_info').value= rec.additional_info || '';

    const nic = rec.nic_number ? ' — ' + rec.nic_number : '';
    document.getElementById('modalTitle').innerText    = 'Wax ka bedel Diiwaanka' + nic;
    document.getElementById('modalSubtitle').innerText = 'Raac tallaabadaha si aad u cusboonaysiiso';
    document.getElementById('modalIcon').className     = 'fas fa-user-edit';
    document.getElementById('submitBtnText').innerText = 'Keydi Isbeddelka';

    if (rec.photo_path) {
        document.getElementById('photoPreviewImg').src          = rec.photo_path;
        document.getElementById('photoPreview').style.display   = 'block';
        document.getElementById('photoDropContent').style.display = 'none';
    } else { resetPhotoPreview(); }
    clearFieldErrors(); gotoStep(1); showModal();
}

function showModal()    { document.getElementById('nicModalOverlay').classList.add('active'); document.body.style.overflow='hidden'; }
function closeNicModal(){ document.getElementById('nicModalOverlay').classList.remove('active'); document.body.style.overflow=''; }
function handleBackdropClick(e) { if (e.target.id==='nicModalOverlay') closeNicModal(); }
function clearFieldErrors() {
    document.querySelectorAll('.form-group input,.form-group select,.form-group textarea').forEach(el => {
        el.style.borderColor=''; el.style.boxShadow='';
    });
}
document.addEventListener('keydown', e => { if (e.key==='Escape') closeNicModal(); });

/* ── Photo handling ────────────────────────────────── */
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        document.getElementById('photoPreviewImg').src = evt.target.result;
        document.getElementById('photoPreview').style.display = 'block';
        document.getElementById('photoDropContent').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

function removePhoto(e) {
    e.stopPropagation();
    document.getElementById('photoInput').value = '';
    resetPhotoPreview();
}

function resetPhotoPreview() {
    document.getElementById('photoPreviewImg').src = '';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('photoDropContent').style.display = 'block';
}

// Drag-and-drop on photo zone
const dropZone = document.getElementById('photoDrop');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor='#0052cc'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor=''; });
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.style.borderColor='';
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer(); dt.items.add(file);
    document.getElementById('photoInput').files = dt.files;
    const reader = new FileReader();
    reader.onload = evt => {
        document.getElementById('photoPreviewImg').src = evt.target.result;
        document.getElementById('photoPreview').style.display = 'block';
        document.getElementById('photoDropContent').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

/* ── Lightbox preview ──────────────────────────────── */
function previewImage(url, name) {
    Swal.fire({
        title: name, imageUrl: url, imageAlt: name,
        showCloseButton: true, showConfirmButton: false,
        background: '#fff', backdrop: 'rgba(15,23,42,.7)'
    });
}

/* ── SweetAlert from PHP ───────────────────────────── */
<?php if ($swal_type): ?>
Swal.fire({
    icon: '<?= $swal_type ?>',
    title: '<?= addslashes($swal_title) ?>',
    text:  '<?= addslashes($swal_msg) ?>',
    confirmButtonColor: '#1d4ed8',
    confirmButtonText:  'Waayahay (OK)',
    timer:            <?= $swal_type === 'success' ? '4000' : '0' ?>,
    timerProgressBar:   true
});
<?php endif; ?>
</script>
</body>
</html>
