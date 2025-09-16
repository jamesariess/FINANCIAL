<?php


session_start();


if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
   
    header("Location: pages/dashboard/dashboard.php");
    exit(); 
} else {
 
    header("Location: partials/login.php");
    exit();
}
?>