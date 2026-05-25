<?php
session_start();

// Agar user login nahi hai, to Login Page par phenk do
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>