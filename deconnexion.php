<?php
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['message'] = "Vous avez été déconnecté.";

header('Location: index.php');
exit();
?>