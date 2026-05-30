<?php
require_once __DIR__ . '/../includes/auth.php';
require_manager_or_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$status = $_GET['status'] ?? '';
$date = $_GET['work_date'] ?? '';
$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort_by'] ?? 'newest_date';

$orderBy = 'wr.work_date DESC, wr.id DESC';

switch ($sortBy) {
    case 'oldest_date':
        $orderBy = 'wr.work_date ASC, wr.id ASC';
        break;
    case 'employee_az':
        $orderBy = 'e.first_name ASC, e.last_name ASC, wr.work_date DESC';
        break;
    case 'employee_za':
        $orderBy = 'e.first_name DESC, e.last_name DESC, wr.work_date DESC';
        break;
    case 'status_az':
        $orderBy = 'wr.status ASC, wr.work_date DESC';
        break;
    case 'department_az':
        $orderBy = 'd.name ASC, e.first_name ASC';
        break;
    case 'newest_date':
    default:
        $orderBy = 'wr.work_date DESC, wr.id DESC';
        break;
}

$sql = "
    SELECT 
        wr.*,
        CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
        e.employee_code,
        e.email,
        d.name AS department_name
    FROM work_records wr
    INNER JOIN employees e ON wr.employee_id = e.id
    INNER JOIN departments d ON e.department_id = d.id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($status !== '') {
    $sql .= " AND wr.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($date !== '') {
    $sql .= " AND wr.work_date = ?";
    $params[] = $date;
    $types .= 's';
}

if ($search !== '') {
    $sql .= " AND (
        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR e.email LIKE ?
        OR d.name LIKE ?
    )";

    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sssss';
}

$sql .= " ORDER BY $orderBy";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$rows = $stmt->get_result();

$totalToday = $conn->query("SELECT COUNT(*) AS c FROM work_records WHERE work_date = CURDATE()")->fetch_assoc()['c'];
$totalPresent = $conn->query("SELECT COUNT(*) AS c FROM work_records WHERE status = 'Present'")->fetch_assoc()['c'];
$totalRemote = $conn->query("SELECT COUNT(*) AS c FROM work_records WHERE status = 'Remote'")->fetch_assoc()['c'];
$totalLeave = $conn->query("SELECT COUNT(*) AS c FROM work_records WHERE status IN ('On Leave', 'Sick Leave', 'Absent')")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Work Records / Attendance</div>
        <h1 class="page-title">Work Records</h1>
        <p class="page-subtitle">
            Track employee attendance, remote work, leave and daily work status.
        </p>
    </div>

    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Work Record
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div class="stat-label">Today Records</div>
            <div class="stat-value"><?php echo e($totalToday); ?></div>
            <div class="stat-note">Records for current date</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-label">Present</div>
            <div class="stat-value"><?php echo e($totalPresent); ?></div>
            <div class="stat-note">On-site employees</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-laptop"></i>
            </div>
            <div class="stat-label">Remote</div>
            <div class="stat-value"><?php echo e($totalRemote); ?></div>
            <div class="stat-note">Remote work records</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div class="stat-label">Leave / Absent</div>
            <div class="stat-value"><?php echo e($totalLeave); ?></div>
            <div class="stat-note">Leave-related records</div>
        </div>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-3">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    name="search" 
                    class="form-control" 
                    placeholder="Search employee or department..."
                    value="<?php echo e($search); ?>"
                >
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="work_date" class="form-control" value="<?php echo e($date); ?>">
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <?php foreach (['Present', 'Remote', 'On Leave', 'Sick Leave', 'Absent'] as $s): ?>
                    <option value="<?php echo e($s); ?>" <?php echo $status === $s ? 'selected' : ''; ?>>
                        <?php echo e($s); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-3 col-md-4">
            <label class="form-label">Sort By</label>
            <select name="sort_by" class="form-select">
                <option value="newest_date" <?php echo $sortBy === 'newest_date' ? 'selected' : ''; ?>>Newest Date First</option>
                <option value="oldest_date" <?php echo $sortBy === 'oldest_date' ? 'selected' : ''; ?>>Oldest Date First</option>
                <option value="employee_az" <?php echo $sortBy === 'employee_az' ? 'selected' : ''; ?>>Employee Name A-Z</option>
                <option value="employee_za" <?php echo $sortBy === 'employee_za' ? 'selected' : ''; ?>>Employee Name Z-A</option>
                <option value="status_az" <?php echo $sortBy === 'status_az' ? 'selected' : ''; ?>>Status A-Z</option>
                <option value="department_az" <?php echo $sortBy === 'department_az' ? 'selected' : ''; ?>>Department A-Z</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-12 d-flex gap-2">
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
            <h5 class="fw-bold mb-1">Attendance Records</h5>
            <div class="text-muted small">
                JOIN: work_records + employees + departments.
            </div>
        </div>

        <span class="badge rounded-pill text-bg-light border">
            <?php echo e($rows->num_rows); ?> records listed
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x d-block fs-2 mb-2"></i>
                            No work records found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($r = $rows->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php
                                        $parts = explode(' ', $r['employee_name']);
                                        echo e(strtoupper(substr($parts[0] ?? 'E', 0, 1) . substr($parts[1] ?? 'M', 0, 1)));
                                    ?>
                                </div>

                                <div>
                                    <strong><?php echo e($r['employee_name']); ?></strong>
                                    <small><?php echo e($r['employee_code'] . ' · ' . $r['email']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td><strong><?php echo e($r['work_date']); ?></strong></td>

                        <td><?php echo e($r['department_name']); ?></td>

                        <td>
                            <?php
                                $class = 'status-pill status-inactive';
                                if ($r['status'] === 'Present' || $r['status'] === 'Remote') {
                                    $class = 'status-pill status-active';
                                }
                            ?>
                            <span class="<?php echo e($class); ?>">
                                <?php echo e($r['status']); ?>
                            </span>
                        </td>

                        <td><?php echo e($r['notes'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>