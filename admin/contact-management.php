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

// Handle automatic contact status update (when viewing)
if (isset($_POST['auto_update_status']) && isset($_POST['contact_id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit();
    } else {
        $contact_id = (int)$_POST['contact_id'];
        
        try {
            if (updateContactStatus($contact_id, 'read')) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
        } catch (Exception $e) {
            error_log("Auto status update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
        exit();
    }
}

// Handle contact status update
if (isset($_POST['update_contact_status']) && isset($_POST['contact_id']) && isset($_POST['contact_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $contact_id = (int)$_POST['contact_id'];
        $status = trim($_POST['contact_status']);

        if (in_array($status, ['new', 'read', 'resolved'])) {
            try {
                if (updateContactStatus($contact_id, $status)) {
                    $_SESSION['success'] = "Contact status updated successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to update contact status.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (Exception $e) {
                error_log("Contact status update error: " . $e->getMessage());
                $_SESSION['error'] = "An error occurred while updating contact status.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid status value.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Pagination and data fetching
$contact_page = isset($_GET['contact_page']) ? (int)$_GET['contact_page'] : 1;
$contact_per_page = 10;
$contact_offset = ($contact_page - 1) * $contact_per_page;

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$contact_per_page, $contact_offset]);
    $contacts = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts");
    $stmt->execute();
    $total_contacts = $stmt->fetchColumn();
} catch (Exception $e) {
    error_log("Error getting contacts: " . $e->getMessage());
    $contacts = [];
    $total_contacts = 0;
}
$total_contact_pages = ceil($total_contacts / $contact_per_page);

// Set current page for active sidebar link
$current_page = 'contact-management.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Contact Management | NextGen LMS</title>
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
                    <p class="text-muted small">Review and manage user inquiries and support requests.</p>
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

            <!-- Contact Management Card -->
            <div class="admin-card">
                <div class="admin-header">
                    <h5 class="mb-0">Contact Form Submissions</h5>
                    <p class="mb-0 small opacity-75">Manage user inquiries and support requests</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="admin-table table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr data-contact-id="<?php echo $contact['id']; ?>">
                                        <td class="fw-semibold">#<?php echo $contact['id']; ?></td>
                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                        <td>
                                            <span class="badge badge-admin-<?php echo $contact['status'] === 'resolved' ? 'success' : ($contact['status'] === 'read' ? 'info' : 'warning'); ?>">
                                                <?php echo ucfirst($contact['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($contact['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-admin-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewContactModal" onclick="viewContact(<?php echo htmlspecialchars(json_encode($contact)); ?>)">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_contact_pages > 1): ?>
                        <nav aria-label="Contact pagination" class="mt-4">
                            <ul class="pagination pagination-admin justify-content-center">
                                <?php
                                $query_params = $_GET;
                                for ($i = 1; $i <= $total_contact_pages; $i++):
                                    $query_params['contact_page'] = $i;
                                    $url = 'contact-management.php?' . http_build_query($query_params);
                                ?>
                                    <li class="page-item <?php echo $i === $contact_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $url; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <?php if (empty($contacts)): ?>
                        <div class="text-center py-5">
                            <h5 class="text-muted">No contact submissions found</h5>
                            <p class="text-muted">Contact form submissions will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
</div>
</div>

<!-- View Contact Modal -->
<div class="modal fade" id="viewContactModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header modal-admin-header-info">
                <h5 class="modal-title">Contact Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">Contact Information</h6>
                                <p class="mb-2"><strong>Name:</strong> <span id="contact_name" class="text-dark"></span></p>
                                <p class="mb-2"><strong>Email:</strong> <span id="contact_email" class="text-dark"></span></p>
                                <p class="mb-0"><strong>Phone:</strong> <span id="contact_phone" class="text-dark"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">Submission Details</h6>
                                <p class="mb-2"><strong>Subject:</strong> <span id="contact_subject" class="text-dark"></span></p>
                                <p class="mb-2"><strong>Status:</strong> <span id="contact_status_badge"></span></p>
                                <p class="mb-0"><strong>Date:</strong> <span id="contact_date" class="text-dark"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3">Message</h6>
                            <div class="border p-3 bg-white rounded" id="contact_message" style="white-space: pre-wrap;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-admin-secondary" data-bs-dismiss="modal">
                    Close
                </button>
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

    function viewContact(contact) {
        // Update modal content
        document.getElementById('contact_name').textContent = contact.name;
        document.getElementById('contact_email').textContent = contact.email;
        document.getElementById('contact_phone').textContent = contact.phone || 'Not provided';
        document.getElementById('contact_subject').textContent = contact.subject;
        document.getElementById('contact_date').textContent = new Date(contact.created_at).toLocaleString();
        document.getElementById('contact_message').textContent = contact.message;

        // Status badge - initially show current status
        const statusBadge = document.getElementById('contact_status_badge');
        const statusClass = contact.status === 'resolved' ? 'success' : (contact.status === 'read' ? 'info' : 'warning');
        statusBadge.className = `badge badge-admin-${statusClass}`;
        statusBadge.textContent = contact.status.charAt(0).toUpperCase() + contact.status.slice(1);

        // Automatically mark as read if it's currently new
        if (contact.status === 'new') {
            // Make AJAX call to update status
            fetch('contact-management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'auto_update_status': '1',
                    'contact_id': contact.id,
                    'csrf_token': '<?php echo $csrf_token; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the status badge to show "Read"
                    statusBadge.className = 'badge badge-admin-info';
                    statusBadge.textContent = 'Read';

                    // Update the status in the table row
                    const row = document.querySelector(`tr[data-contact-id="${contact.id}"]`);
                    if (row) {
                        const statusCell = row.querySelector('td:nth-child(5) .badge');
                        if (statusCell) {
                            statusCell.className = 'badge badge-admin-info';
                            statusCell.textContent = 'Read';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error updating contact status:', error);
            });
        }
    }
</script>
</body>
</html>
