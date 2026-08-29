<?php
/**
 * Database Migration Script
 * Run this once to create contacts and courses tables
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL migration queries
    $migrations = [
        // Contacts table
        "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'resolved') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        )",
        
        // Courses table
        "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            duration VARCHAR(50),
            level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
            instructor VARCHAR(100),
            image_url VARCHAR(255),
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            is_active TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_category (category),
            INDEX idx_active (is_active)
        )",
        
        // Enrollments table
        "CREATE TABLE IF NOT EXISTS enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_id INT NOT NULL,
            progress INT DEFAULT 0,
            status ENUM('enrolled', 'in-progress', 'completed', 'dropped') DEFAULT 'enrolled',
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            UNIQUE KEY unique_enrollment (user_id, course_id),
            INDEX idx_user (user_id),
            INDEX idx_course (course_id),
            INDEX idx_status (status)
        )",

        // Supported profile fields for users table
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL",

        // Remove deprecated profile columns
        "ALTER TABLE users DROP COLUMN IF EXISTS profile_picture",
        "ALTER TABLE users DROP COLUMN IF EXISTS linkedin_url",
        "ALTER TABLE users DROP COLUMN IF EXISTS github_url",
        "ALTER TABLE users DROP COLUMN IF EXISTS twitter_url",
        "ALTER TABLE users DROP COLUMN IF EXISTS website_url",
        "ALTER TABLE users DROP COLUMN IF EXISTS education",
        "ALTER TABLE users DROP COLUMN IF EXISTS profession",
        "ALTER TABLE users DROP COLUMN IF EXISTS skills",
        "ALTER TABLE users DROP COLUMN IF EXISTS interests",
        "ALTER TABLE users DROP COLUMN IF EXISTS preferred_language",
        "ALTER TABLE users DROP COLUMN IF EXISTS timezone",
        "ALTER TABLE users DROP COLUMN IF EXISTS email_notifications",
        "ALTER TABLE users DROP COLUMN IF EXISTS profile_visibility"
    ];

    $results = [];
    foreach ($migrations as $i => $sql) {
        $pdo->exec($sql);
        $results[] = "✓ Migration " . ($i + 1) . " completed successfully";
    }

    // Seed sample courses when migration is run
    $seedCourses = [
        ['Full-Stack Web Development', 'Build modern web applications using PHP, MySQL, HTML, CSS, and JavaScript with real-world project experience.', 'Development', '12 weeks', 'intermediate', 'Ariana Nash', '', 'published', 1],
        ['Data Analytics with Python', 'Learn data analysis, visualization, and reporting using Python, Pandas, and real datasets.', 'Data Science', '10 weeks', 'beginner', 'Jordan Lee', '', 'published', 1],
        ['UI/UX Design Essentials', 'Master design fundamentals, user research, and interface prototyping for modern web and mobile apps.', 'Design', '8 weeks', 'beginner', 'Maya Chen', '', 'published', 1],
        ['Cloud Infrastructure & DevOps', 'Deploy, monitor, and automate scalable applications using cloud platforms and DevOps best practices.', 'Cloud', '14 weeks', 'advanced', 'Noah Rivera', '', 'published', 1],
        ['Digital Marketing Growth', 'Build brand awareness and drive conversions through SEO, social media, and paid advertising.', 'Marketing', '6 weeks', 'beginner', 'Lina Patel', '', 'published', 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($seedCourses as $course) {
        $check = $pdo->prepare("SELECT 1 FROM courses WHERE title = ? LIMIT 1");
        $check->execute([$course[0]]);
        if (!$check->fetch()) {
            $stmt->execute($course);
        }
    }

    // Success response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Database migration completed successfully',
        'results' => $results
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}
?>
