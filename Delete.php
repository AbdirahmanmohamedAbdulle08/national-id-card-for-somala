<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Africa/Nairobi');

$deleted_success = false;
$deleted_msg     = '';
$deleted_name    = '';
$deleted_nic     = '';

/* ─── Delete handler ──────────────────────────────────── */
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $row = $conn->query("SELECT photo_path, full_name, nic_number FROM nic_records WHERE id = $delete_id")->fetch_assoc();
    if ($row) {
        $deleted_name = $row['full_name'];
        $deleted_nic  = $row['nic_number'];
        if (!empty($row['photo_path']) && file_exists($row['photo_path'])) {
            @unlink($row['photo_path']);
        }
        $conn->query("DELETE FROM nic_records WHERE id = $delete_id");
        $deleted_success = true;
        $deleted_msg = "Diiwaanka #{$delete_id} — {$deleted_name} ({$deleted_nic}) si guul leh ayaa loo tirtiray.";
    }
}

/* ─── Stats ───────────────────────────────────────────── */
$today          = date('Y-m-d');
$total_records  = (int) $conn->query("SELECT COUNT(*) FROM nic_records")->fetch_row()[0];
$today_count    = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE DATE(date_created)='$today'")->fetch_row()[0];
$male_count     = (int) $conn->query("SELECT COUNT(*) FROM nic_records WHERE gender='Male'")->fetch_row()[0];
$female_count   = $total_records - $male_count;

/* ─── Records table ───────────────────────────────────── */
$records   = $conn->query("SELECT * FROM nic_records ORDER BY id DESC");
$active_page = 'delete';
?>
<!DOCTYPE html>
<html lang="so">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tirtir Diiwaanada – NIC System</title>
    <meta name="description" content="Maamulka iyo tirtiridda diiwaanada aqoonsiga muwaadiniinta.">

    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="sidebar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
