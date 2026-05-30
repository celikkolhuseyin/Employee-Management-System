<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$departmentId = $_GET['department_id'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';

$orderBy = 'e.id DESC';

switch ($sortBy) {
    case 'oldest':
        $orderBy = 'e.id ASC';
        break;
    case 'name_az':
        $orderBy = 'e.first_name ASC, e.last_name ASC';
        break;
    case 'name_za':
        $orderBy = 'e.first_name DESC, e.last_name DESC';
        break;
    case 'department_az':
        $orderBy = 'd.name ASC, e.first_name ASC';
        break;
    case 'highest_salary':
        $orderBy = 'e.salary DESC, e.first_name ASC';
        break;
    case 'active_first':
        $orderBy = 'e.is_active DESC, e.first_name ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'e.id DESC';
        break;
}

$departments = $conn->query("SELECT id, name FROM departments ORDER BY name");

$sql = "
    SELECT 
        e.*,
        d.name AS department_name,
        GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
    FROM employees e
    INNER JOIN departments d ON e.department_id = d.id
    LEFT JOIN employee_roles er ON er.employee_id = e.id
    LEFT JOIN roles r ON r.id = er.role_id
    WHERE CONCAT(
        e.employee_code, ' ',
        e.first_name, ' ',
        e.last_name, ' ',
        e.email, ' ',
        e.phone
    ) LIKE ?
";

$params = ['%' . $search . '%'];
$types = 's';

if ($status !== '') {
    $sql .= " AND e.is_active = ?";
    $params[] = (int)$status;
    $types .= 'i';
}

if ($departmentId !== '') {
    $sql .= " AND e.department_id = ?";
    $params[] = (int)$departmentId;
    $types .= 'i';
}

$sql .= "
    GROUP BY e.id
    ORDER BY $orderBy
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$employees = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Employees / Directory</div>
        <h1 class="page-title">Employees</h1>
        <p class="page-subtitle">Manage employee records, departments, roles and active status.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-primary" href="/employee-management-system/reports/department_report.php">
            <i class="bi bi-bar-chart-line me-1"></i> Reports
        </a>

        <?php if (is_admin()): ?>
            <a class="btn btn-primary" href="create.php">
                <i class="bi bi-plus-lg me-1"></i> Add Employee
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>

                <input 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by name, code, email or phone..."
                    value="<?php echo e($search); ?>"
                >
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All Departments</option>

                <?php while ($department = $departments->fetch_assoc()): ?>
                    <option 
                        value="<?php echo e($department['id']); ?>"
                        <?php echo ((string)$departmentId === (string)$department['id']) ? 'selected' : ''; ?>
                    >
                        <?php echo e($department['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Sort By</label>
            <select name="sort_by" class="form-select">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="name_az" <?php echo $sortBy === 'name_az' ? 'selected' : ''; ?>>Name A-Z</option>
                <option value="name_za" <?php echo $sortBy === 'name_za' ? 'selected' : ''; ?>>Name Z-A</option>
                <option value="department_az" <?php echo $sortBy === 'department_az' ? 'selected' : ''; ?>>Department A-Z</option>
                <option value="highest_salary" <?php echo $sortBy === 'highest_salary' ? 'selected' : ''; ?>>Highest Salary</option>
                <option value="active_first" <?php echo $sortBy === 'active_first' ? 'selected' : ''; ?>>Active First</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-12 d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Apply
            </button>

            <a class="btn btn-outline-secondary" href="index.php">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="card-modern">
    <div class="p-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Employee Directory</h5>
            <div class="text-muted small">JOIN: employees + departments + employee_roles + roles</div>
        </div>

        <?php if (is_admin()): ?>
            <a class="btn btn-primary btn-sm" href="create.php">
                <i class="bi bi-plus-lg me-1"></i> New Employee
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
               <tr>
    <th>Employee</th>
    <th>Code</th>
    <th>Department</th>
    <th>Roles</th>
    <th>Phone</th>
    <th>Salary</th>
    <th>Status</th>
    <th class="text-end">Actions</th>
</tr>
            </thead>

            <tbody>
                <?php if ($employees->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-search d-block fs-2 mb-2"></i>
                            No employees found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($e = $employees->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php echo e(strtoupper(substr($e['first_name'], 0, 1) . substr($e['last_name'], 0, 1))); ?>
                                </div>

                                <div>
                                    <strong><?php echo e($e['first_name'] . ' ' . $e['last_name']); ?></strong>
                                    <small><?php echo e($e['email']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="fw-semibold">
                                <?php echo e($e['employee_code']); ?>
                            </span>
                        </td>

                        <td><?php echo e($e['department_name']); ?></td>

                        <td>
                            <?php if (!empty($e['roles'])): ?>
                                <?php echo e($e['roles']); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                        <td><?php echo e($e['phone']); ?></td>

<td>
    <strong><?php echo e(number_format((float)$e['salary'], 2)); ?> ₺</strong>
</td>

<td>
    <?php if ((int)$e['is_active'] === 1): ?>
        <span class="status-pill status-active">Active</span>
    <?php else: ?>
        <span class="status-pill status-inactive">Inactive</span>
    <?php endif; ?>
</td>

                        <td>
                            <div class="action-buttons justify-content-end">
                                <a 
                                    class="btn btn-sm btn-outline-primary" 
                                    href="show.php?id=<?php echo e($e['id']); ?>" 
                                    title="View"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <?php if (is_admin()): ?>
                                    <a 
                                        class="btn btn-sm btn-outline-secondary" 
                                        href="edit.php?id=<?php echo e($e['id']); ?>" 
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form class="delete-form" method="post" action="delete.php">
                                        <input type="hidden" name="id" value="<?php echo e($e['id']); ?>">

                                        <button 
                                            class="btn btn-sm btn-outline-danger" 
                                            type="submit" 
                                            title="Delete"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>