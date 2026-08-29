<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

// Get and clear flash messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Get form data for repopulation on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Populate form fields (only on error)
$email = $form_data['email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['error'] = "Please enter your email address.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!emailExists($email)) {
        $_SESSION['error'] = "No account found with this email address.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Generate reset token
        $token = bin2hex(random_bytes(32));

        // Create password reset token in database
        if (createPasswordResetToken($email, $token)) {
            // In a real application, send email here
            // For demo purposes, we'll show the reset link
            $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
            $reset_link = $scheme . '://' . $_SERVER['HTTP_HOST'] . APP_USER_PATH . '/reset-password.php?token=' . urlencode($token);

            $_SESSION['success'] = "Password reset link has been sent to your email. For demo purposes, here's the link: <br><a href='$reset_link' target='_blank'>$reset_link</a>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to process password reset. Please try again.";
            $_SESSION['form_data'] = $_POST;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>NextGen | Forgot Password</title>
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
                <i class="fas fa-key fa-2x" style="color: var(--accent-blue);"></i>
                <h2>Reset Password</h2>
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-simple">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

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
                    <label><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>

                <button type="submit" name="reset_password" class="simple-login-btn">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </form>

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
