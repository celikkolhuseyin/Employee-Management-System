<?php
require_once __DIR__ . '/../includes/auth.php';
require_report_access();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$departmentId = $_GET['department_id'] ?? '';
$status = $_GET['status'] ?? '';

$departments = $conn->query("SELECT id, name FROM departments ORDER BY name");

$sql = "
    SELECT
        e.id,
        e.employee_code,
        e.first_name,
        e.last_name,
        e.email,
        e.phone,
        e.salary,
        e.hire_date,
        e.is_active,
        d.name AS department_name,
        GROUP_CONCAT(r.name SEPARATOR ', ') AS roles,
        COUNT(w.id) AS work_record_count,
        COUNT(doc.id) AS document_count
    FROM employees e
    INNER JOIN departments d ON e.department_id = d.id
    LEFT JOIN employee_roles er ON er.employee_id = e.id
    LEFT JOIN roles r ON r.id = er.role_id
    LEFT JOIN work_records w ON w.employee_id = e.id
    LEFT JOIN employee_documents doc ON doc.employee_id = e.id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($departmentId !== '') {
    $sql .= " AND e.department_id = ?";
    $params[] = (int)$departmentId;
    $types .= 'i';
}

if ($status !== '') {
    $sql .= " AND e.is_active = ?";
    $params[] = (int)$status;
    $types .= 'i';
}

$sql .= "
    GROUP BY e.id
    ORDER BY d.name ASC, e.first_name ASC, e.last_name ASC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$rows = $stmt->get_result();

$totalEmployees = $rows->num_rows;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Reports / JOIN Operation</div>
        <h1 class="page-title">Employee JOIN Report</h1>
        <p class="page-subtitle">
            This report retrieves employee data using JOIN operations between employees, departments, roles, work records and documents.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="/employee-management-system/reports/department_report.php" class="btn btn-outline-primary">
            <i class="bi bi-database-check me-1"></i> Stored Procedure Report
        </a>

        <a href="/employee-management-system/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="stat-label">Database Feature</div>
            <div class="stat-value" style="font-size: 24px;">JOIN</div>
            <div class="stat-note">Multi-table relational query</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-table"></i>
            </div>
            <div class="stat-label">Tables Used</div>
            <div class="stat-value" style="font-size: 24px;">5 Tables</div>
            <div class="stat-note">employees, departments, roles, records, documents</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-label">Filtered Result</div>
            <div class="stat-value" style="font-size: 24px;"><?php echo e($totalEmployees); ?></div>
            <div class="stat-note">Employees listed</div>
        </div>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-5 col-md-6">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All Departments</option>

                <?php while ($department = $departments->fetch_assoc()): ?>
                    <option value="<?php echo e($department['id']); ?>"
                        <?php echo ((string)$departmentId === (string)$department['id']) ? 'selected' : ''; ?>>
                        <?php echo e($department['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="col-lg-4 col-md-12 d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Filter Report
            </button>

            <a href="join_report.php" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="card-modern">
    <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">JOIN Query Result</h5>
            <div class="text-muted small">
                This result is generated by INNER JOIN and LEFT JOIN operations.
            </div>
        </div>

        <span class="badge rounded-pill text-bg-success">
            JOIN Operation Active
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Roles</th>
                    <th>Work Records</th>
                    <th>Documents</th>
                    <th>Salary</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-search d-block fs-2 mb-2"></i>
                            No JOIN report data found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php echo e(strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1))); ?>
                                </div>

                                <div>
                                    <strong><?php echo e($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                    <small><?php echo e($row['employee_code'] . ' · ' . $row['email']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td><?php echo e($row['department_name']); ?></td>

                        <td>
                            <?php if (!empty($row['roles'])): ?>
                                <?php echo e($row['roles']); ?>
                            <?php else: ?>
                                <span class="text-muted">No role assigned</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge rounded-pill text-bg-light border">
                                <?php echo e($row['work_record_count']); ?> records
                            </span>
                        </td>

                        <td>
                            <span class="badge rounded-pill text-bg-light border">
                                <?php echo e($row['document_count']); ?> files
                            </span>
                        </td>

                        <td>
                            <strong><?php echo e(number_format((float)$row['salary'], 2)); ?> ₺</strong>
                        </td>

                        <td>
                            <?php if ((int)$row['is_active'] === 1): ?>
                                <span class="status-pill status-active">Active</span>
                            <?php else: ?>
                                <span class="status-pill status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card-modern p-4 mt-4">
    <h5 class="fw-bold mb-2">JOIN query used in this report</h5>
    <p class="text-muted mb-3">
        The report joins employee records with departments, roles, work records and uploaded documents.
        This demonstrates relational data retrieval from normalized MySQL tables.
    </p>

    <pre class="sql-box"><code>SELECT e.employee_code, e.first_name, e.last_name, d.name AS department_name,
       GROUP_CONCAT(r.name SEPARATOR ', ') AS roles,
       COUNT(w.id) AS work_record_count,
       COUNT(doc.id) AS document_count
FROM employees e
INNER JOIN departments d ON e.department_id = d.id
LEFT JOIN employee_roles er ON er.employee_id = e.id
LEFT JOIN roles r ON r.id = er.role_id
LEFT JOIN work_records w ON w.employee_id = e.id
LEFT JOIN employee_documents doc ON doc.employee_id = e.id
GROUP BY e.id;</code></pre>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>