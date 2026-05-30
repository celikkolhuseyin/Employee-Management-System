<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc();

if (!$role) {
    set_flash('danger', 'Role not found.');
    redirect('index.php');
}

$countStmt = $conn->prepare("SELECT COUNT(*) AS c FROM employee_roles WHERE role_id = ?");
$countStmt->bind_param('i', $id);
$countStmt->execute();
$assignedCount = $countStmt->get_result()->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Authorization / Roles / Edit</div>
        <h1 class="page-title">Edit Role</h1>
        <p class="page-subtitle">
            Update role name and description.
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Roles
    </a>
</div>

<form method="post" action="update.php">
    <input type="hidden" name="id" value="<?php echo e($role['id']); ?>">

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-modern p-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">Role Information</h5>
                    <div class="text-muted small">
                        Changes will affect employee role listings and reports.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label required">Role Name</label>
                        <input 
                            name="name" 
                            class="form-control" 
                            required
                            value="<?php echo e($role['name']); ?>"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea 
                            name="description" 
                            class="form-control" 
                            rows="5"
                        ><?php echo e($role['description']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-modern p-4 sticky-side-card">
                <h5 class="fw-bold mb-3">Role Summary</h5>

                <div class="profile-preview">
                    <div class="profile-preview-placeholder">
                        <i class="bi bi-shield"></i>
                    </div>

                    <div>
                        <strong><?php echo e($role['name']); ?></strong>
                        <small><?php echo e($assignedCount); ?> employee assignments</small>
                    </div>
                </div>

                <hr class="my-4">

                <p class="text-muted">
                    This role may be assigned to multiple employees using the employee_roles table.
                </p>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>

                    <a href="index.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>