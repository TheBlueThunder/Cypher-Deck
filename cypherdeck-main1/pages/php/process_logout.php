<?php
// Destroy session to clear all user authentication data and redirect to homepage
session_start();
session_destroy();
header('Location: ../../index.php');
exit();
?>
