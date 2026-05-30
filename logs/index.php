<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action_type'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT 
        l.id,
        l.employee_id,
        l.action_type,
        l.log_message,
        l.created_at,
        e.employee_code,
        e.first_name,
        e.last_name,
        e.email
    FROM employee_logs l
    LEFT JOIN employees e ON e.id = l.employee_id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($action !== '') {
    $sql .= " AND l.action_type = ?";
    $params[] = $action;
    $types .= 's';
}

if ($search !== '') {
    $sql .= " AND (
        l.log_message LIKE ?
        OR e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR e.email LIKE ?
    )";

    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sssss';
}

$sql .= " ORDER BY l.created_at DESC, l.id DESC LIMIT 150";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$logs = $stmt->get_result();

$totalLogs = $conn->query("SELECT COUNT(*) AS c FROM employee_logs")->fetch_assoc()['c'];
$insertLogs = $conn->query("SELECT COUNT(*) AS c FROM employee_logs WHERE action_type = 'INSERT'")->fetch_assoc()['c'];
$updateLogs = $conn->query("SELECT COUNT(*) AS c FROM employee_logs WHERE action_type = 'UPDATE'")->fetch_assoc()['c'];
$deleteLogs = $conn->query("SELECT COUNT(*) AS c FROM employee_logs WHERE action_type = 'DELETE'")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Database / Trigger Logs</div>
        <h1 class="page-title">Employee Logs</h1>
        <p class="page-subtitle">
            Employee insert, update and delete operations are recorded automatically by MySQL triggers.
        </p>
    </div>

    <a href="/employee-management-system/employees/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Employees
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card log-stat-card">
            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            <div class="stat-label">Total Logs</div>
            <div class="stat-value"><?php echo e($totalLogs); ?></div>
            <div class="stat-note">All trigger records</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card log-stat-card">
            <div class="stat-icon"><i class="bi bi-plus-circle"></i></div>
            <div class="stat-label">Insert Logs</div>
            <div class="stat-value"><?php echo e($insertLogs); ?></div>
            <div class="stat-note">After insert trigger</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card log-stat-card">
            <div class="stat-icon"><i class="bi bi-pencil-square"></i></div>
            <div class="stat-label">Update Logs</div>
            <div class="stat-value"><?php echo e($updateLogs); ?></div>
            <div class="stat-note">After update trigger</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card log-stat-card">
            <div class="stat-icon"><i class="bi bi-trash3"></i></div>
            <div class="stat-label">Delete Logs</div>
            <div class="stat-value"><?php echo e($deleteLogs); ?></div>
            <div class="stat-note">Before delete trigger</div>
        </div>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    class="form-control" 
                    name="search" 
                    value="<?php echo e($search); ?>"
                    placeholder="Search by employee name, code, email or log message..."
                >
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label">Action Type</label>
            <select name="action_type" class="form-select">
                <option value="">All Actions</option>
                <option value="INSERT" <?php echo $action === 'INSERT' ? 'selected' : ''; ?>>INSERT</option>
                <option value="UPDATE" <?php echo $action === 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                <option value="DELETE" <?php echo $action === 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
            </select>
        </div>

        <div class="col-lg-3 col-md-6 d-flex gap-2">
            <button class="btn btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>

            <a href="index.php" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="card-modern">
    <div class="p-4 border-bottom">
        <h5 class="fw-bold mb-1">Trigger Activity Records</h5>
        <div class="text-muted small">
            This table reads records generated by MySQL triggers on the employees table.
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Action</th>
                    <th>Employee</th>
                    <th>Message</th>
                    <th>Date / Time</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($logs->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-database-x d-block fs-2 mb-2"></i>
                            No log records found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-semibold">
                            <?php echo e($log['id']); ?>
                        </td>

                        <td>
                            <?php
                                $badgeClass = 'log-badge log-badge-default';

                                if ($log['action_type'] === 'INSERT') {
                                    $badgeClass = 'log-badge log-badge-insert';
                                } elseif ($log['action_type'] === 'UPDATE') {
                                    $badgeClass = 'log-badge log-badge-update';
                                } elseif ($log['action_type'] === 'DELETE') {
                                    $badgeClass = 'log-badge log-badge-delete';
                                }
                            ?>

                            <span class="<?php echo e($badgeClass); ?>">
                                <?php echo e($log['action_type']); ?>
                            </span>
                        </td>

                        <td>
                            <?php if (!empty($log['employee_code'])): ?>
                                <div class="employee-name">
                                    <div class="employee-avatar">
                                        <?php echo e(strtoupper(substr($log['first_name'], 0, 1) . substr($log['last_name'], 0, 1))); ?>
                                    </div>

                                    <div>
                                        <strong><?php echo e($log['first_name'] . ' ' . $log['last_name']); ?></strong>
                                        <small><?php echo e($log['employee_code'] . ' · ' . $log['email']); ?></small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Employee record no longer exists</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php echo e($log['log_message']); ?>
                        </td>

                        <td>
                            <?php echo e($log['created_at']); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>