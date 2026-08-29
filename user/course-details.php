<?php
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Get course ID from URL parameter
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch course from database
$course = null;
if ($courseId > 0) {
    $course = getCourseById($courseId);
}

// If course not found, show error
if (!$course) {
    $course = [
        'title' => 'Course Not Found',
        'description' => 'The requested course could not be found. Please check our course catalog for available programs.',
        'category' => 'Unknown',
        'duration' => 'TBD',
        'level' => 'Unknown',
        'instructor' => 'TBD',
        'status' => 'Not Available'
    ];
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
    <title>NextGen | <?php echo htmlspecialchars($course['title']); ?></title>
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
                    <span class="hero-badge">Course preview</span>
                    <h1 class="hero-title mt-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                    <p class="text-secondary mt-4" style="max-width: 680px; font-size: 1.05rem; line-height: 1.8;">
                        <?php echo htmlspecialchars($course['description']); ?>
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <?php if ($course['status'] === 'published'): ?>
                            <a href="register.php" class="btn btn-soft-primary">Enroll Now</a>
                        <?php else: ?>
                            <a href="#" class="btn btn-soft-primary">Join Waitlist</a>
                        <?php endif; ?>
                        <a href="courses.php#courses-list" class="btn btn-outline-accent">Back to courses</a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="course-card p-4">
                        <h5 class="fw-semibold text-primary">Status</h5>
                        <p class="fs-4 fw-bold mb-3">
                            <span class="badge bg-<?php echo $course['status'] === 'published' ? 'success' : ($course['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                <?php echo ucfirst(htmlspecialchars($course['status'])); ?>
                            </span>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Duration</span>
                            <strong><?php echo htmlspecialchars($course['duration']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Level</span>
                            <strong><?php echo ucfirst(htmlspecialchars($course['level'])); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary">Category</span>
                            <strong><?php echo htmlspecialchars($course['category']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary">Instructor</span>
                            <strong><?php echo htmlspecialchars($course['instructor']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="course-card p-4">
                        <h3 class="fw-bold">What you’ll learn</h3>
                        <p class="text-secondary mb-4">This course is coming soon. While the full curriculum is still being finalized, here is what you can expect to master.</p>
                        <ul class="list-unstyled">
                            <?php if (!empty($course['details'])): ?>
                                <?php foreach ($course['details'] as $detail): ?>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><?php echo htmlspecialchars($detail); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="mb-3 text-secondary">More course details will be added soon. Stay tuned for the full syllabus.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="course-card p-4">
                        <h4 class="fw-bold">Course coming soon</h4>
                        <p class="text-secondary mb-4">We’re preparing this course with quality resources, practical lessons, and expert guidance. Register to be notified when enrollment opens.</p>
                        <a href="#" class="btn btn-soft-primary w-100 mb-3">Join waitlist</a>
                        <a href="contact.php" class="btn btn-outline-accent w-100">Ask support</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
