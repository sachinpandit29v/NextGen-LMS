<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Get token from URL
$token = $_GET['token'] ?? '';

// Get and clear flash messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
        exit();
    }
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password)) {
        $_SESSION['error'] = "Please enter a new password.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
        exit();
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
        exit();
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
        exit();
    } else {
        // Verify token and get user email
        $reset_data = getPasswordResetToken($token);

        if ($reset_data) {
            // Update password
            if (updatePassword($reset_data['email'], $password)) {
                // Delete the used token
                deletePasswordResetToken($token);
                $_SESSION['success'] = "Password updated successfully! You can now <a href='login.php'>login</a> with your new password.";
                header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
                exit();
            } else {
                $_SESSION['error'] = "Failed to update password. Please try again.";
                header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid or expired reset token.";
            header("Location: " . $_SERVER['PHP_SELF'] . "?token=" . urlencode($token));
            exit();
        }
    }
}

// Check if token is valid for displaying the form
$token_valid = false;
if (!empty($token)) {
    $reset_data = getPasswordResetToken($token);
    $token_valid = $reset_data !== false;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>NextGen | Reset Password</title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Include Navigation Bar -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Password Reset Form -->
    <div class="simple-login-wrapper">
        <div class="simple-login-card">
            <div class="simple-login-header">
                <i class="fas fa-lock fa-2x" style="color: var(--accent-blue);"></i>
                <h2>Set New Password</h2>
                <p>Enter your new password below.</p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-simple">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                </div>
            <?php elseif (!$token_valid && !empty($token)): ?>
                <div class="alert alert-danger alert-simple">
                    <i class="fas fa-exclamation-circle me-2"></i> Invalid or expired reset token. Please request a new password reset.
                </div>
                <div class="text-center mt-4">
                    <a href="forgot-password.php" class="btn btn-soft-primary">Request New Reset Link</a>
                </div>
            <?php elseif ($token_valid): ?>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-simple">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Reset Form -->
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="simple-input-group">
                        <label><i class="fas fa-lock me-2"></i>New Password</label>
                        <input type="password" name="password" placeholder="Enter new password" required>
                    </div>

                    <div class="simple-input-group">
                        <label><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" name="update_password" class="simple-login-btn">
                        <i class="fas fa-save me-2"></i>Update Password
                    </button>
                </form>

            <?php else: ?>
                <div class="alert alert-warning alert-simple">
                    <i class="fas fa-info-circle me-2"></i> Please use the link from your email to reset your password.
                </div>
                <div class="text-center mt-4">
                    <a href="forgot-password.php" class="btn btn-soft-primary">Request Password Reset</a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none" style="color: var(--accent-blue);">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>