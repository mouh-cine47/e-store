<?php
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
	header('Location: ../public/csrf_error.php');
	exit();
}

session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
