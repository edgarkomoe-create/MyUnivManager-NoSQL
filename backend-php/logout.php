<?php
session_start();

// Secure session destruction
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
?>