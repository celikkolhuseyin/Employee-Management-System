<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    set_flash('danger', 'Employee not found.');
    redirect('index.php');
}

$selectedRoles = [];
$rs = $conn->prepare('SELECT role_id FROM employee_roles WHERE employee_id = ?');
$rs->bind_param('i', $id);
$rs->execute();
$rr = $rs->get_result();

while ($row = $rr->fetch_assoc()) {
    $selectedRoles[] = $row['role_id'];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Employees / Edit</div>
        <h1 class="page-title">Edit Employee</h1>
        <p class="page-subtitle">Update employee information, profile photo, department, roles and documents.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="show.php?id=<?php echo e($id); ?>" class="btn btn-outline-primary">
            <i class="bi bi-eye me-1"></i> View Profile
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<form method="post" action="update.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo e($id); ?>">
    <?php include '_form.php'; ?>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>