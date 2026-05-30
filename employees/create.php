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
        <div class="page-kicker">Employees / Create</div>
        <h1 class="page-title">Add Employee</h1>
        <p class="page-subtitle">Create a new employee profile with department, roles, photo and documents.</p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Employees
    </a>
</div>

<form method="post" action="store.php" enctype="multipart/form-data">
    <?php include '_form.php'; ?>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>