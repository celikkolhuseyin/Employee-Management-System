<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[\w.%+\-]+@[\w.\-]+\.[A-Za-z]{2,}$/', $email)) {
        $error = 'Please enter a valid e-mail address.';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name, email, password, user_type FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];

            redirect('dashboard.php');
        } else {
            $error = 'Invalid e-mail or password.';
        }
    }
}

$demoAccounts = [
    [
        'role' => 'Administrator',
        'email' => 'admin@ems.local',
        'password' => 'admin123',
        'description' => 'Full access to all modules',
        'icon' => 'bi-shield-lock'
    ],
    [
        'role' => 'Manager',
        'email' => 'manager@ems.local',
        'password' => 'manager123',
        'description' => 'Reports, work records and documents',
        'icon' => 'bi-person-workspace'
    ],
    [
        'role' => 'Employee',
        'email' => 'employee@ems.local',
        'password' => 'employee123',
        'description' => 'Restricted read-only access',
        'icon' => 'bi-person'
    ]
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Employee Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/employee-management-system/assets/css/style.css?v=20">
</head>

<body class="ems-login-body">

<div class="ems-login-page">
    <section class="ems-login-hero">
        <div class="ems-login-brand">
            <div class="ems-login-logo">
                <i class="bi bi-people-fill"></i>
            </div>

            <div>
                <div class="ems-login-brand-title">EMS</div>
                <div class="ems-login-brand-subtitle">Employee Management System</div>
            </div>
        </div>

        <div class="ems-login-hero-content">
            <span class="ems-login-badge">
                <i class="bi bi-mortarboard"></i>
                Graduation Project
            </span>

            <h1>Manage employees with a secure web-based system.</h1>

            <p>
                A PHP and MySQL based Employee Management System with session authentication,
                role-based access control, CRUD operations, file upload, JOIN reports,
                triggers and stored procedures.
            </p>

            <div class="ems-login-feature-grid">
                <div class="ems-login-feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Session-based login</span>
                </div>

                <div class="ems-login-feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Role-based authorization</span>
                </div>

                <div class="ems-login-feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>MySQLi secure database access</span>
                </div>

                <div class="ems-login-feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Trigger, procedure and JOIN reports</span>
                </div>
            </div>
        </div>
    </section>

    <section class="ems-login-panel">
        <div class="ems-login-card">
            <div class="ems-login-card-header">
                <div>
                    <h2>Welcome back</h2>
                    <p>Select a demo role or enter your credentials.</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="ems-login-form">
                <div class="mb-3">
                    <label class="form-label required">E-mail</label>
                    <div class="ems-input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" class="form-control" required value="admin@ems.local">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Password</label>
                    <div class="ems-input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" class="form-control" required value="admin123">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg ems-login-button">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Login
                </button>
            </form>

            <div class="ems-demo-title">
                <span></span>
                Demo Accounts
                <span></span>
            </div>

            <div class="ems-demo-accounts">
                <?php foreach ($demoAccounts as $account): ?>
                    <button 
                        type="button" 
                        class="ems-demo-account"
                        onclick="fillDemoAccount('<?php echo e($account['email']); ?>', '<?php echo e($account['password']); ?>')"
                    >
                        <div class="ems-demo-icon">
                            <i class="bi <?php echo e($account['icon']); ?>"></i>
                        </div>

                        <div class="ems-demo-meta">
                            <strong><?php echo e($account['role']); ?></strong>
                            <small><?php echo e($account['description']); ?></small>
                            <code><?php echo e($account['email']); ?> / <?php echo e($account['password']); ?></code>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<script>
function fillDemoAccount(email, password) {
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('input[name="password"]').value = password;
}
</script>

</body>
</html>