<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'] === 'success' ? 'success' : 'danger';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
            . e($_SESSION['flash']['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash']);
    }
}

function validate_employee_input($data) {
    $errors = [];

    if (!preg_match('/^EMP[0-9]{3,8}$/', $data['employee_code'] ?? '')) {
        $errors[] = 'Employee code must be like EMP001.';
    }

    if (!preg_match('/^[A-Za-zÇĞİÖŞÜçğıöşü\s]{2,80}$/u', $data['first_name'] ?? '')) {
        $errors[] = 'First name must contain only letters and spaces.';
    }

    if (!preg_match('/^[A-Za-zÇĞİÖŞÜçğıöşü\s]{2,80}$/u', $data['last_name'] ?? '')) {
        $errors[] = 'Last name must contain only letters and spaces.';
    }

    if (!preg_match('/^[\w.%+\-]+@[\w.\-]+\.[A-Za-z]{2,}$/', $data['email'] ?? '')) {
        $errors[] = 'Please enter a valid e-mail address.';
    }

    $phoneRaw = trim($data['phone'] ?? '');
    $phoneNormalized = preg_replace('/[\s\-]/', '', $phoneRaw);

    if (!preg_match('/^(05[0-9]{9}|5[0-9]{9}|\+905[0-9]{9})$/', $phoneNormalized)) {
        $errors[] = 'Phone number must be a valid Turkish mobile number, for example 05321234567.';
    }

    if (!in_array(($data['gender'] ?? ''), ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if (!is_numeric($data['salary'] ?? '') || (float)$data['salary'] < 0) {
        $errors[] = 'Salary must be a positive number.';
    }

    if (empty($data['department_id']) || !is_numeric($data['department_id'])) {
        $errors[] = 'Please select a valid department.';
    }

    if (empty($data['hire_date'])) {
        $errors[] = 'Hire date is required.';
    }

    return $errors;
}

function upload_employee_file($file) {
    if (empty($file['name'])) {
        return [null, null, null];
    }

    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        return [null, null, 'Only PDF, DOC, DOCX, JPG, JPEG and PNG files are allowed.'];
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        return [null, null, 'File size must be less than 3 MB.'];
    }

    $safeName = uniqid('doc_', true) . '.' . $ext;
    $target = __DIR__ . '/../assets/uploads/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [null, null, 'File upload failed.'];
    }

    return [$safeName, $file['name'], null];
}

function upload_employee_photo($file) {
    if (empty($file['name'])) {
        return [null, null];
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        return [null, 'Only JPG, JPEG, PNG and WEBP profile photos are allowed.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return [null, 'Profile photo size must be less than 2 MB.'];
    }

    $photoDir = __DIR__ . '/../assets/uploads/photos/';

    if (!is_dir($photoDir)) {
        mkdir($photoDir, 0777, true);
    }

    $safeName = uniqid('profile_', true) . '.' . $ext;
    $target = $photoDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [null, 'Profile photo upload failed.'];
    }

    return [$safeName, null];
}

function employee_avatar_html($employee, $sizeClass = '') {
    $photo = $employee['profile_photo'] ?? '';

    if (!empty($photo)) {
        return '<img class="employee-photo ' . e($sizeClass) . '" src="/employee-management-system/assets/uploads/photos/' . e($photo) . '" alt="Employee photo">';
    }

    $initials = strtoupper(substr($employee['first_name'] ?? 'E', 0, 1) . substr($employee['last_name'] ?? 'M', 0, 1));

    return '<div class="employee-avatar ' . e($sizeClass) . '">' . e($initials) . '</div>';
}
?>