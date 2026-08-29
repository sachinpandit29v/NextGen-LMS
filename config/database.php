<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nextgen_lms');
define('DB_USER', 'root');        // Change to your database username
define('DB_PASS', '');            // Change to your database password

// Create connection function
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database Connection Failed: " . $e->getMessage());
    }
}

function getTableColumns($table) {
    static $table_columns = [];

    if (isset($table_columns[$table])) {
        return $table_columns[$table];
    }

    $pdo = getDBConnection();
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
    $columns = [];

    foreach ($stmt->fetchAll() as $column) {
        $columns[] = $column['Field'];
    }

    $table_columns[$table] = $columns;
    return $columns;
}

function tableHasColumn($table, $column) {
    return in_array($column, getTableColumns($table), true);
}

// CSRF Protection Functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// Rate Limiting Functions
function checkRateLimit($action, $limit = 5, $window = 900) { // 5 attempts per 15 minutes
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 0, 'reset_time' => time() + $window];
    }
    
    $rate_data = &$_SESSION['rate_limit'][$key];
    
    // Reset if window has passed
    if (time() > $rate_data['reset_time']) {
        $rate_data['count'] = 0;
        $rate_data['reset_time'] = time() + $window;
    }
    
    // Check if limit exceeded
    if ($rate_data['count'] >= $limit) {
        return false;
    }
    
    $rate_data['count']++;
    return true;
}

function getRateLimitResetTime($action) {
    $key = $action . '_' . $_SERVER['REMOTE_ADDR'];
    return $_SESSION['rate_limit'][$key]['reset_time'] ?? time();
}

// Get single user by email
function getUserByEmail($email) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Get single user by ID
function getUserById($id) {
    $pdo = getDBConnection();

    // Build column list dynamically based on what exists
    $columns = ['id', 'first_name', 'last_name', 'email', 'role', 'created_at'];

    // Add optional columns only if they exist in the database
    $optional_columns = ['phone', 'address', 'date_of_birth', 'bio'];
    foreach ($optional_columns as $col) {
        // Use INFORMATION_SCHEMA to check column existence instead of tableHasColumn to avoid caching issues
        try {
            $check = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = ?");
            $check->execute([$col]);
            if ($check->fetchColumn()) {
                $columns[] = $col;
            }
        } catch (Exception $e) {
            // If check fails, skip this column
        }
    }

    try {
        $sql = "SELECT " . implode(', ', $columns) . " FROM users WHERE id = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            // Set defaults for optional fields
            foreach ($optional_columns as $col) {
                if (!isset($user[$col])) {
                    $user[$col] = null;
                }
            }
        }

        return $user;
    } catch (Exception $e) {
        error_log("getUserById error: " . $e->getMessage());
        return null;
    }
}

// Create new user
function createUser($first_name, $last_name, $email, $password, $role = 'student') {
    $pdo = getDBConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role]);
}

// Check if email exists
function emailExists($email) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

// Verify user login
function verifyUserLogin($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Update last login time
function updateLastLogin($user_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$user_id]);
}

// Update user profile
function updateUser($user_id, $first_name, $last_name, $email, $phone = null, $address = null, $date_of_birth = null, $bio = null) {
    $pdo = getDBConnection();
    
    $update_clauses = [];
    $params = [];
    
    // Always update these fields
    $update_clauses[] = 'first_name = ?';
    $params[] = $first_name;
    
    $update_clauses[] = 'last_name = ?';
    $params[] = $last_name;
    
    $update_clauses[] = 'email = ?';
    $params[] = $email;
    
    // Update optional fields if they exist in the database
    if (tableHasColumn('users', 'phone')) {
        $update_clauses[] = 'phone = ?';
        $params[] = $phone;
    }

    if (tableHasColumn('users', 'address')) {
        $update_clauses[] = 'address = ?';
        $params[] = $address;
    }

    if (tableHasColumn('users', 'date_of_birth')) {
        $update_clauses[] = 'date_of_birth = ?';
        $params[] = $date_of_birth;
    }

    if (tableHasColumn('users', 'bio')) {
        $update_clauses[] = 'bio = ?';
        $params[] = $bio;
    }

    if (tableHasColumn('users', 'updated_at')) {
        $update_clauses[] = 'updated_at = NOW()';
    }

    // Add user_id as final parameter for WHERE clause
    $params[] = $user_id;

    // Build and execute the UPDATE statement
    $sql = "UPDATE users SET " . implode(', ', $update_clauses) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);
    
    if (!$result) {
        error_log("Update failed - SQL: $sql | Params: " . json_encode($params) . " | Error: " . json_encode($stmt->errorInfo()));
    }
    
    return $result;
}

// Update user password
function updatePassword($user_id, $new_password) {
    $pdo = getDBConnection();
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}

// Delete user account (hard delete - completely remove from database)
function deleteUser($user_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->rowCount() > 0;
}

