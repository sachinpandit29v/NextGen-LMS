<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../user/login.php");
    exit();
}

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    session_destroy();
    header("Location: ../user/login.php");
    exit();
}

// Set current page for active sidebar link
$current_page = 'admin-panel.php';

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Initialize error and success variables
$error = '';
$success = '';

// Dashboard statistics
$total_users = 0;
$total_students = 0;
$total_admins = 0;
$total_contacts = 0;
$total_courses = 0;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1");
    $stmt->execute();
    $total_students = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1");
    $stmt->execute();
    $total_admins = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts");
    $stmt->execute();
    $total_contacts = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE is_active = 1");
    $stmt->execute();
    $total_courses = $stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
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
    <title>Admin Dashboard | NextGen LMS</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom professional admin styling (overrides legacy) -->
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
            <!-- Dashboard greeting -->
            <!-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-secondary mb-0 fs-6">Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Admin'); ?> 👋</p>
                    <p class="text-muted small">Monitor platform activity and manage resources efficiently.</p>
                </div>
            </div> -->

            <!-- Statistics Cards - Modern redesign -->
            <div class="row g-4 mb-5">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                                    <div class="stat-label mt-1">Total users</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                            </div>
                            <div class="mt-3 small text-success fw-semibold">
                                <i class="fas fa-arrow-up me-1"></i> +12% active
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number"><?php echo number_format($total_students); ?></div>
                                    <div class="stat-label mt-1">Active students</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(34,197,94,0.12); color:#16a34a;">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                            <div class="mt-3 small text-secondary fw-semibold">
                                <i class="fas fa-chart-line me-1"></i> engagement +8%
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number"><?php echo number_format($total_admins); ?></div>
                                    <div class="stat-label mt-1">Administrators</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(139,92,246,0.12); color:#8b5cf6;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                            <div class="mt-3 small text-secondary">Platform managers</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number"><?php echo number_format($total_courses); ?></div>
                                    <div class="stat-label mt-1">Active courses</div>
                                </div>
                                <div class="stat-icon" style="background: rgba(249,115,22,0.12); color:#f97316;">
                                    <i class="fas fa-book"></i>
                                </div>
                            </div>
                            <div class="mt-3 small text-info fw-semibold">
                                <i class="fas fa-plus-circle me-1"></i> +3 new this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary row: Contact Submissions & Quick Actions -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="quick-card h-100 p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon" style="background:#f1f5f9; color:#3b82f6;">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0"><?php echo number_format($total_contacts); ?></h3>
                                <p class="text-muted mb-0">Contact form submissions</p>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">Latest inquiries from users, ready to review.</p>
                        <a href="contact-management.php" class="btn btn-outline-glass w-100">
                            View all messages
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="quick-card h-100 p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="stat-icon" style="background:#eef2ff; color:#2563eb;">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Quick Actions</h5>
                                <p class="text-muted small">Streamline your LMS operations</p>
                            </div>
                        </div>
                        <div class="d-grid gap-3">
                            <a href="user-management.php" class="btn btn-admin-primary rounded-pill shadow-sm">
                                Manage Users
                            </a>
                            <a href="course-management.php" class="btn btn-admin-secondary rounded-pill">
                                Manage Courses
                            </a>
                            <a href="contact-management.php" class="btn btn-admin-secondary rounded-pill">
                                View Contacts
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Overview with elevated design -->
            <div class="row">
                <div class="col-12">
                    <div class="overview-card">
                        <div class="overview-header d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2"></i>System Overview</h5>
                                <p class="text-muted small mb-0">Real-time snapshot of platform growth</p>
                            </div>
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill"><i class="far fa-calendar-alt me-1"></i> Live data</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row text-center gy-4">
                                <div class="col-md-3 col-6">
                                    <div class="stat-metric">
                                        <h4 class="text-primary"><?php echo number_format($total_users); ?></h4>
                                        <small class="text-muted">Registered Users</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-metric">
                                        <h4 class="text-success"><?php echo number_format($total_students); ?></h4>
                                        <small class="text-muted">Active Students</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-metric">
                                        <h4 class="text-info"><?php echo number_format($total_admins); ?></h4>
                                        <small class="text-muted">Administrators</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="stat-metric">
                                        <h4 class="text-warning"><?php echo number_format($total_courses); ?></h4>
                                        <small class="text-muted">Published Courses</small>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-sync-alt text-secondary"></i>
                                    <span class="small text-secondary">Last updated: <?php echo date('H:i, d M Y'); ?></span>
                                </div>
                                <a href="#" class="small text-decoration-none fw-semibold">View full analytics <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- additional professional footer note -->
            <div class="mt-5 text-center text-muted small border-top pt-4">
                 NextGen LMS – Secure Admin Console
            </div>
</div>
</div>

<!-- Bootstrap JS Bundle (required for offcanvas and dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Additional micro interaction -->
<script>
    (function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    })();
</script>
</body>
</html>
