<?php
// index.php
// This is the main entry point for the SLATE application.
// It redirects users to either the login page or the dashboard based on their session status.

session_start(); // Start the PHP session to manage user login state

// Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
   
    header("Location: pages/ar/adjustment.php");
    exit(); // Always exit after a header redirect
} else {
 
    header("Location: partials/login.php");
    exit(); // Always exit after a header redirect
}
?>