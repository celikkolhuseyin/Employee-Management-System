<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$sortBy = $_GET['sort_by'] ?? 'newest';

$orderBy = 'r.id DESC';

switch ($sortBy) {
    case 'oldest':
        $orderBy = 'r.id ASC';
        break;
    case 'name_az':
        $orderBy = 'r.name ASC';
        break;
    case 'name_za':
        $orderBy = 'r.name DESC';
        break;
    case 'most_assigned':
        $orderBy = 'assigned_count DESC, r.name ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'r.id DESC';
        break;
}

$sql = "
    SELECT 
        r.id,
        r.name,
        r.description,
        COUNT(er.employee_id) AS assigned_count
    FROM roles r
    LEFT JOIN employee_roles er ON er.role_id = r.id
    WHERE r.name LIKE ? OR r.description LIKE ?
    GROUP BY r.id
    ORDER BY $orderBy
";

$like = '%' . $search . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$roles = $stmt->get_result();

$totalRoles = $conn->query("SELECT COUNT(*) AS c FROM roles")->fetch_assoc()['c'];
$assignedRoles = $conn->query("
    SELECT COUNT(DISTINCT role_id) AS c 
    FROM employee_roles
")->fetch_assoc()['c'];
$totalAssignments = $conn->query("SELECT COUNT(*) AS c FROM employee_roles")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Authorization / Roles</div>
        <h1 class="page-title">Roles</h1>
        <p class="page-subtitle">
            Manage employee role definitions and role assignments.
        </p>
    </div>

    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Role
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-label">Total Roles</div>
            <div class="stat-value"><?php echo e($totalRoles); ?></div>
            <div class="stat-note">Defined role records</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-label">Assigned Roles</div>
            <div class="stat-value"><?php echo e($assignedRoles); ?></div>
            <div class="stat-note">Used in employee assignments</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-link-45deg"></i>
            </div>
            <div class="stat-label">Total Assignments</div>
            <div class="stat-value"><?php echo e($totalAssignments); ?></div>
            <div class="stat-note">employee_roles records</div>
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
                    placeholder="Search by role name or description..."
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
                <option value="most_assigned" <?php echo $sortBy === 'most_assigned' ? 'selected' : ''; ?>>Most Assigned</option>
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
            <h5 class="fw-bold mb-1">Role List</h5>
            <div class="text-muted small">
                JOIN: roles + employee_roles for assignment count.
            </div>
        </div>

        <span class="badge rounded-pill text-bg-light border">
            <?php echo e($roles->num_rows); ?> roles listed
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Assigned Employees</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($roles->num_rows === 0): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-shield-x d-block fs-2 mb-2"></i>
                            No roles found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($r = $roles->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php echo e(strtoupper(substr($r['name'], 0, 2))); ?>
                                </div>

                                <div>
                                    <strong><?php echo e($r['name']); ?></strong>
                                    <small>Role ID: <?php echo e($r['id']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge rounded-pill text-bg-light border">
                                <?php echo e($r['assigned_count']); ?> employees
                            </span>
                        </td>

                        <td>
                            <?php echo e($r['description'] ?: '-'); ?>
                        </td>

                        <td>
                            <div class="action-buttons justify-content-end">
                                <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?php echo e($r['id']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form class="delete-form" method="post" action="delete.php">
                                    <input type="hidden" name="id" value="<?php echo e($r['id']); ?>">
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