// Password Reset Functions
function createPasswordResetToken($email, $token, $expires_hours = 24) {
    $pdo = getDBConnection();
    
    // First, delete any existing tokens for this email
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    
    // Create new token
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_hours} hours"));
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $token, $expires_at]);
}

function getPasswordResetToken($token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function deletePasswordResetToken($token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
    return $stmt->execute([$token]);
}

// User Session Functions (for remember me functionality)
function createUserSession($user_id, $session_token, $user_agent, $ip_address, $expires_days = 30) {
    $pdo = getDBConnection();
    
    // Delete any existing sessions for this user
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Create new session
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $session_token, $user_agent, $ip_address, $expires_at]);
}

function getUserSession($session_token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT us.*, u.id as user_id, u.first_name, u.last_name, u.email, u.role, u.is_active 
        FROM user_sessions us 
        JOIN users u ON us.user_id = u.id 
        WHERE us.session_token = ? AND us.expires_at > NOW() AND u.is_active = 1
    ");
    $stmt->execute([$session_token]);
    return $stmt->fetch();
}

function deleteUserSession($session_token) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
    return $stmt->execute([$session_token]);
}

function deleteExpiredSessions() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE expires_at <= NOW()");
    return $stmt->execute();
}

// Get all users (for admin functionality)
function getAllUsers($limit = null, $offset = 0) {
    $pdo = getDBConnection();
    $sql = "SELECT id, first_name, last_name, email, role, is_active, created_at, updated_at FROM users WHERE is_active = 1 ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// Get user count
function getUserCount() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Search users
function searchUsers($search_term, $limit = null, $offset = 0) {
    $pdo = getDBConnection();
    $sql = "SELECT id, first_name, last_name, email, role, is_active, created_at, updated_at 
            FROM users 
            WHERE is_active = 1 AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?) 
            ORDER BY created_at DESC";
    
    $search_param = "%{$search_term}%";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_param, $search_param, $search_param, $limit, $offset]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_param, $search_param, $search_param]);
    }
    
    return $stmt->fetchAll();
}

// ==================== CONTACT FUNCTIONS ====================

// Create new contact submission
function createContact($name, $email, $phone, $subject, $message) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $phone, $subject, $message]);
}

// Get all contacts with pagination
function getAllContacts($limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

// Get contact count
function getContactCount() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Get single contact by ID
function getContactById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Update contact status (new, read, resolved)
function updateContactStatus($id, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

// Delete contact
function deleteContact($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    return $stmt->execute([$id]);
}

// Get contacts by status
function getContactsByStatus($status, $limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$status, $limit, $offset]);
    return $stmt->fetchAll();
}

// ==================== COURSE FUNCTIONS ====================

// Create new course
function createCourse($title, $description, $category, $duration, $level, $instructor) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, duration, level, instructor, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    if ($stmt->execute([$title, $description, $category, $duration, $level, $instructor])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// Get all courses
function getAllCourses($limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

// Get all courses (including inactive - for admin)
function getAllCoursesAdmin($limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

// Get course count
function getCourseCount() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE is_active = 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Get single course by ID
function getCourseById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Update course
function updateCourse($id, $title, $description, $category, $duration, $level, $instructor, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, category = ?, duration = ?, level = ?, instructor = ?, status = ? WHERE id = ?");
    return $stmt->execute([$title, $description, $category, $duration, $level, $instructor, $status, $id]);
}

// Delete course (hard delete - completely remove from database)
function deleteCourse($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

// Get courses by category
function getCoursesByCategory($category, $limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE category = ? AND is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$category, $limit, $offset]);
    return $stmt->fetchAll();
}

// Get courses by level
function getCoursesByLevel($level, $limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE level = ? AND is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$level, $limit, $offset]);
    return $stmt->fetchAll();
}

// ==================== ENROLLMENT FUNCTIONS ====================

// Enroll user in course
function enrollUserInCourse($user_id, $course_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES (?, ?, 'enrolled')");
    return $stmt->execute([$user_id, $course_id]);
}

// Check if user is enrolled in course
function isUserEnrolled($user_id, $course_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    return $stmt->fetchColumn() > 0;
}

// Get user enrollments
function getUserEnrollments($user_id, $limit = 10, $offset = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT c.*, e.progress, e.status, e.enrolled_at 
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.user_id = ? AND e.status != 'dropped'
        ORDER BY e.enrolled_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    return $stmt->fetchAll();
}

// Update enrollment progress
function updateEnrollmentProgress($user_id, $course_id, $progress) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?");
    return $stmt->execute([$progress, $user_id, $course_id]);
}

// Mark course as completed
function markCourseCompleted($user_id, $course_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'completed', progress = 100, completed_at = NOW() WHERE user_id = ? AND course_id = ?");
    return $stmt->execute([$user_id, $course_id]);
}

?>
