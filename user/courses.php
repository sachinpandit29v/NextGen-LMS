<?php
// Start session to keep navbar state correct
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Check if user is admin, redirect to admin panel
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: ../admin/admin-panel.php");
    exit();
}

// Helper function to escape output
if (!function_exists('esc')) {
    function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// Fetch courses from database that are active and not archived
$courses = [];
try {
    $pdo = getDBConnection();
    // Show all courses except archived ones (includes draft and published)
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE status != 'archived' AND is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching courses: " . $e->getMessage());
    $courses = [];
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
    <title>NextGen | Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <section class="hero-alternate">
        <div class="container">
            <div class="row align-items-center gx-5">
                <div class="col-lg-7">
                    <span class="hero-badge">Course catalog</span>
                    <h1 class="hero-title mt-4">Explore in-demand courses built for career growth.</h1>
                    <p class="text-secondary mt-4" style="max-width: 680px; font-size: 1.05rem; line-height: 1.8;">NextGen courses are designed around practical skills, project work, and mentorship so learners can build real experience and stay confident from day one.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="#courses-list" class="btn btn-soft-primary">Browse courses</a>
                        <a href="contact.php" class="btn btn-outline-accent">Need help?</a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="course-card p-4">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">4.9</div>
                                    <div class="text-secondary small mt-2">Average rating</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">120+</div>
                                    <div class="text-secondary small mt-2">Courses live</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">30K</div>
                                    <div class="text-secondary small mt-2">Learners</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">97%</div>
                                    <div class="text-secondary small mt-2">Success rate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white" id="courses-list">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="fas fa-graduation-cap me-1"></i> Popular path</span>
                <h2 class="display-6 fw-bold mt-3">Featured courses</h2>
                <p class="text-secondary col-lg-8 mx-auto mt-3">Choose a path that matches your goals — from design and development to data and digital marketing.</p>
            </div>
            <div class="row g-4">
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="course-card p-4 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo esc($course['title']); ?></h5>
                                        <small class="text-secondary"><?php echo esc($course['level']); ?></small>
                                    </div>
                                    <i class="fas fa-graduation-cap fs-3 text-primary"></i>
                                </div>
                                <p class="text-secondary"><?php echo esc(substr($course['description'], 0, 100) . '...'); ?></p>
                                <div class="mt-4 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success bg-opacity-15 text-success py-2 px-3 rounded-pill"><?php echo esc($course['duration']); ?></span>
                                    <a href="course-details.php?id=<?php echo (int)$course['id']; ?>" class="btn btn-outline-accent btn-sm">View course</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <p class="mb-0">No courses available at the moment. Check back soon!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="py-5" style="background: var(--soft-bg);">
        <div class="container">
            <div class="row align-items-center gx-5">
                <div class="col-lg-7">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Learn with confidence</span>
                    <h2 class="fw-bold display-6 mt-3">Start your next course today</h2>
                    <p class="text-secondary mt-4" style="max-width: 620px; font-size: 1.05rem; line-height: 1.8;">Our courses are structured so you can learn fast, build practical skills, and move seamlessly from lessons to real-world work.</p>
                    <div class="mt-4">
                        <a href="#courses-list" class="btn btn-soft-primary me-3">View all courses</a>
                        <a href="register.php" class="btn btn-outline-accent">Join NextGen</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="course-card p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user-graduate fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">100% career support</h5>
                                <p class="text-secondary mb-0">Get guidance from mentors and career coaches every step of the way.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-clock fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">Flexible scheduling</h5>
                                <p class="text-secondary mb-0">Learn at your own pace with short lessons and clear milestones.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-rocket fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="mb-1">Launch faster</h5>
                                <p class="text-secondary mb-0">Move from study to real skills with structured course progress.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
