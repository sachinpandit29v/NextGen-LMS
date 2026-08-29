<?php
// Start session
session_start();

// Prevent caching to force re-authentication on back button
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Get and clear flash messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Get form data for repopulation on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_ajax_request = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Security validation failed. Please try again.";

        if ($is_ajax_request) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }

        $_SESSION['error'] = $message;
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '') ?: null;
    $address = trim($_POST['address'] ?? '') ?: null;
    $date_of_birth = trim($_POST['date_of_birth'] ?? '') ?: null;
    $bio = trim($_POST['bio'] ?? '') ?: null;

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $message = "First name, last name, and email are required.";

        if ($is_ajax_request) {
            header('Content-Type: application/json');
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }

        $_SESSION['error'] = $message;
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

        if ($is_ajax_request) {
            header('Content-Type: application/json');
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }

        $_SESSION['error'] = $message;
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Update user in database
        try {
            updateUser($_SESSION['user_id'], $first_name, $last_name, $email, $phone, $address, $date_of_birth, $bio);

            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = trim($first_name . ' ' . $last_name);

            $updated_user = getUserById($_SESSION['user_id']);
            $message = "Profile updated successfully!";

            if ($is_ajax_request) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'user' => $updated_user
                ]);
                exit();
            }

            $_SESSION['success'] = $message;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $message = "Error updating profile. Please try again.";

            if ($is_ajax_request) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $message]);
                exit();
            }

            $_SESSION['error'] = $message;
            $_SESSION['form_data'] = $_POST;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
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
    <title>NextGen | My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-wrapper {
            min-height: auto;
            background: linear-gradient(135deg, #f0f7fe 0%, #ffffff 100%);
            padding: 1.5rem 0;
        }
        .profile-card {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 18px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.2rem;
            border-bottom: 1px solid #eef2f6;
        }
        .profile-avatar {
            width: 96px;
            height: 96px;
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 18px rgba(27, 79, 110, 0.18);
        }
        .profile-avatar i {
            font-size: 2.2rem;
            color: white;
        }
        .info-card {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #eef2f6;
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.06);
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .info-value {
            font-size: 1rem;
            color: #0a2b3e;
            font-weight: 500;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-item {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: 1rem;
            padding: 1.1rem;
            text-align: center;
            border: 1px solid #eef2f6;
        }
        .stat-number {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 0.35rem;
        }
        .stat-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .edit-btn {
            background: linear-gradient(135deg, #0f2b3d, #1b4f6e);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            color: white;
            font-weight: 600;
            transition: all 0.2s;
            margin-top: 0.75rem;
            font-size: 0.95rem;
        }
        .edit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(27, 79, 110, 0.25);
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <?php if (!empty($success)): ?>
        <div id="profileAlert" class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (!empty($error)): ?>
        <div id="profileAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php else: ?>
        <div id="profileAlertContainer"></div>
    <?php endif; ?>

    <div class="profile-wrapper">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1 id="profileDisplayName" class="display-5 fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                    <p class="lead text-secondary mb-0">Welcome to your NextGen profile</p>
                </div>

                <!-- User Information -->
                <div class="d-flex flex-wrap gap-4 justify-content-center">
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">First Name</div>
                        <div id="profileFirstName" class="info-value"><?php echo htmlspecialchars($user['first_name']); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Last Name</div>
                        <div id="profileLastName" class="info-value"><?php echo htmlspecialchars($user['last_name']); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Email Address</div>
                        <div id="profileEmail" class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Phone Number</div>
                        <div id="profilePhone" class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Address</div>
                        <div id="profileAddress" class="info-value"><?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Date of Birth</div>
                        <div id="profileDateOfBirth" class="info-value"><?php echo htmlspecialchars($user['date_of_birth'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Bio</div>
                        <div id="profileBio" class="info-value"><?php echo htmlspecialchars($user['bio'] ?? 'Not provided'); ?></div>
                    </div>

                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Account Type</div>
                        <div class="info-value">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                <i class="fas fa-user me-1"></i><?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">
                            <i class="fas fa-calendar me-2 text-primary"></i>
                            <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    <div class="info-card flex-fill" style="min-width: 200px;">
                        <div class="info-label">Account Status</div>
                        <div class="info-value">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Courses Enrolled</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Hours Learned</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Certificates Earned</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-label">Completed Courses</div>
                    </div>
                </div>

                <!-- Edit Profile Button -->
                <div class="text-center">
                    <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProfileForm" method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($form_data['first_name'] ?? $user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($form_data['last_name'] ?? $user['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email'] ?? $user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone'] ?? $user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($form_data['address'] ?? $user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? $user['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($form_data['bio'] ?? $user['bio'] ?? ''); ?></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-soft-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('editProfileForm');
            const alertMount = document.getElementById('profileAlertContainer') || document.getElementById('profileAlert')?.parentElement;
            const modalElement = document.getElementById('editProfileModal');
            const saveButton = form?.querySelector('button[type="submit"]');

            if (!form || !window.fetch || !modalElement) {
                return;
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

            function getDisplayValue(value) {
                return value && String(value).trim() !== '' ? value : 'Not provided';
            }

            function showAlert(type, message) {
                const existingAlert = document.getElementById('profileAlert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                if (!alertMount) {
                    return;
                }

                const alert = document.createElement('div');
                alert.id = 'profileAlert';
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.setAttribute('role', 'alert');
                alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                alertMount.prepend(alert);
            }

            function updateText(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            }

            function updateNotificationBadge(notifications) {
                const element = document.getElementById('profileNotifications');
                if (element) {
                    const isEnabled = notifications == 1;
                    const badgeClass = isEnabled ? 'success' : 'warning';
                    const iconClass = isEnabled ? 'bell' : 'bell-slash';
                    element.innerHTML = `<span class="badge bg-${badgeClass} bg-opacity-10 text-${badgeClass} px-3 py-2 rounded-pill"><i class="fas fa-${iconClass} me-1"></i>${isEnabled ? 'Enabled' : 'Disabled'}</span>`;
                }
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const formData = new FormData(form);
                const originalButtonText = saveButton ? saveButton.innerHTML : '';

                if (saveButton) {
                    saveButton.disabled = true;
                    saveButton.textContent = 'Saving...';
                }

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Unable to update profile right now.');
                    }

                    const user = result.user || {};
                    const fullName = [user.first_name || '', user.last_name || ''].join(' ').trim();

                    updateText('profileDisplayName', fullName);
                    updateText('profileFirstName', user.first_name || '');
                    updateText('profileLastName', user.last_name || '');
                    updateText('profileEmail', user.email || '');
                    updateText('profilePhone', getDisplayValue(user.phone));
                    updateText('profileAddress', getDisplayValue(user.address));
                    updateText('profileDateOfBirth', getDisplayValue(user.date_of_birth));
                    updateText('profileBio', getDisplayValue(user.bio));

                    const navbarName = document.querySelector('#profileDropdown .d-none.d-md-inline');
                    if (navbarName && fullName) {
                        navbarName.textContent = fullName;
                    }

                    showAlert('success', result.message || 'Profile updated successfully!');
                    modalInstance.hide();
                } catch (error) {
                    showAlert('danger', error.message || 'Unable to update profile right now.');
                } finally {
                    if (saveButton) {
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalButtonText;
                    }
                }
            });
        })();
    </script>
</body>

</html>
