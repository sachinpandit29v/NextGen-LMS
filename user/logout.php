<?php
require_once __DIR__ . '/../config/app.php';

// Ensure logout is only processed via POST to prevent CSRF via unsafe GET links.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_USER_PATH . '/login.php');
    exit();
}

// Prevent caching of logout response
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Start session if needed before destroying it.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Destroy all session data safely.
session_unset();
session_destroy();

// Delete the session cookie explicitly with multiple paths
$sessionName = session_name();
setcookie($sessionName, '', time() - 3600, '/');
if (APP_BASE_URL !== '') {
    setcookie($sessionName, '', time() - 3600, APP_BASE_URL . '/');
    setcookie($sessionName, '', time() - 3600, APP_USER_PATH . '/');
    setcookie($sessionName, '', time() - 3600, APP_ADMIN_PATH . '/');
}

// Clear remember me cookie if exists.
if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, '/');
}

header('Location: ' . APP_USER_PATH . '/login.php?t=' . time());
exit();
?>
