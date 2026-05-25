<?php
session_start();
session_destroy(); // Session khatam
header("Location: login.php"); // Wapis login page par
exit;
?>