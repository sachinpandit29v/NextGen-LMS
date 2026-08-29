<?php
// Start session to manage login state
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is already logged in and redirect appropriately
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: ../admin/admin-panel.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Get and clear flash messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get form data for repopulation on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Populate remembered email from cookie when available
$remembered_email = $_COOKIE['remember_email'] ?? '';

// Populate form fields (only on error)
$email = $form_data['email'] ?? $remembered_email;
$password = '';
$remember = $form_data['remember'] ?? false;

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!checkRateLimit('login')) {
        $reset_time = getRateLimitResetTime('login');
        $minutes_left = ceil(($reset_time - time()) / 60);
        $_SESSION['error'] = "Too many login attempts. Please try again in {$minutes_left} minutes.";
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Verify user from database
        $user = verifyUserLogin($email, $password);
        
        if ($user) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Update last login
            updateLastLogin($user['id']);
            
            // Set remember cookie if requested
            if ($remember) {
                setcookie('remember_email', $email, time() + (86400 * 30), "/", "", true, true);
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin-panel.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password. Please try again.";
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>NextGen | Login to Your Account</title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Include Navigation Bar -->
    <?php $hide_nav_links = true; ?>
    <!-- <?php include __DIR__ . '/../includes/navbar.php'; ?> -->

    <!-- Simple Login Form -->
    <div class="simple-login-wrapper">
        <div class="simple-login-card">
            <div class="simple-login-header">
                <i class="fas fa-graduation-cap fa-2x" style="color: var(--accent-blue);"></i>
                <h2>Welcome Back</h2>
                <!-- <p>Sign in to continue learning</p> -->
                <h6 class="text-muted mb-0">Students & Admins use the same login.</h6>
            </div>
            
            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-simple">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-simple">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="simple-input-group">
                    <label><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email ?: $remembered_email); ?>" required>
                </div>
                
                <div class="simple-input-group">
                    <label><i class="fas fa-lock me-2"></i>Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" <?php echo $remember ? 'checked' : ''; ?>>
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-decoration-none small" style="color: var(--accent-blue);">Forgot password?</a>
                </div>
                
                <button type="submit" name="login" class="simple-btn">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i>Sign In
                </button>
            </form>
            
            <!-- Demo Credentials -->
            <div class="demo-box">
                <i class="fas fa-flask me-1" style="color: var(--accent-blue);"></i>
                <strong>Demo Login</strong><br>
                <span class="text-muted d-block">Student: <code>student@nextgen.com</code> / <code>student123</code></span>
                <span class="text-muted d-block mt-1">Admin: <code>admin@nextgen.com</code> / <code>password</code></span>
            </div>
            
            <!-- Sign Up Link -->
            <div class="login-link ">
                Don't have an account? <b><a href="register.php">Create free account</a></b>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <!-- <?php include __DIR__ . '/../includes/footer.php'; ?> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>
