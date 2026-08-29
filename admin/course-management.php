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

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Get and clear flash messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Handle course creation
if (isset($_POST['add_course'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $title = trim($_POST['course_title'] ?? '');
        $description = trim($_POST['course_description'] ?? '');
        $category = trim($_POST['course_category'] ?? '');
        $duration = trim($_POST['course_duration'] ?? '');
        $level = trim($_POST['course_level'] ?? 'beginner');
        $instructor = trim($_POST['course_instructor'] ?? '');

        if (empty($title)) {
            $_SESSION['error'] = "Course title is required.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            try {
                if (createCourse($title, $description, $category, $duration, $level, $instructor)) {
                    $_SESSION['success'] = "Course created successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to create course. Please try again.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (Exception $e) {
                error_log("Course creation error: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while creating the course.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Handle course editing
if (isset($_POST['edit_course'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Security validation failed. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $course_id = (int)$_POST['course_id'];
        $title = trim($_POST['edit_course_title'] ?? '');
        $description = trim($_POST['edit_course_description'] ?? '');
        $category = trim($_POST['edit_course_category'] ?? '');
        $duration = trim($_POST['edit_course_duration'] ?? '');
        $level = trim($_POST['edit_course_level'] ?? 'beginner');
        $instructor = trim($_POST['edit_course_instructor'] ?? '');
        $status = trim($_POST['edit_course_status'] ?? 'draft');

        if (empty($title)) {
            $_SESSION['error'] = "Course title is required.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            try {
                if (updateCourse($course_id, $title, $description, $category, $duration, $level, $instructor, $status)) {
                    $_SESSION['success'] = "Course updated successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to update course.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (Exception $e) {
                error_log("Course update error: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while updating the course.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Handle course deletion
if (isset($_POST['delete_course']) && isset($_POST['course_id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $course_id = (int)$_POST['course_id'];

        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, title FROM courses WHERE id = ? AND is_active = 1");
            $stmt->execute([$course_id]);
            $course_to_delete = $stmt->fetch();

            if (!$course_to_delete) {
                $_SESSION['error'] = "Course not found or already deleted.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                if (deleteCourse($course_id)) {
                    $_SESSION['success'] = "Course deleted successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to delete course.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Course deletion error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while deleting the course.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Pagination and data fetching
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$search = trim($_GET['course_search'] ?? '');

try {
    $pdo = getDBConnection();

    // Build query with search
    $where_clause = "WHERE is_active = 1";
    $params = [];

    if (!empty($search)) {
        $where_clause .= " AND (title LIKE ? OR category LIKE ? OR instructor LIKE ? OR description LIKE ?)";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }

    // Get total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses $where_clause");
    $stmt->execute($params);
    $total_courses = $stmt->fetchColumn();

    // Get courses with pagination
    $stmt = $pdo->prepare("SELECT id, title, description, category, duration, level, instructor, status, created_at FROM courses $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $params[] = $per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $courses = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Error getting courses: " . $e->getMessage());
    $courses = [];
    $total_courses = 0;
}
$total_course_pages = ceil($total_courses / $per_page);

// Set current page for active sidebar link
$current_page = 'course-management.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Course Management | NextGen LMS</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom professional admin styling -->
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
            <!-- Dashboard greeting -->
            <!-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-secondary mb-0 fs-6">Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Admin'); ?> 👋</p>
                    <p class="text-muted small">Create and manage course offerings for your learners.</p>
                </div>
            </div> -->

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-admin">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-admin">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Course Management Card -->
            <div class="admin-card">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Course Management</h5>
                        <p class="mb-0 small opacity-75">Manage all courses, pricing, and content</p>
                    </div>
                    <button class="btn btn-admin-primary btn-header-action rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        Add Course
                    </button>
                </div>
                <div class="card-body p-4">
                    <!-- Course Search Form -->
                    <form method="GET" class="mb-4">
                        <div class="course-search-bar">
                            <div class="course-search-group">
                                <input type="text" name="course_search" class="form-control course-search-input" placeholder="Search courses by title, category, or instructor..." value="<?php echo htmlspecialchars($_GET['course_search'] ?? ''); ?>">
                                <button class="btn btn-admin-primary course-search-btn" type="submit">
                                    Search
                                </button>
                            </div>
                            <?php if (!empty($_GET['course_search'])): ?>
                                <a href="course-management.php" class="btn btn-admin-secondary course-search-reset">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="admin-table table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td class="fw-semibold">#<?php echo $course['id']; ?></td>
                                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                                        <td><?php echo htmlspecialchars($course['category'] ?: 'Uncategorized'); ?></td>
                                        <td>
                                            <span class="badge-admin">
                                                <?php echo ucfirst($course['level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-admin-<?php echo $course['status'] === 'published' ? 'success' : ($course['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-admin-secondary btn-sm btn-course-edit" data-bs-toggle="modal" data-bs-target="#editCourseModal" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                                                    Edit
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course permanently?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <button type="submit" name="delete_course" class="btn btn-admin-secondary btn-sm">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_course_pages > 1): ?>
                        <nav aria-label="Course pagination" class="mt-4">
                            <ul class="pagination pagination-admin justify-content-center">
                                <?php
                                $query_params = $_GET;
                                for ($i = 1; $i <= $total_course_pages; $i++):
                                    $query_params['page'] = $i;
                                    $url = 'course-management.php?' . http_build_query($query_params);
                                ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <?php if (empty($courses)): ?>
                        <div class="text-center py-5">
                            <h5 class="text-muted">No courses found</h5>
                            <p class="text-muted">Try adjusting your search criteria or add a new course</p>
                            <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                Add First Course
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
</div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-admin-header-primary">
                <h5 class="modal-title">Add New Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="form-admin">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Course Title *</label>
                                <input type="text" name="course_title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="course_category" class="form-control" placeholder="e.g., Programming, Design">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="course_duration" class="form-control" placeholder="e.g., 2 hours, 1 week">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <select name="course_level" class="form-select">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructor</label>
                                <input type="text" name="course_instructor" class="form-control" placeholder="Course instructor name">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="course_description" class="form-control" rows="3" placeholder="Course description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" name="add_course" class="btn btn-admin-primary">
                        Add Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-admin-header-warning">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="form-admin">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="course_id" id="edit_course_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Course Title *</label>
                                <input type="text" name="edit_course_title" id="edit_course_title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="edit_course_category" id="edit_course_category" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="edit_course_duration" id="edit_course_duration" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Level</label>
                                <select name="edit_course_level" id="edit_course_level" class="form-select">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructor</label>
                                <input type="text" name="edit_course_instructor" id="edit_course_instructor" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="edit_course_status" id="edit_course_status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="edit_course_description" id="edit_course_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" name="edit_course" class="btn btn-admin-primary">
                        Update Course
                    </button>
                </div>
            </form>
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

    function editCourse(course) {
        document.getElementById('edit_course_id').value = course.id;
        document.getElementById('edit_course_title').value = course.title;
        document.getElementById('edit_course_description').value = course.description || '';
        document.getElementById('edit_course_category').value = course.category || '';
        document.getElementById('edit_course_duration').value = course.duration || '';
        document.getElementById('edit_course_level').value = course.level;
        document.getElementById('edit_course_instructor').value = course.instructor || '';
        document.getElementById('edit_course_status').value = course.status;
    }
</script>
</body>
</html>