.dash-title-group h1 i { color:#dc2626; }
.dash-title-group p { font-size:13px; color:#64748b; font-weight:500; }
.dash-top-actions { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.pill-date {
    display:inline-flex; align-items:center; gap:8px; background:#f8fafc;
    border:1px solid #e2e8f0; padding:8px 16px; border-radius:50px;
    font-size:13px; font-weight:600; color:#475569;
}
.pill-date i { color:#0052cc; }
.btn-view {
    display:inline-flex; align-items:center; gap:8px;
    background:#f8fafc; color:#475569; border:1.5px solid #e2e8f0;
    padding:10px 20px; border-radius:12px; font-size:14px; font-weight:700;
    text-decoration:none; transition:all .2s;
}
.btn-view:hover { border-color:#0052cc; color:#0052cc; }

/* ── Alert Banner ─────────────────────────────────────── */
.danger-alert {
    background:linear-gradient(135deg,#7f1d1d 0%,#dc2626 100%);
    border-radius:16px; padding:20px 26px; margin-bottom:24px;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:16px;
    box-shadow:0 6px 24px rgba(220,38,38,.22);
    animation:alertPulse 3s ease-in-out infinite;
}
@keyframes alertPulse {
    0%,100% { box-shadow:0 6px 24px rgba(220,38,38,.22); }
    50%      { box-shadow:0 8px 32px rgba(220,38,38,.38); }
}
.danger-alert-left { display:flex; align-items:center; gap:16px; }
.danger-alert-icon {
    width:48px; height:48px; border-radius:12px;
    background:rgba(255,255,255,.15); display:flex; align-items:center;
    justify-content:center; font-size:22px; color:#fff; flex-shrink:0;
}
.danger-alert-text h3 { font-family:'Outfit',sans-serif; font-size:16px; font-weight:800; color:#fff; margin-bottom:3px; }
.danger-alert-text p  { font-size:13px; color:rgba(255,255,255,.75); }
.danger-count-pill {
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
    padding:10px 22px; border-radius:50px; font-size:14px; font-weight:800;
    color:#fff; white-space:nowrap; display:flex; align-items:center; gap:8px;
}
.danger-count-pill span { font-family:'Outfit',sans-serif; font-size:22px; }

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
.kpi-icon.red    { background:#fef2f2; color:#dc2626; }
.kpi-icon.blue   { background:#eff6ff; color:#2563eb; }
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
.tbl-card-title i { color:#dc2626; }
.rec-badge {
    background:#fef2f2; color:#dc2626; font-size:12px;
    font-weight:700; padding:4px 12px; border-radius:50px; border:1px solid #fecaca;
}

/* ── DataTable ────────────────────────────────────────── */
.dt-wrap { padding:0 24px 24px; }
.dt-wrap .dataTables_wrapper .dataTables_filter input {
    border:1.5px solid #e2e8f0; border-radius:10px; padding:8px 14px;
    font-family:inherit; font-size:13px; outline:none; transition:border-color .2s;
}
.dt-wrap .dataTables_wrapper .dataTables_filter input:focus { border-color:#dc2626; }
.dt-wrap .dataTables_wrapper .dataTables_length select {
    border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 10px;
    font-family:inherit; font-size:13px; cursor:pointer;
}
table.dataTable thead th {
    background:#fef2f2; color:#7f1d1d; font-weight:700; font-size:12px;
    text-transform:uppercase; letter-spacing:.6px;
    border-bottom:1px solid #fecaca !important; white-space:nowrap;
    padding:14px 16px !important;
}
table.dataTable tbody td {
    padding:13px 16px !important; border-bottom:1px solid #f1f5f9 !important;
    font-size:14px; vertical-align:middle; color:#1e293b;
}
table.dataTable tbody tr:hover td { background:#fff5f5; }
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

/* ── Delete Button ────────────────────────────────────── */
.btn-delete {
    display:inline-flex; align-items:center; gap:7px;
    background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca;
    padding:8px 16px; border-radius:10px; font-size:13px; font-weight:700;
    font-family:inherit; cursor:pointer;
    transition:all .2s ease;
}
.btn-delete:hover {
    background:#dc2626; color:#fff; border-color:#dc2626;
    box-shadow:0 4px 14px rgba(220,38,38,.3);
    transform:translateY(-1px);
}

/* ── DataTables pagination ────────────────────────────── */
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding:4px 10px; border-radius:7px; border:1px solid #e2e8f0;
    font-size:13px; cursor:pointer; margin:0 2px;
}
.dt-wrap .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background:#dc2626 !important; color:#fff !important; border-color:#dc2626 !important;
}
.dt-wrap .dataTables_wrapper .dataTables_info { font-size:13px; color:#64748b; padding-top:12px; }

/* ── Responsive ───────────────────────────────────────── */
@media (max-width:1000px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:600px)  { .kpi-grid { grid-template-columns:1fr; } }
</style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content" id="mainContent">
<div class="page-wrap">

    <!-- ── Top Bar ──────────────────────────────────────── -->
    <div class="dash-topbar">
        <div class="dash-title-group">
            <h1><i class="fas fa-trash-alt"></i> Tirtir Diiwaanada NIC</h1>
            <p>Diiwaanka la tirtiray dib looma soo celin karo — taxaddar badan oo lagu talo galay</p>
        </div>
        <div class="dash-top-actions">
            <div class="pill-date"><i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?></div>
            <a href="Update.php" class="btn-view">
                <i class="fas fa-table-list"></i> Diiwaanada Oo Dhan
            </a>
        </div>
    </div>

    <!-- ── Danger Alert Banner ──────────────────────────── -->

        <div class="danger-count-pill">
            <i class="fas fa-database"></i>
            Wadarta: <span><?= number_format($total_records) ?></span>
        </div>
    </div>

    <!-- ── KPI Cards ────────────────────────────────────── -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="fas fa-database"></i></div>
            <div class="kpi-info"><h4>Wadarta Diiwaanka</h4><p><?= number_format($total_records) ?></p></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-user-plus"></i></div>
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

    <!-- ── Records Table ────────────────────────────────── -->
    <div class="tbl-card">
        <div class="tbl-card-hdr">
            <div class="tbl-card-title">
                <i class="fas fa-list-check"></i>
                Liiska Dhammaan Diiwaanada
                <span class="rec-badge"><?= number_format($total_records) ?> Record</span>
            </div>
        </div>

        <div class="dt-wrap">
            <table id="deleteTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIC Number</th>
                        <th>Magaca Buuxa</th>
                        <th>Jinsiga</th>
                        <th>Telefoon</th>
                        <th>Sawir</th>
                        <th>Taariikhda</th>
                        <th style="text-align:center;">Tirtir</th>
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
                                     onclick="previewImage('<?= htmlspecialchars($row['photo_path']) ?>','<?= addslashes(htmlspecialchars($row['full_name'])) ?>')"
                                     title="Weynee">
                            <?php else: ?>
                                <div class="tbl-avatar-ph"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#64748b;"><?= date('d M Y', strtotime($row['date_created'])) ?></td>
                        <td style="text-align:center;">
                            <button type="button" class="btn-delete"
                                onclick="confirmDelete(<?= $row['id'] ?>,'<?= addslashes(htmlspecialchars($row['full_name'])) ?>','<?= addslashes(htmlspecialchars($row['nic_number'])) ?>')">
                                <i class="fas fa-trash-alt"></i> Tirtir
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:50px 20px;color:#64748b;">
                            <i class="fas fa-folder-open" style="font-size:40px;color:#cbd5e1;display:block;margin-bottom:12px;"></i>
                            Wali ma jiraan diiwaano la diiwaangeliyay.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.page-wrap -->
</div><!-- /.main-content -->

<!-- ── Scripts ──────────────────────────────────────────── -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ── DataTable ──────────────────────────────────────────── */
$(document).ready(function () {
    $('#deleteTable').DataTable({
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

/* ── Confirm Delete ─────────────────────────────────────── */
function confirmDelete(id, name, nic) {
    Swal.fire({
        title: 'Ma Hubtaa Inaad Tirtirto?',
        html: `
            <div style="text-align:left;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px 18px;margin:10px 0;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <span style="font-size:11px;font-weight:700;color:#7f1d1d;text-transform:uppercase;letter-spacing:.5px;">Magaca Qofka</span>
                </div>
                <strong style="font-size:16px;color:#0f172a;">${name}</strong><br>
                <code style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:6px;font-size:13px;display:inline-block;margin-top:6px;">${nic}</code>
            </div>
            <p style="color:#ef4444;font-size:13px;font-weight:600;margin-top:10px;">
                <i class="fas fa-triangle-exclamation"></i>
                Ficilkani waa joogto ah — dib looma soo celin karo!
            </p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor:  '#64748b',
        confirmButtonText:  '<i class="fas fa-trash-alt"></i> Haa, Tirtir Hadda!',
        cancelButtonText:   '<i class="fas fa-times"></i> Jooji',
        reverseButtons: true,
        backdrop: 'rgba(15,23,42,.7)',
        customClass: {
            popup:         'swal-custom-popup',
            confirmButton: 'swal-confirm-btn',
            cancelButton:  'swal-cancel-btn',
        }
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Waa La Tirtirayo...',
                html: `<p style="color:#64748b;">Fadlan sug daqiiqad.</p>`,
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                timerProgressBar: true,
                timer: 1500,
                didOpen: () => Swal.showLoading()
            }).then(() => {
                window.location.href = 'Delete.php?delete=' + id;
            });
        }
    });
}

/* ── Lightbox preview ───────────────────────────────────── */
function previewImage(url, name) {
    Swal.fire({
        title: name, imageUrl: url, imageAlt: name,
        showCloseButton: true, showConfirmButton: false,
        background: '#fff', backdrop: 'rgba(15,23,42,.7)'
    });
}

/* ── Success toast after delete ─────────────────────────── */
<?php if ($deleted_success): ?>
Swal.fire({
    icon: 'success',
    title: '<span style="color:#059669;">Waa La Tirtiray!</span>',
    html:  `<p style="color:#374151;"><?= addslashes($deleted_msg) ?></p>`,
    confirmButtonColor: '#0052cc',
    confirmButtonText:  'Waayahay (OK)',
    timer: 5000,
    timerProgressBar: true,
    showCloseButton: true
});
<?php endif; ?>
</script>
</body>
</html>
