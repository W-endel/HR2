<?php
session_start();

// Unset only employee-specific session variables
unset($_SESSION['e_id']);
unset($_SESSION['role']);

header("Location: ../employee/login.php");
exit();
?>
