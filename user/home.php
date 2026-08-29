<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>NextGen | Online Learning Platform — Modern UI</title>
    <!-- Bootstrap 5 + Icons + Google Fonts (Inter) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Hero Section - alternate modern design -->
    <section id="homeAlt" class="hero-alternate">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-1">
                    <div class="hero-badge mb-3">
                        <i class="fas fa-rocket me-1"></i> Launch your career 2.0
                    </div>
                    <h1 class="hero-title">Unlock your <span class="gradient-text">potential</span> with world-class online learning</h1>
                    <p class="lead text-secondary my-4">Interactive courses, live mentorship, and career-focused paths designed for the modern learner. Join 15,000+ students advancing their futures.</p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="#" class="btn btn-soft-primary px-4 py-3">Start Learning Free →</a>
                        <a href="#" class="btn btn-outline-accent px-4 py-3">View Programs</a>
                    </div>
                    <div class="row g-3 mt-2 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="stat-card">
                                <h3 class="fw-bold text-primary mb-0">100+</h3>
                                <small class="text-muted">Expert courses</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stat-card">
                                <h3 class="fw-bold text-primary mb-0">94%</h3>
                                <small class="text-muted">job placement</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="stat-card">
                                <h3 class="fw-bold text-primary mb-0">24/7</h3>
                                <small class="text-muted">mentor support</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center text-lg-end order-lg-2">
                    <div class="position-relative">
                        <img src="https://placehold.co/600x500/ffffff/2d9cdb?text=🎧+Learn+Anywhere" alt="student learning" class="img-fluid rounded-4 shadow-lg hero-image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section (benefits) with new card layout -->
    <section id="coursesAlt" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="fas fa-gem me-1"></i> premium experience</span>
                <h2 class="display-5 fw-bold mt-3">Designed for your <span class="gradient-text">growth</span></h2>
                <p class="text-secondary col-lg-7 mx-auto">Everything you need to thrive in a fast-changing world — all in one platform.</p>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-calendar-alt"></i></div>
                        <h4 class="fw-bold">Flexible Learning</h4>
                        <p class="text-secondary">Self-paced modules, mobile-friendly interface, and lifetime access to materials — learn when it suits you.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-chalkboard-user"></i></div>
                        <h4 class="fw-bold">Expert Instructors</h4>
                        <p class="text-secondary">Industry veterans from Google, Microsoft, and top universities guide you through real-world projects.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-puzzle-piece"></i></div>
                        <h4 class="fw-bold">Interactive Content</h4>
                        <p class="text-secondary">Quizzes, hands-on labs, AI code checker, and peer-reviewed assignments that keep you engaged.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-award"></i></div>
                        <h4 class="fw-bold">Global Certification</h4>
                        <p class="text-secondary">Earn industry-recognized certificates to boost your resume and LinkedIn profile instantly.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-users-viewfinder"></i></div>
                        <h4 class="fw-bold">Career Accelerator</h4>
                        <p class="text-secondary">Resume reviews, mock interviews, and exclusive job fairs with 300+ partner companies.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="feature-modern">
                        <div class="icon-circle"><i class="fas fa-brain"></i></div>
                        <h4 class="fw-bold">AI Study Assistant</h4>
                        <p class="text-secondary">Get personalized recommendations and 24/7 AI tutor to answer your coding & theory questions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section (reimagined with stats + mission) -->
    <section id="aboutAlt" class="py-5" style="background: linear-gradient(120deg, #f8fafc 0%, #ffffff 100%);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-2">
                    <div class="bg-white p-3 rounded-4 shadow-sm">
                        <img src="https://placehold.co/600x500/eef3fc/1b6b93?text=NextGen+Community" alt="About NextGen" class="img-fluid rounded-4 w-100">
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill mb-3"><i class="fas fa-flag-checkered me-1"></i> Our story</span>
                    <h2 class="fw-bold display-6">Where ambition meets <span class="gradient-text">excellence</span></h2>
                    <p class="lead text-secondary mt-3">NextGen was founded to remove barriers to quality education. We combine adaptive technology with human touch to deliver life-changing outcomes.</p>
                    <div class="row mt-4 g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle text-primary fs-5"></i> <span>1:1 mentorship</span></div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle text-primary fs-5"></i> <span>Project-based learning</span></div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle text-primary fs-5"></i> <span>Career coaching</span></div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle text-primary fs-5"></i> <span>Flexible payment plans</span></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-2">
                        <div class="d-flex gap-4">
                            <div><strong class="fs-2 text-primary">10K+</strong><br><span class="text-muted">Active learners</span></div>
                            <div><strong class="fs-2 text-primary">97%</strong><br><span class="text-muted">Satisfaction rate</span></div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-soft-primary mt-4">Discover our mission →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials (grid style) -->
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><i class="fas fa-star me-1"></i> learner love</span>
                <h2 class="display-5 fw-bold mt-2">Success stories that <span class="gradient-text">inspire</span></h2>
                <p class="text-secondary col-md-8 mx-auto">Hear from our global community of alumni who transformed their careers with NextGen.</p>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="testimonial-modern">
                        <div class="rating-star mb-2">★★★★★</div>
                        <p class="fst-italic text-secondary">“The full-stack web development bootcamp changed my life. After 4 months, I got hired as a frontend developer. The projects were portfolio-worthy and mentors were always available.”</p>
                        <div class="d-flex mt-3 align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3" style="width:48px;height:48px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-user-circle fa-2x text-secondary"></i></div>
                            <div><strong>Emily Rodriguez</strong><br><small class="text-muted">Frontend Dev @ TechStart</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="testimonial-modern">
                        <div class="rating-star mb-2">★★★★★</div>
                        <p class="fst-italic text-secondary">“The data science track gave me hands-on experience with real datasets. The career team helped me land a data analyst role within weeks. Highly recommended!”</p>
                        <div class="d-flex mt-3 align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3" style="width:48px;height:48px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-chart-line fa-2x text-secondary"></i></div>
                            <div><strong>James Kim</strong><br><small class="text-muted">Data Analyst @ Insightful</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="testimonial-modern">
                        <div class="rating-star mb-2">★★★★★</div>
                        <p class="fst-italic text-secondary">“As a working parent, flexibility was key. NextGen’s self-paced UI/UX course gave me the confidence to switch careers. Now I lead design at a startup!”</p>
                        <div class="d-flex mt-3 align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3" style="width:48px;height:48px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-paintbrush-fine fa-2x text-secondary"></i></div>
                            <div><strong>Sophia M.</strong><br><small class="text-muted">Product Designer</small></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- extra CTA inside testimonials -->
            <div class="text-center mt-5">
                <a href="#" class="btn btn-outline-accent px-5">Read 500+ success stories →</a>
            </div>
        </div>
    </section>

    <!-- Contact Section with form + alternative style -->
    <section id="contactAlt" class="py-5" style="background: var(--soft-bg);">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="display-6 fw-bold">Let’s start your journey</h2>
                            <p class="text-secondary">Have questions? Our team is ready to help you find the perfect learning path.</p>
                        </div>
                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <div class="d-flex flex-column align-items-center text-center p-3">
                                    <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                    <h5>Email</h5>
                                    <p class="text-muted">hello@nextgen.com</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-flex flex-column align-items-center text-center p-3">
                                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                                    <h5>Live support</h5>
                                    <p class="text-muted">Mon-Fri 9am-6pm EST</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-flex flex-column align-items-center text-center p-3">
                                    <i class="fab fa-slack fa-2x text-primary mb-2"></i>
                                    <h5>Community chat</h5>
                                    <p class="text-muted">Join our Slack hub</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-soft-primary px-5 py-3">Get free consultation →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer with social links & contact info (consistent palette) -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // smooth scroll for anchor links with offset handling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === "#" || targetId === "") return;
                const targetElem = document.querySelector(targetId);
                if (targetElem) {
                    e.preventDefault();
                    targetElem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    history.pushState(null, null, targetId);
                }
            });
        });
    </script>
</body>

</html>