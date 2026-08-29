<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>NextGen | About</title>
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
                    <span class="hero-badge">Our story</span>
                    <h1 class="hero-title mt-4">NextGen helps learners build future-ready skills and launch careers.</h1>
                    <p class="text-secondary mt-4" style="max-width: 640px; font-size: 1.05rem; line-height: 1.8;">We combine practical projects, expert mentorship, and career support so every student can grow with confidence. Our focus is on real outcomes, practical experience, and a streamlined learning experience.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="home.php" class="btn btn-soft-primary">Explore courses</a>
                        <a href="contact.php" class="btn btn-outline-accent">Contact support</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="course-card p-4">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">120+</div>
                                    <div class="text-secondary small">Courses live</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">95%</div>
                                    <div class="text-secondary small">Graduate satisfaction</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">30k</div>
                                    <div class="text-secondary small">Learners enrolled</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="fs-3 fw-bold text-primary">24/7</div>
                                    <div class="text-secondary small">Learning support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-5">
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-lightbulb"></i></div>
                        <h5>Practical learning</h5>
                        <p class="text-secondary">Every module is built around real projects that students complete from start to finish.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h5>Expert mentorship</h5>
                        <p class="text-secondary">Get personal guidance, feedback, and actionable coaching from seasoned instructors.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-rocket"></i></div>
                        <h5>Career-focused</h5>
                        <p class="text-secondary">We help learners build strong portfolios, write better resumes, and prepare for interviews.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-5">
                <div class="col-lg-4">
                    <div class="stat-card">
                        <div class="fs-2 fw-bold text-primary">Courses</div>
                        <div class="text-secondary mt-2">Hands-on curriculum with immediate application.</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stat-card">
                        <div class="fs-2 fw-bold text-primary">Mentors</div>
                        <div class="text-secondary mt-2">Career-experienced coaches supporting every learner.</div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stat-card">
                        <div class="fs-2 fw-bold text-primary">Results</div>
                        <div class="text-secondary mt-2">Outcome-driven progress from day one.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>