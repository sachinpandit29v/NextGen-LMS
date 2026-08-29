<?php
// Start session
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - NextGen LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Include Navigation Bar -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- 404 Page -->
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                <h1 class="display-4 fw-bold text-primary mb-3">404</h1>
                <h2 class="h3 mb-3">Page Not Found</h2>
                <p class="lead text-muted mb-4">
                    The page you're looking for doesn't exist or has been moved.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="home.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>