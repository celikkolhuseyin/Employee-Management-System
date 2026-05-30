<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$employeeCount = $conn->query('SELECT COUNT(*) c FROM employees')->fetch_assoc()['c'];
$departmentCount = $conn->query('SELECT COUNT(*) c FROM departments')->fetch_assoc()['c'];
$activeCount = $conn->query('SELECT COUNT(*) c FROM employees WHERE is_active = 1')->fetch_assoc()['c'];
$todayRecords = $conn->query("SELECT COUNT(*) c FROM work_records WHERE work_date = CURDATE()")->fetch_assoc()['c'];
$documentCount = $conn->query('SELECT COUNT(*) c FROM employee_documents')->fetch_assoc()['c'];

$recent = $conn->query("
    SELECT 
        e.id,
        e.employee_code,
        e.first_name,
        e.last_name,
        e.email,
        e.is_active,
        d.name AS department_name,
        GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
    FROM employees e
    INNER JOIN departments d ON e.department_id = d.id
    LEFT JOIN employee_roles er ON er.employee_id = e.id
    LEFT JOIN roles r ON r.id = er.role_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
    LIMIT 5
");

$departmentStats = $conn->query("
    SELECT d.name, COUNT(e.id) AS total
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id
    GROUP BY d.id
    ORDER BY total DESC
    LIMIT 6
");

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Dashboard / Overview</div>
        <h1 class="page-title">Employee Overview</h1>
        <p class="page-subtitle">Real-time summary of employees, departments, work records and documents.</p>
    </div>

    <a href="/employee-management-system/employees/create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Employee
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-modern stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value"><?php echo e($employeeCount); ?></div>
            <div class="stat-note"><i class="bi bi-arrow-up-right"></i> Employee records</div>
        </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-modern stat-card">
            <div class="stat-icon"><i class="bi bi-person-check"></i></div>
            <div class="stat-label">Active Employees</div>
            <div class="stat-value"><?php echo e($activeCount); ?></div>
            <div class="stat-note"><i class="bi bi-check-circle"></i> Currently active</div>
        </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-modern stat-card">
            <div class="stat-icon"><i class="bi bi-diagram-3"></i></div>
            <div class="stat-label">Departments</div>
            <div class="stat-value"><?php echo e($departmentCount); ?></div>
            <div class="stat-note">3NF relation table</div>
        </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-modern stat-card">
            <div class="stat-icon"><i class="bi bi-calendar2-check"></i></div>
            <div class="stat-label">Today Work Records</div>
            <div class="stat-value"><?php echo e($todayRecords); ?></div>
            <div class="stat-note">Attendance tracking</div>
        </div>
    </div>

    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-modern stat-card">
            <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
            <div class="stat-label">Uploaded Documents</div>
            <div class="stat-value"><?php echo e($documentCount); ?></div>
            <div class="stat-note">File upload module</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card-modern p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Attendance Trend</h5>
                    <div class="text-muted small">Visual dashboard area for work record tracking</div>
                </div>
                <span class="badge text-bg-light border">Today</span>
            </div>

            <div class="chart-placeholder">
                <div class="chart-line"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-modern p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1 fw-bold">Employees by Department</h5>
                    <div class="text-muted small">JOIN-based department summary</div>
                </div>
                <a href="/employee-management-system/reports/department_report.php" class="btn btn-sm btn-outline-primary">Full Report</a>
            </div>

            <div class="mini-list">
                <?php while ($dept = $departmentStats->fetch_assoc()): ?>
                    <div class="mini-list-item">
                        <div>
                            <strong><?php echo e($dept['name']); ?></strong>
                            <div class="text-muted small">Department</div>
                        </div>
                        <span class="badge rounded-pill text-bg-success"><?php echo e($dept['total']); ?> employees</span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="mb-1 fw-bold">Recent Employees</h5>
            <div class="text-muted small">This table uses JOIN with departments and roles.</div>
        </div>

        <a href="/employee-management-system/employees/index.php" class="btn btn-outline-primary">
            View All Employees
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <?php echo employee_avatar_html($row); ?>
                                <div>
                                    <strong><?php echo e($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                    <small><?php echo e($row['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo e($row['employee_code']); ?></td>
                        <td><?php echo e($row['department_name']); ?></td>
                        <td><?php echo e($row['roles'] ?? '-'); ?></td>
                        <td>
                            <?php if ((int)$row['is_active'] === 1): ?>
                                <span class="status-pill status-active">Active</span>
                            <?php else: ?>
                                <span class="status-pill status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/employee-management-system/employees/show.php?id=<?php echo e($row['id']); ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>