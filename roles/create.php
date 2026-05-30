<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Authorization / Roles / Create</div>
        <h1 class="page-title">Add Role</h1>
        <p class="page-subtitle">
            Create a role that can be assigned to employees.
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Roles
    </a>
</div>

<form method="post" action="store.php">
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-modern p-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">Role Information</h5>
                    <div class="text-muted small">
                        Employee roles are stored separately and connected by the employee_roles table.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label required">Role Name</label>
                        <input 
                            name="name" 
                            class="form-control" 
                            required
                            placeholder="Example: HR Specialist"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea 
                            name="description" 
                            class="form-control" 
                            rows="5"
                            placeholder="Describe the role responsibility..."
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-modern p-4 sticky-side-card">
                <h5 class="fw-bold mb-3">Role Relation</h5>

                <div class="profile-preview">
                    <div class="profile-preview-placeholder">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <div>
                        <strong>Many-to-Many</strong>
                        <small>Employees can have multiple roles</small>
                    </div>
                </div>

                <hr class="my-4">

                <p class="text-muted">
                    Roles are connected to employees using the employee_roles relation table.
                    This supports assigning multiple roles to one employee.
                </p>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-check2-circle me-1"></i> Create Role
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