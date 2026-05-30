<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = validate_employee_input($_POST);

if ($errors) {
    set_flash('danger', implode(' ', $errors));
    redirect('create.php');
}

$is_active = isset($_POST['is_active']) ? 1 : 0;

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO employees
        (employee_code, first_name, last_name, email, phone, gender, birth_date, hire_date, salary, address, department_id, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        'ssssssssdssi',
        $_POST['employee_code'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['gender'],
        $_POST['birth_date'],
        $_POST['hire_date'],
        $_POST['salary'],
        $_POST['address'],
        $_POST['department_id'],
        $is_active
    );

    $stmt->execute();
    $employee_id = $conn->insert_id;

    if (!empty($_POST['role_ids'])) {
        $roleStmt = $conn->prepare('INSERT INTO employee_roles(employee_id, role_id) VALUES (?, ?)');

        foreach ($_POST['role_ids'] as $rid) {
            $rid = (int)$rid;
            $roleStmt->bind_param('ii', $employee_id, $rid);
            $roleStmt->execute();
        }
    }

    [$fileName, $original, $uploadError] = upload_employee_file($_FILES['document'] ?? []);

    if ($uploadError) {
        throw new Exception($uploadError);
    }

    if ($fileName) {
        $docType = $_POST['document_type'];
        $docStmt = $conn->prepare('
            INSERT INTO employee_documents(employee_id, document_type, file_name, original_name)
            VALUES (?, ?, ?, ?)
        ');
        $docStmt->bind_param('isss', $employee_id, $docType, $fileName, $original);
        $docStmt->execute();
    }

    $conn->commit();

    set_flash('success', 'Employee created successfully.');
    redirect('index.php');
} catch (Throwable $e) {
    $conn->rollback();
    set_flash('danger', 'Error: ' . $e->getMessage());
    redirect('create.php');
}
?>