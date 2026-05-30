<?php
$currentPath = $_SERVER['PHP_SELF'] ?? '';

function is_active_menu($keyword) {
    global $currentPath;
    return str_contains($currentPath, $keyword) ? 'active' : '';
}

$fullName = $_SESSION['full_name'] ?? 'Admin User';
$userType = $_SESSION['user_type'] ?? 'Administrator';
?>

<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="brand-title">EMS</div>
                <div class="brand-subtitle">Employee Management System</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a class="sidebar-link <?php echo is_active_menu('dashboard'); ?>" href="/employee-management-system/dashboard.php">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <a class="sidebar-link <?php echo is_active_menu('employees'); ?>" href="/employee-management-system/employees/index.php">
                <i class="bi bi-person-badge"></i>
                <span>Employees</span>
            </a>

            <?php if (is_admin()): ?>
                <a class="sidebar-link <?php echo is_active_menu('departments'); ?>" href="/employee-management-system/departments/index.php">
                    <i class="bi bi-diagram-3"></i>
                    <span>Departments</span>
                </a>

                <a class="sidebar-link <?php echo is_active_menu('roles'); ?>" href="/employee-management-system/roles/index.php">
                    <i class="bi bi-shield-check"></i>
                    <span>Roles</span>
                </a>
            <?php endif; ?>

            <?php if (is_admin() || is_manager()): ?>
                <a class="sidebar-link <?php echo is_active_menu('attendance'); ?>" href="/employee-management-system/attendance/index.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>Work Records</span>
                </a>

                <a class="sidebar-link <?php echo is_active_menu('documents'); ?>" href="/employee-management-system/documents/index.php">
                    <i class="bi bi-folder2-open"></i>
                    <span>Documents</span>
                </a>

                <a class="sidebar-link <?php echo is_active_menu('reports'); ?>" href="/employee-management-system/reports/department_report.php">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports</span>
                </a>
            <?php endif; ?>

            <?php if (is_admin()): ?>
                <a class="sidebar-link <?php echo is_active_menu('logs'); ?>" href="/employee-management-system/logs/index.php">
                    <i class="bi bi-clock-history"></i>
                    <span>Logs</span>
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="system-status">
                <span class="status-dot"></span>
                <div>
                    <strong><?php echo e($userType); ?> Access</strong>
                    <small>Session active</small>
                </div>
            </div>

            <a class="logout-link" href="/employee-management-system/logout.php">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-wrapper">
        <header class="topbar">
            <button class="sidebar-toggle" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-title">
                <strong>Employee Management System</strong>
                <small>Role-based session: <?php echo e($userType); ?></small>
            </div>

            <div class="topbar-actions">
                <div class="user-chip">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($fullName, 0, 1)); ?>
                    </div>
                    <div class="user-meta">
                        <strong><?php echo e($fullName); ?></strong>
                        <small><?php echo e($userType); ?></small>
                    </div>
                </div>
            </div>
        </header>

        <section class="app-content">