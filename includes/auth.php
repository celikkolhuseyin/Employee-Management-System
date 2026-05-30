<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /employee-management-system/login.php');
        exit;
    }
}

function current_user_type() {
    return $_SESSION['user_type'] ?? '';
}

function is_admin() {
    return current_user_type() === 'Administrator';
}

function is_manager() {
    return current_user_type() === 'Manager';
}

function is_employee_user() {
    return current_user_type() === 'Employee';
}

function can_manage_system() {
    return is_admin();
}

function can_view_reports() {
    return is_admin() || is_manager();
}

function can_view_logs() {
    return is_admin();
}

function can_manage_employees() {
    return is_admin();
}

function require_admin() {
    require_login();

    if (!is_admin()) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Only administrators can perform this action.'
        ];

        header('Location: /employee-management-system/dashboard.php');
        exit;
    }
}

function require_manager_or_admin() {
    require_login();

    if (!is_admin() && !is_manager()) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'This page is available only for administrators and managers.'
        ];

        header('Location: /employee-management-system/dashboard.php');
        exit;
    }
}

function require_report_access() {
    require_login();

    if (!can_view_reports()) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'You do not have permission to view reports.'
        ];

        header('Location: /employee-management-system/dashboard.php');
        exit;
    }
}
?>