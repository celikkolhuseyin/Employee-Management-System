<?php
require_once __DIR__ . '/../includes/auth.php'; require_admin();
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../includes/functions.php';
$id=(int)($_POST['id']??0); $stmt=$conn->prepare('DELETE FROM employees WHERE id=?'); $stmt->bind_param('i',$id);
if($stmt->execute()) set_flash('success','Employee deleted successfully.'); else set_flash('danger','Delete failed.'); redirect('index.php');
?>
