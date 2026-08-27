<?php
session_start();

// Clear session variables but keep tasks intact
unset($_SESSION['username']);
unset($_SESSION['role']);
unset($_SESSION['logged_in']);

// Or use session_destroy() if you want to clear everything
// $_SESSION = array();
// session_destroy();

header('Location: header.php');
exit();
