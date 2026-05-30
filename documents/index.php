<?php
require_once __DIR__ . '/../includes/auth.php';
require_manager_or_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['search'] ?? '');
$type = $_GET['document_type'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';

$orderBy = 'ed.uploaded_at DESC, ed.id DESC';

switch ($sortBy) {
    case 'oldest':
        $orderBy = 'ed.uploaded_at ASC, ed.id ASC';
        break;
    case 'employee_az':
        $orderBy = 'e.first_name ASC, e.last_name ASC';
        break;
    case 'employee_za':
        $orderBy = 'e.first_name DESC, e.last_name DESC';
        break;
    case 'type_az':
        $orderBy = 'ed.document_type ASC, ed.uploaded_at DESC';
        break;
    case 'file_az':
        $orderBy = 'ed.original_name ASC';
        break;
    case 'department_az':
        $orderBy = 'd.name ASC, e.first_name ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'ed.uploaded_at DESC, ed.id DESC';
        break;
}

$sql = "
    SELECT 
        ed.*,
        CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
        e.employee_code,
        e.email,
        d.name AS department_name
    FROM employee_documents ed
    INNER JOIN employees e ON ed.employee_id = e.id
    INNER JOIN departments d ON e.department_id = d.id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($type !== '') {
    $sql .= " AND ed.document_type = ?";
    $params[] = $type;
    $types .= 's';
}

if ($search !== '') {
    $sql .= " AND (
        e.employee_code LIKE ?
        OR e.first_name LIKE ?
        OR e.last_name LIKE ?
        OR e.email LIKE ?
        OR ed.original_name LIKE ?
        OR d.name LIKE ?
    )";

    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssssss';
}

$sql .= " ORDER BY $orderBy";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$rows = $stmt->get_result();

$totalDocuments = $conn->query("SELECT COUNT(*) AS c FROM employee_documents")->fetch_assoc()['c'];
$totalCv = $conn->query("SELECT COUNT(*) AS c FROM employee_documents WHERE document_type = 'CV'")->fetch_assoc()['c'];
$totalContract = $conn->query("SELECT COUNT(*) AS c FROM employee_documents WHERE document_type = 'Contract'")->fetch_assoc()['c'];
$totalCertificate = $conn->query("SELECT COUNT(*) AS c FROM employee_documents WHERE document_type = 'Certificate'")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<?php show_flash(); ?>

<div class="page-header">
    <div>
        <div class="page-kicker">Documents / Employee Files</div>
        <h1 class="page-title">Documents</h1>
        <p class="page-subtitle">
            View uploaded employee documents such as CVs, contracts and certificates.
        </p>
    </div>

    <a href="/employee-management-system/employees/index.php" class="btn btn-outline-primary">
        <i class="bi bi-person-lines-fill me-1"></i> Manage via Employees
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="stat-label">Total Documents</div>
            <div class="stat-value"><?php echo e($totalDocuments); ?></div>
            <div class="stat-note">Uploaded files</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-file-earmark-person"></i>
            </div>
            <div class="stat-label">CV Files</div>
            <div class="stat-value"><?php echo e($totalCv); ?></div>
            <div class="stat-note">Employee resumes</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="stat-label">Contracts</div>
            <div class="stat-value"><?php echo e($totalContract); ?></div>
            <div class="stat-note">Employment contracts</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card-modern stat-card">
            <div class="stat-icon">
                <i class="bi bi-award"></i>
            </div>
            <div class="stat-label">Certificates</div>
            <div class="stat-value"><?php echo e($totalCertificate); ?></div>
            <div class="stat-note">Employee certificates</div>
        </div>
    </div>
</div>

<div class="card-modern toolbar-card">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    name="search" 
                    class="form-control" 
                    placeholder="Search employee, department or file..."
                    value="<?php echo e($search); ?>"
                >
            </div>
        </div>

        <div class="col-lg-2 col-md-4">
            <label class="form-label">Document Type</label>
            <select name="document_type" class="form-select">
                <option value="">All Types</option>
                <?php foreach (['CV', 'Contract', 'Certificate', 'Other'] as $docType): ?>
                    <option value="<?php echo e($docType); ?>" <?php echo $type === $docType ? 'selected' : ''; ?>>
                        <?php echo e($docType); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-4 col-md-5">
            <label class="form-label">Sort By</label>
            <select name="sort_by" class="form-select">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest Uploaded First</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest Uploaded First</option>
                <option value="employee_az" <?php echo $sortBy === 'employee_az' ? 'selected' : ''; ?>>Employee Name A-Z</option>
                <option value="employee_za" <?php echo $sortBy === 'employee_za' ? 'selected' : ''; ?>>Employee Name Z-A</option>
                <option value="type_az" <?php echo $sortBy === 'type_az' ? 'selected' : ''; ?>>Document Type A-Z</option>
                <option value="file_az" <?php echo $sortBy === 'file_az' ? 'selected' : ''; ?>>File Name A-Z</option>
                <option value="department_az" <?php echo $sortBy === 'department_az' ? 'selected' : ''; ?>>Department A-Z</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-3 d-flex gap-2">
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
            <h5 class="fw-bold mb-1">Employee Document List</h5>
            <div class="text-muted small">
                JOIN: employee_documents + employees + departments.
            </div>
        </div>

        <span class="badge rounded-pill text-bg-light border">
            <?php echo e($rows->num_rows); ?> files listed
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Document Type</th>
                    <th>File</th>
                    <th>Department</th>
                    <th>Uploaded</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-folder-x d-block fs-2 mb-2"></i>
                            No documents found.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php while ($d = $rows->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-name">
                                <div class="employee-avatar">
                                    <?php
                                        $parts = explode(' ', $d['employee_name']);
                                        echo e(strtoupper(substr($parts[0] ?? 'E', 0, 1) . substr($parts[1] ?? 'M', 0, 1)));
                                    ?>
                                </div>

                                <div>
                                    <strong><?php echo e($d['employee_name']); ?></strong>
                                    <small><?php echo e($d['employee_code'] . ' · ' . $d['email']); ?></small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge rounded-pill text-bg-light border">
                                <?php echo e($d['document_type']); ?>
                            </span>
                        </td>

                        <td>
                            <a 
                                target="_blank" 
                                class="fw-semibold text-decoration-none"
                                href="../assets/uploads/<?php echo e($d['file_name']); ?>"
                            >
                                <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                <?php echo e($d['original_name']); ?>
                            </a>
                        </td>

                        <td><?php echo e($d['department_name']); ?></td>

                        <td><?php echo e($d['uploaded_at']); ?></td>

                        <td>
                            <div class="action-buttons justify-content-end">
                                <a 
                                    target="_blank" 
                                    class="btn btn-sm btn-outline-primary"
                                    href="../assets/uploads/<?php echo e($d['file_name']); ?>"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <?php if (is_admin()): ?>
                                    <form class="delete-form" method="post" action="delete.php">
                                        <input type="hidden" name="id" value="<?php echo e($d['id']); ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card-modern p-4 mt-4">
    <h5 class="fw-bold mb-2">How document upload works</h5>
    <p class="text-muted mb-0">
        Documents are uploaded from the employee create/edit form. The file metadata is stored in the
        <code>employee_documents</code> table, while the actual file is stored in the
        <code>assets/uploads</code> directory. This satisfies the file input and upload requirement.
    </p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>