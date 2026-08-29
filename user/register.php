<?php
// Start session to manage registration state
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
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Get form data for repopulation on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    $form_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email
    ];
    
    // Validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!checkRateLimit('register')) {
        $reset_time = getRateLimitResetTime('register');
        $minutes_left = ceil(($reset_time - time()) / 60);
        $_SESSION['error'] = "Too many registration attempts. Please try again in {$minutes_left} minutes.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!$terms) {
        $_SESSION['error'] = "You must agree to the Terms of Service and Privacy Policy.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (emailExists($email)) {
        $_SESSION['error'] = "An account with this email already exists. Please login instead.";
        $_SESSION['form_data'] = $form_data;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Create new user in database
        $result = createUser($first_name, $last_name, $email, $password);
        
        if ($result) {
            $_SESSION['success'] = "Account created successfully! You can now login with your credentials.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Registration failed. Please try again later.";
            $_SESSION['form_data'] = $form_data;
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
    <title>NextGen | Create Account</title>
    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Include Navigation Bar -->
    <?php $hide_nav_links = true; ?>
    <!-- <?php include __DIR__ . '/../includes/navbar.php'; ?> -->

    <!-- Simple Register Form -->
    <div class="simple-register-wrapper">
        <div class="simple-register-card">
            <div class="simple-register-header">
                <i class="fas fa-user-plus fa-2x" style="color: var(--accent-blue);"></i>
                <h2>Create Account</h2>
                <!-- <p>Join NextGen and start your learning journey</p> -->
            </div>
            
            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-simple">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Registration Form -->
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="simple-input-row">
                    <div class="simple-input-group">
                        <label><i class="fas fa-user me-2"></i>First Name *</label>
                        <input type="text" name="first_name" placeholder="John" value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="simple-input-group">
                        <label><i class="fas fa-user me-2"></i>Last Name *</label>
                        <input type="text" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="simple-input-group">
                    <label><i class="fas fa-envelope me-2"></i>Email Address *</label>
                    <input type="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                </div>
                
                <div class="simple-input-row">
                    <div class="simple-input-group">
                        <label><i class="fas fa-lock me-2"></i>Password *</label>
                        <input type="password" name="password" id="password" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="simple-input-group">
                        <label><i class="fas fa-check-circle me-2"></i>Confirm Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="terms-check">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a> *
                    </label>
                </div>
                
                <button type="submit" name="register" class="simple-btn">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="login-link">
                Already have an account? <b><a href="login.php">Sign in </a></b>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <!-- <?php include __DIR__ . '/../includes/footer.php'; ?> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time password match confirmation
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (confirm.length > 0) {
                if (password === confirm) {
                    confirmInput.style.borderColor = '#10b981';
                } else {
                    confirmInput.style.borderColor = '#dc2626';
                }
            } else {
                confirmInput.style.borderColor = '#e2e8f0';
            }
        }
        
        confirmInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
        
        // Form validation before submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match. Please check and try again.');
            }
        });
    </script>
</body>

</html>
