<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT 
        e.*, 
        d.name AS department_name, 
        GROUP_CONCAT(r.name SEPARATOR ', ') AS roles 
    FROM employees e 
    INNER JOIN departments d ON e.department_id = d.id 
    LEFT JOIN employee_roles er ON er.employee_id = e.id 
    LEFT JOIN roles r ON r.id = er.role_id 
    WHERE e.id = ? 
    GROUP BY e.id
");

$stmt->bind_param('i', $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    set_flash('danger', 'Employee not found.');
    redirect('index.php');
}

$docs = $conn->prepare('SELECT * FROM employee_documents WHERE employee_id = ? ORDER BY uploaded_at DESC');
$docs->bind_param('i', $id);
$docs->execute();
$documents = $docs->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="page-header">
    <div>
        <div class="page-kicker">Employees / Profile</div>
        <h1 class="page-title">Employee Profile</h1>
        <p class="page-subtitle">Detailed employee information using JOIN with department and roles.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="edit.php?id=<?php echo e($employee['id']); ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Employee
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card-modern p-4">
            <div class="profile-detail-header">
               <div class="employee-avatar employee-photo-lg">
    <?php echo e(strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1))); ?>
</div>

                <div>
                    <h3 class="fw-bold mb-1">
                        <?php echo e($employee['first_name'] . ' ' . $employee['last_name']); ?>
                    </h3>
                    <div class="text-muted"><?php echo e($employee['employee_code']); ?></div>

                    <div class="mt-3">
                        <?php if ((int)$employee['is_active'] === 1): ?>
                            <span class="status-pill status-active">Active</span>
                        <?php else: ?>
                            <span class="status-pill status-inactive">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <hr>

            <div class="profile-meta-list">
                <div>
                    <span>Email</span>
                    <strong><?php echo e($employee['email']); ?></strong>
                </div>

                <div>
                    <span>Phone</span>
                    <strong><?php echo e($employee['phone']); ?></strong>
                </div>

                <div>
                    <span>Department</span>
                    <strong><?php echo e($employee['department_name']); ?></strong>
                </div>

                <div>
                    <span>Roles</span>
                    <strong><?php echo e($employee['roles'] ?? '-'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card-modern p-4 mb-4">
            <h5 class="fw-bold mb-3">Personal & Work Information</h5>

            <div class="row g-3">
                <div class="col-md-6 info-box">
                    <span>Gender</span>
                    <strong><?php echo e($employee['gender']); ?></strong>
                </div>

                <div class="col-md-6 info-box">
                    <span>Birth Date</span>
                    <strong><?php echo e($employee['birth_date'] ?: '-'); ?></strong>
                </div>

                <div class="col-md-6 info-box">
                    <span>Hire Date</span>
                    <strong><?php echo e($employee['hire_date']); ?></strong>
                </div>

                <div class="col-md-6 info-box">
                    <span>Salary</span>
                    <strong><?php echo e(number_format((float)$employee['salary'], 2)); ?> ₺</strong>
                </div>

                <div class="col-12 info-box">
                    <span>Address</span>
                    <strong><?php echo e($employee['address'] ?: '-'); ?></strong>
                </div>
            </div>
        </div>

        <div class="card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Employee Documents</h5>
                    <div class="text-muted small">Uploaded employee-related files.</div>
                </div>
            </div>

            <?php if ($documents->num_rows === 0): ?>
                <div class="text-muted">No documents uploaded for this employee.</div>
            <?php else: ?>
                <div class="mini-list">
                    <?php while ($d = $documents->fetch_assoc()): ?>
                        <div class="mini-list-item">
                            <div>
                                <strong><?php echo e($d['document_type']); ?></strong>
                                <div class="text-muted small"><?php echo e($d['original_name']); ?></div>
                            </div>

                            <a class="btn btn-sm btn-outline-primary"
                               href="../assets/uploads/<?php echo e($d['file_name']); ?>"
                               target="_blank">
                                Open
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>