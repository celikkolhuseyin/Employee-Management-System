<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_POST['id'] ?? 0);

$errors = validate_employee_input($_POST);

if ($errors) {
    set_flash('danger', implode(' ', $errors));
    redirect('edit.php?id=' . $id);
}

$is_active = isset($_POST['is_active']) ? 1 : 0;

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        UPDATE employees 
        SET employee_code = ?, first_name = ?, last_name = ?, email = ?, phone = ?, gender = ?, 
            birth_date = ?, hire_date = ?, salary = ?, address = ?, department_id = ?, is_active = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        'ssssssssdssii',
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
        $is_active,
        $id
    );

    $stmt->execute();

    $del = $conn->prepare('DELETE FROM employee_roles WHERE employee_id = ?');
    $del->bind_param('i', $id);
    $del->execute();

    if (!empty($_POST['role_ids'])) {
        $roleStmt = $conn->prepare('INSERT INTO employee_roles(employee_id, role_id) VALUES (?, ?)');

        foreach ($_POST['role_ids'] as $rid) {
            $rid = (int)$rid;
            $roleStmt->bind_param('ii', $id, $rid);
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
        $docStmt->bind_param('isss', $id, $docType, $fileName, $original);
        $docStmt->execute();
    }

    $conn->commit();

    set_flash('success', 'Employee updated successfully.');
    redirect('index.php');
} catch (Throwable $e) {
    $conn->rollback();
    set_flash('danger', 'Error: ' . $e->getMessage());
    redirect('edit.php?id=' . $id);
}
?>