<?php
// sidebar.php - Shared sidebar included in all pages
// Usage: include('sidebar.php'); -- set $active_page before including
// $active_page options: 'dashboard', 'manage_users', 'update', 'delete', 'generate_nic'
if (!isset($active_page)) $active_page = '';
$admin_username = $_SESSION['username'] ?? 'Admin';
?>
<!-- Mobile Backdrop Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <!-- Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Kala bixi / Soo koob Liiska">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Header / Logo & Brand -->
    <a href="dashboard.php" class="sidebar-header" title="NIC System Dashboard">
        <div class="sidebar-logo" style="width: 46px; height: 46px; min-width: 46px; min-height: 46px; max-width: 46px; max-height: 46px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <img src="logo.png" alt="Somalia National ID Card Logo" width="46" height="46" style="width: 46px; height: 46px; min-width: 46px; min-height: 46px; max-width: 46px; max-height: 46px; object-fit: cover; border-radius: 50%; display: block;">
        </div>
        <div class="sidebar-brand">
            <span class="brand-name">NIC System</span>
            <span class="brand-sub"><i class="fas fa-shield-alt" style="color: #38bdf8; font-size: 10px; margin-right: 3px;"></i> Aqoonsiga Qaranka</span>
        </div>
    </a>

    <!-- Navigation List -->
    <nav class="sidebar-nav">
        <div class="nav-label">MAAMULKA GUUD</div>

        <a href="dashboard.php" class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>" title="Dashboard">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="manage_users.php" class="nav-item <?= $active_page === 'manage_users' ? 'active' : '' ?>" title="Manage Users">
            <span class="nav-icon"><i class="fas fa-users-cog"></i></span>
            <span class="nav-text">Manage Users</span>
        </a>

        <a href="Update.php" class="nav-item <?= $active_page === 'update' ? 'active' : '' ?>" title="Register / Update">
            <span class="nav-icon"><i class="fas fa-user-edit"></i></span>
            <span class="nav-text">Register / Update</span>
        </a>

        <a href="Delete.php" class="nav-item <?= $active_page === 'delete' ? 'active' : '' ?>" title="Delete Records">
            <span class="nav-icon"><i class="fas fa-trash-alt"></i></span>
            <span class="nav-text">Delete Records</span>
        </a>

        <a href="generate_nationalid.php" class="nav-item <?= $active_page === 'generate_nic' ? 'active' : '' ?>" title="Generate NIC">
            <span class="nav-icon"><i class="fas fa-id-card"></i></span>
            <span class="nav-text">Generate NIC</span>
        </a>

       
    </nav>

    <!-- Bottom: User Info + Logout -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar-wrap">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <span class="online-indicator" title="Online"></span>
            </div>
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars($admin_username) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
        <a href="logout.php" class="nav-item logout-btn" title="Ka bax nidaamka">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span class="nav-text">Ka Bax (Logout)</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('collapsed');
    if (main) main.classList.toggle('expanded');
    if (overlay) overlay.classList.toggle('active');
}
</script>
