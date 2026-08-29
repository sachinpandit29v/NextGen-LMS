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

// Handle user deletion
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    // Check rate limit for admin actions
    if (!checkRateLimit('admin_action', 10, 60)) { // 10 actions per minute
        $_SESSION['error'] = "Too many actions. Please wait " . getRateLimitResetTime('admin_action') . " seconds.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $user_id = (int)$_POST['user_id'];
        // Check if user exists and is active
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            $user_to_delete = $stmt->fetch();
        } catch (Exception $e) {
            $user_to_delete = null;
        }

        if (!$user_to_delete) {
            $_SESSION['error'] = "User not found or already deleted.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } elseif ($user_id === $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            try {
                if (deleteUser($user_id)) {
                    $_SESSION['success'] = "User deleted successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to delete user.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (Exception $e) {
                error_log("User deletion error: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while deleting the user.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Pagination and data fetching
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role, is_active, created_at, updated_at FROM users WHERE is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$per_page, $offset]);
    $users = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Error getting users: " . $e->getMessage());
    $users = [];
    $total_users = 0;
}
$total_pages = ceil($total_users / $per_page);

// Handle search
$search_term = $_GET['search'] ?? '';
if (!empty($search_term)) {
    $users = searchUsers($search_term, $per_page, $offset);
    // Get total count for search results
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1 AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)");
    $search_param = "%{$search_term}%";
    $stmt->execute([$search_param, $search_param, $search_param]);
    $total_users = $stmt->fetchColumn();
    $total_pages = ceil($total_users / $per_page);
}

// Set current page for active sidebar link
$current_page = 'user-management.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>User Management | NextGen LMS</title>
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
                    <p class="text-muted small">Manage user accounts and permissions efficiently.</p>
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

            <!-- User Management Card -->
            <div class="admin-card">
                <div class="admin-header">
                    <h5 class="mb-0">User Management</h5>
                    <p class="mb-0 small opacity-75">Manage all registered users and their permissions</p>
                </div>
                <div class="card-body p-4">
                    <!-- Search Form -->
                    <form method="GET" class="mb-4">
                        <div class="course-search-bar">
                            <div class="course-search-group">
                                <input type="text" name="search" class="form-control course-search-input" placeholder="Search users by name or email..." value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-admin-primary course-search-btn" type="submit">
                                    Search
                                </button>
                            </div>
                            <?php if (!empty($search_term)): ?>
                                <a href="user-management.php" class="btn btn-admin-secondary course-search-reset">
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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td class="fw-semibold">#<?php echo $u['id']; ?></td>
                                        <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge-admin">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-admin-success">Active</span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone!')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-admin-secondary btn-sm">
                                                         Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">Cannot delete self</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="User pagination" class="mt-4">
                            <ul class="pagination pagination-admin justify-content-center">
                                <?php
                                $query_params = $_GET;
                                for ($i = 1; $i <= $total_pages; $i++):
                                    $query_params['page'] = $i;
                                    $url = 'user-management.php?' . http_build_query($query_params);
                                ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <h5 class="text-muted">No users found</h5>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    <?php endif; ?>
                </div>
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
