<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Get and clear flash messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get form data for repopulation on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Populate form fields (only on error)
$name = $form_data['name'] ?? '';
$email = $form_data['email'] ?? '';
$phone = $form_data['phone'] ?? '';
$subject = $form_data['subject'] ?? '';
$message = $form_data['message'] ?? '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        $_SESSION['form_data'] = $_POST;
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please provide a valid email address.";
        $_SESSION['form_data'] = $_POST;
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Sanitize inputs
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // Try to save contact to database
        if (createContact($name, $email, $phone, $subject, $message)) {
            $_SESSION['success'] = "Thank you for contacting us! We'll get back to you shortly.";
            // Clear form fields
            $name = $email = $phone = $subject = $message = '';
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "An error occurred while saving your message. Please try again.";
            $_SESSION['form_data'] = $_POST;
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Re-populate form fields from $_POST only if there was an error
if (!empty($error)) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
} else {
    // Clear form fields on successful submission or initial load
    $name = $email = $phone = $subject = $message = '';
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
    <title>NextGen | Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <section class="hero-alternate">
        <div class="container">
            <div class="row align-items-center gx-5">
                <div class="col-lg-6">
                    <h1 class="hero-title mt-4">Need help? Our support team is ready to assist.</h1>
                    <p class="text-secondary mt-4" style="max-width: 640px; font-size: 1.05rem; line-height: 1.8;">Whether you have a question about enrollment, course access, or your account, we make it fast and simple to get help. Choose your preferred channel below and we’ll respond promptly.</p>
                </div>
                <div class="col-lg-6">
                    <div class="course-card p-4">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">24/7</div>
                                    <div class="text-secondary small mt-2">Support access</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">1h</div>
                                    <div class="text-secondary small mt-2">Typical response</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">Email</div>
                                    <div class="text-secondary small mt-2">hello@nextgen.com</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">Live chat</div>
                                    <div class="text-secondary small mt-2">Business hours</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="hero-badge">Reach us</span>

            <section class="py-5">
                <div class="container">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-5">
                            <div class="course-card p-4">
                                <h3 class="mb-4">Contact details</h3>
                                <div class="mb-4">
                                    <h6 class="fw-semibold">Email</h6>
                                    <p class="text-secondary">hello@nextgen.com</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="fw-semibold">Phone</h6>
                                    <p class="text-secondary">+1 (555) 123-4567</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="fw-semibold">Office</h6>
                                    <p class="text-secondary">123 Innovation Drive, Suite 450, New York, NY</p>
                                </div>
                                <a href="mailto:hello@nextgen.com" class="btn btn-soft-primary w-100">Email support</a>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="course-card p-4" id="contact-form">
                                <h3 class="mb-4">Send a message</h3>

                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="post" action="#contact-form">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Full name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" placeholder="Jane Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Phone (optional)</label>
                                        <input type="tel" class="form-control" name="phone" placeholder="(123) 456-7890" value="<?php echo htmlspecialchars($phone); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="subject" placeholder="Course access question" value="<?php echo htmlspecialchars($subject); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="message" rows="5" placeholder="Tell us how we can help." required><?php echo htmlspecialchars($message); ?></textarea>
                                    </div>
                                    <button type="submit" name="contact_submit" class="btn btn-soft-primary w-100">Submit request</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="row g-4 mt-5">
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-envelope"></i></div>
                        <h5>Email support</h5>
                        <p class="text-secondary">Send us a message anytime and we’ll reply as soon as possible.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-headset"></i></div>
                        <h5>Live help</h5>
                        <p class="text-secondary">Get real-time assistance during working hours for urgent issues.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-comments"></i></div>
                        <h5>Community</h5>
                        <p class="text-secondary">Join other learners in our support community for tips and guidance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clear form fields after successful submission
        <?php if (!empty($success)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                if (form) {
                    form.reset();
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>