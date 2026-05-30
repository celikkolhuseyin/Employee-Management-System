<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$department = $stmt->get_result()->fetch_assoc();

if (!$department) {
    set_flash('danger', 'Department not found.');
    redirect('index.php');
}

$countStmt = $conn->prepare("SELECT COUNT(*) AS c FROM employees WHERE department_id = ?");
$countStmt->bind_param('i', $id);
$countStmt->execute();
$employeeCount = $countStmt->get_result()->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Organization / Departments / Edit</div>
        <h1 class="page-title">Edit Department</h1>
        <p class="page-subtitle">
            Update department name and description.
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Departments
    </a>
</div>

<form method="post" action="update.php">
    <input type="hidden" name="id" value="<?php echo e($department['id']); ?>">

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-modern p-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">Department Information</h5>
                    <div class="text-muted small">
                        Changes affect employee department listings and reports.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label required">Department Name</label>
                        <input 
                            name="name" 
                            class="form-control" 
                            required
                            value="<?php echo e($department['name']); ?>"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea 
                            name="description" 
                            class="form-control" 
                            rows="5"
                        ><?php echo e($department['description']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-modern p-4 sticky-side-card">
                <h5 class="fw-bold mb-3">Department Summary</h5>

                <div class="profile-preview">
                    <div class="profile-preview-placeholder">
                        <i class="bi bi-building"></i>
                    </div>

                    <div>
                        <strong><?php echo e($department['name']); ?></strong>
                        <small><?php echo e($employeeCount); ?> employees assigned</small>
                    </div>
                </div>

                <hr class="my-4">

                <p class="text-muted">
                    If this department is assigned to employees, it should not be deleted
                    unless those employees are reassigned first.
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