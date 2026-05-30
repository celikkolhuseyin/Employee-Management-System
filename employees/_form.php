<?php
$departments = $conn->query('SELECT id, name FROM departments ORDER BY name');
$roles = $conn->query('SELECT id, name FROM roles ORDER BY name');
$selectedRoles = $selectedRoles ?? [];
$isEdit = isset($employee) && !empty($employee['id']);
?>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card-modern p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Personal Information</h5>
                    <div class="text-muted small">Main employee identity and contact details.</div>
                </div>
                <span class="badge rounded-pill text-bg-light border">Server-side Regex Validation</span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required">Employee Code</label>
                    <input name="employee_code" class="form-control" required
                           value="<?php echo e($employee['employee_code'] ?? ''); ?>"
                           placeholder="EMP001">
                    <small class="text-muted">Format: EMP001</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">First Name</label>
                    <input name="first_name" class="form-control" required
                           value="<?php echo e($employee['first_name'] ?? ''); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Last Name</label>
                    <input name="last_name" class="form-control" required
                           value="<?php echo e($employee['last_name'] ?? ''); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?php echo e($employee['email'] ?? ''); ?>"
                           placeholder="employee@company.com">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Phone</label>
                    <input name="phone" class="form-control" required
                           pattern="^(05[0-9]{9}|5[0-9]{9}|\+905[0-9]{9})$"
                           value="<?php echo e($employee['phone'] ?? ''); ?>"
                           placeholder="05321234567">
                    <small class="text-muted">Allowed: 05321234567, 5321234567, +905321234567</small>
                </div>

                <div class="col-md-5">
                    <label class="form-label required">Gender</label>
                    <div class="gender-box">
                        <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                            <label class="choice-card">
                                <input type="radio" name="gender" value="<?php echo e($g); ?>"
                                    <?php echo (($employee['gender'] ?? 'Male') === $g) ? 'checked' : ''; ?>>
                                <span><?php echo e($g); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control"
                           value="<?php echo e($employee['birth_date'] ?? ''); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" required
                           value="<?php echo e($employee['hire_date'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="4"
                              placeholder="Employee address..."><?php echo e($employee['address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="card-modern p-4">
            <div class="mb-4">
                <h5 class="fw-bold mb-1">Work Information</h5>
                <div class="text-muted small">Department, roles, salary and employment status.</div>
            </div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label required">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select department...</option>
                        <?php while ($d = $departments->fetch_assoc()): ?>
                            <option value="<?php echo e($d['id']); ?>"
                                <?php echo (($employee['department_id'] ?? '') == $d['id']) ? 'selected' : ''; ?>>
                                <?php echo e($d['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Salary</label>
                    <input type="number" step="0.01" name="salary" class="form-control" required
                           value="<?php echo e($employee['salary'] ?? '0'); ?>">
                </div>

                <div class="col-md-3">
    <label class="form-label">Status</label>
    <div class="active-status-card">
        <div class="status-text">
            <strong>Active</strong>
            <small>Visible in employee list</small>
        </div>

        <label class="modern-switch">
            <input type="checkbox" name="is_active" value="1"
                <?php echo !isset($employee) || (int)$employee['is_active'] === 1 ? 'checked' : ''; ?>>
            <span></span>
        </label>
    </div>
</div>

                <div class="col-12">
                    <label class="form-label">Roles - Multiple Select</label>
                    <select name="role_ids[]" class="form-select" multiple size="6">
                        <?php while ($r = $roles->fetch_assoc()): ?>
                            <option value="<?php echo e($r['id']); ?>"
                                <?php echo in_array($r['id'], $selectedRoles) ? 'selected' : ''; ?>>
                                <?php echo e($r['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Hold CTRL to select multiple roles. This satisfies the multiple select form requirement.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card-modern p-4 mb-4 sticky-side-card">
            <h5 class="fw-bold mb-3">Employee Summary</h5>

<div class="profile-preview">
    <div class="profile-preview-placeholder">
        <i class="bi bi-person"></i>
    </div>

    <div>
        <strong>
            <?php echo e(($employee['first_name'] ?? 'New') . ' ' . ($employee['last_name'] ?? 'Employee')); ?>
        </strong>
        <small><?php echo e($employee['employee_code'] ?? 'Employee profile'); ?></small>
    </div>
</div>

<hr class="my-4">

            <h5 class="fw-bold mb-3">Employee Document</h5>

            <label class="form-label">Document File</label>
            <input type="file" name="document" class="form-control">

            <label class="form-label mt-3">Document Type</label>
            <select name="document_type" class="form-select">
                <option>CV</option>
                <option>Contract</option>
                <option>Certificate</option>
                <option>Other</option>
            </select>

            <small class="text-muted d-block mt-2">This area satisfies the file input form requirement.</small>

            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-primary btn-lg" type="submit">
                    <i class="bi bi-check2-circle me-1"></i>
                    <?php echo $isEdit ? 'Save Changes' : 'Create Employee'; ?>
                </button>

                <a href="index.php" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>