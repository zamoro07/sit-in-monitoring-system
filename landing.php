<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: ../dashboard.php"); 
} else {
    header("Location: index.php");
}
exit();
?>