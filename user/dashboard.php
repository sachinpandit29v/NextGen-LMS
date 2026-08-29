<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin, redirect to admin panel
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: ../admin/admin-panel.php");
    exit();
}

// Get user data from database
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    // User not found in database, logout
    session_destroy();
    header("Location: login.php");
    exit();
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
    <title>NextGen | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-wrapper {
            min-height: calc(100vh - 200px);
            background: linear-gradient(135deg, #f6f8fa 0%, #ffffff 100%);
            padding: 3rem 0;
        }
        .welcome-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s;
            border: 1px solid #eef2f6;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="dashboard-wrapper">
        <div class="container">
            <div class="mb-4">
                <h2 class="fw-bold">Dashboard</h2>
                <p class="text-muted">Your personalized learning overview and quick access to account actions.</p>
            </div>
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold">
                            Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 👋
                        </h1>
                        <p class="lead text-secondary mt-2">
                            Continue your learning journey with NextGen.
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                <i class="fas fa-user me-1"></i> <?php echo ucfirst($user['role']); ?>
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill ms-2">
                                <i class="fas fa-calendar me-1"></i> Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex justify-content-end">
                            <span class="text-muted small">
                                <i class="fas fa-shield-alt me-1"></i>Secure session
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-book-open fa-3x" style="color: var(--accent-blue);"></i>
                        <h3 class="mt-3 mb-0">0</h3>
                        <p class="text-secondary">Courses Enrolled</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-clock fa-3x" style="color: var(--accent-blue);"></i>
                        <h3 class="mt-3 mb-0">0</h3>
                        <p class="text-secondary">Hours Learned</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-certificate fa-3x" style="color: var(--accent-blue);"></i>
                        <h3 class="mt-3 mb-0">0</h3>
                        <p class="text-secondary">Certificates Earned</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="stats-card text-start">
                        <i class="fas fa-play-circle fa-2x mb-3" style="color: var(--accent-blue);"></i>
                        <h4>Continue Learning</h4>
                        <p class="text-secondary">Pick up where you left off with your recent courses.</p>
                        <a href="courses.php" class="btn btn-soft-primary mt-2">Browse Courses</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card text-start">
                        <i class="fas fa-trophy fa-2x mb-3" style="color: var(--accent-blue);"></i>
                        <h4>Your Achievements</h4>
                        <p class="text-secondary">Track your progress and earn certificates.</p>
                        <a href="profile.php" class="btn btn-outline-accent mt-2">View Profile →</a>
                    </div>
                </div>
            </div>

            <!-- Course Section -->
            <div class="mb-4">
                <h3 class="fw-bold">Recommended courses</h3>
                <p class="text-secondary">Explore the top learning paths selected for your growth.</p>
            </div>
            <div class="row g-4" id="course-section">
                <div class="col-lg-4">
                    <div class="course-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="mb-1">UI/UX Design</h5>
                                <span class="text-secondary">Beginner friendly</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill">12 hrs</span>
                        </div>
                        <p class="text-secondary">Learn the fundamentals of user experience, wireframing, and interface design.</p>
                        <div class="mt-4">
                            <span class="badge bg-success bg-opacity-15 text-success me-2">Live project</span>
                            <span class="badge bg-info bg-opacity-15 text-info">Mentor support</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="course-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="mb-1">Web Development</h5>
                                <span class="text-secondary">Hands-on track</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill">18 hrs</span>
                        </div>
                        <p class="text-secondary">Build modern responsive websites with HTML, CSS, JavaScript, and Bootstrap.</p>
                        <div class="mt-4">
                            <span class="badge bg-success bg-opacity-15 text-success me-2">Portfolio-ready</span>
                            <span class="badge bg-info bg-opacity-15 text-info">Flexible pace</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="course-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="mb-1">Career Launch</h5>
                                <span class="text-secondary">Success path</span>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill">10 hrs</span>
                        </div>
                        <p class="text-secondary">Get interview-ready with resume support, mock interviews, and career coaching.</p>
                        <div class="mt-4">
                            <span class="badge bg-success bg-opacity-15 text-success me-2">Job-ready</span>
                            <span class="badge bg-info bg-opacity-15 text-info">Expert review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>