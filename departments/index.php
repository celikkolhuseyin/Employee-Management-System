<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort_by'] ?? 'newest';

$orderBy = 'd.id DESC';

switch ($sortBy) {
    case 'oldest':
        $orderBy = 'd.id ASC';
        break;
    case 'name_az':
        $orderBy = 'd.name ASC';
        break;
    case 'name_za':
        $orderBy = 'd.name DESC';
        break;
    case 'most_employees':
        $orderBy = 'employee_count DESC, d.name ASC';
        break;
    case 'highest_salary':
        $orderBy = 'average_salary DESC, d.name ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'd.id DESC';
        break;
}

$sql = "
    SELECT 
        d.id,
        d.name,
        d.description,
        COUNT(e.id) AS employee_count,
        SUM(CASE WHEN e.is_active = 1 THEN 1 ELSE 0 END) AS active_count,
        COALESCE(AVG(e.salary), 0) AS average_salary
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id
    WHERE d.name LIKE ? OR d.description LIKE ?
    GROUP BY d.id
    ORDER BY $orderBy
";

$like = '%' . $search . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$departments = $stmt->get_result();

$totalDepartments = $conn->query("SELECT COUNT(*) AS c FROM departments")->fetch_assoc()['c'];
$totalEmployees = $conn->query("SELECT COUNT(*) AS c FROM employees")->fetch_assoc()['c'];
$activeDepartments = $conn->query("
    SELECT COUNT(*) AS c
    FROM departments d
    WHERE EXISTS (
        SELECT 1 FROM employees e WHERE e.department_id = d.id
    )
")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Organization / Departments</div>
        <h1 class="page-title">Departments</h1>
        <p class="page-subtitle">
            Manage organizational departments and view employee distribution.
        </p>
    </div>

    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Department
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="stat-label">Total Departments</div>
            <div class="stat-value"><?php echo e($totalDepartments); ?></div>
            <div class="stat-note">Organizational units</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value"><?php echo e($totalEmployees); ?></div>
            <div class="stat-note">Assigned to departments</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="stat-label">Active Departments</div>
            <div class="stat-value"><?php echo e($activeDepartments); ?></div>
            <div class="stat-note">Departments with employees</div>
        </div>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-5">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by department name or description..."
                    value="<?php echo e($search); ?>"
                >
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label">Sort By</label>
            <select name="sort_by" class="form-select">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="name_az" <?php echo $sortBy === 'name_az' ? 'selected' : ''; ?>>Name A-Z</option>
                <option value="name_za" <?php echo $sortBy === 'name_za' ? 'selected' : ''; ?>>Name Z-A</option>
                <option value="most_employees" <?php echo $sortBy === 'most_employees' ? 'selected' : ''; ?>>Most Employees</option>
                <option value="highest_salary" <?php echo $sortBy === 'highest_salary' ? 'selected' : ''; ?>>Highest Average Salary</option>
            </select>
        </div>

        <div class="col-lg-4 col-md-6 d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Apply
            </button>

            <a href="index.php" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="card-modern">
    <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Department List</h5>
            <div class="text-muted small">
                JOIN: departments + employees for employee count and average salary.
            </div>
        </div>

        <span class="badge rounded-pill text-bg-light border">
            <?php echo e($departments->num_rows); ?> departments listed
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employees</th>
                    <th>Active Employees</th>
                    <th>Average Salary</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($departments->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-diagram-3 d-block fs-2 mb-2"></i>
                            No departments found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($d = $departments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php echo e(strtoupper(substr($d['name'], 0, 2))); ?>
                                </div>

                                <div>
                                    <strong><?php echo e($d['name']); ?></strong>
                                    <small>Department ID: <?php echo e($d['id']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge rounded-pill text-bg-light border">
                                <?php echo e($d['employee_count']); ?> employees
                            </span>
                        </td>

                        <td>
                            <span class="status-pill status-active">
                                <?php echo e($d['active_count'] ?? 0); ?> active
                            </span>
                        </td>

                        <td>
                            <strong><?php echo e(number_format((float)$d['average_salary'], 2)); ?> ₺</strong>
                        </td>

                        <td>
                            <?php echo e($d['description'] ?: '-'); ?>
                        </td>

                        <td>
                            <div class="action-buttons justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?php echo e($d['id']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form class="delete-form" method="post" action="delete.php">
                                    <input type="hidden" name="id" value="<?php echo e($d['id']); ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>