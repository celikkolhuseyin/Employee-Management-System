<?php
require_once __DIR__ . '/../includes/auth.php';
require_manager_or_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$employees = $conn->query("
    SELECT 
        e.id,
        e.employee_code,
        CONCAT(e.first_name, ' ', e.last_name) AS name,
        d.name AS department_name
    FROM employees e
    INNER JOIN departments d ON e.department_id = d.id
    WHERE e.is_active = 1
    ORDER BY e.first_name, e.last_name
");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Work Records / Create</div>
        <h1 class="page-title">Add Work Record</h1>
        <p class="page-subtitle">
            Create a daily work status record for an active employee.
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Records
    </a>
</div>

<form method="post" action="store.php">
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-modern p-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">Record Information</h5>
                    <div class="text-muted small">
                        Select the employee, date and work status.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee...</option>
                            <?php while ($e = $employees->fetch_assoc()): ?>
                                <option value="<?php echo e($e['id']); ?>">
                                    <?php echo e($e['employee_code'] . ' - ' . $e['name'] . ' (' . $e['department_name'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Date</label>
                        <input 
                            type="date" 
                            name="work_date" 
                            class="form-control" 
                            required 
                            value="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Present">Present</option>
                            <option value="Remote">Remote</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea 
                            name="notes" 
                            class="form-control" 
                            rows="5"
                            placeholder="Optional notes about this work record..."
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-modern p-4 sticky-side-card">
                <h5 class="fw-bold mb-3">Work Record Summary</h5>

                <div class="profile-preview">
                    <div class="profile-preview-placeholder">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>
                        <strong>Attendance Tracking</strong>
                        <small>Employee daily work status</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mini-list">
                    <div class="mini-list-item">
                        <div>
                            <strong>Present</strong>
                            <div class="text-muted small">Employee worked on-site</div>
                        </div>
                    </div>

                    <div class="mini-list-item">
                        <div>
                            <strong>Remote</strong>
                            <div class="text-muted small">Employee worked remotely</div>
                        </div>
                    </div>

                    <div class="mini-list-item">
                        <div>
                            <strong>Leave / Absent</strong>
                            <div class="text-muted small">Leave or absence tracking</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Save Record
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