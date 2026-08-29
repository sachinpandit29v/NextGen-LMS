<?php
// Main entry point for NextGen LMS
// Redirect to user homepage by default

// Check if user is logged in and redirect accordingly
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        // Redirect admins to admin panel
        header("Location: admin/admin-panel.php");
    } else {
        // Redirect regular users to user dashboard
        header("Location: user/dashboard.php");
    }
} else {
    // Redirect non-logged-in users to homepage
    header("Location: user/home.php");
}
exit();
?>