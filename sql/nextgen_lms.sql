-- NextGen LMS - Single SQL Setup File
-- Import this once (fresh install): creates DB, tables, and demo data.

CREATE DATABASE IF NOT EXISTS nextgen_lms;
USE nextgen_lms;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Demo Users (passwords: admin123, student123, demo123)
INSERT INTO users (first_name, last_name, email, password, role)
SELECT 'Admin', 'User', 'admin@nextgen.com', '$2y$10$lcaEQ1w9mVViNca9egtFkuPKnqw98UGuEJgPsd5Hw.Iebnkq7u9zq', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@nextgen.com');

INSERT INTO users (first_name, last_name, email, password, role)
SELECT 'Student', 'User', 'student@nextgen.com', '$2y$10$IjbbD3UTqt892Yw5QPXL9eRk5kc8SpRAsTqy3Bk/FpzRl0eETwKgq', 'student'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'student@nextgen.com');

INSERT INTO users (first_name, last_name, email, password, role)
SELECT 'Demo', 'User', 'demo@nextgen.com', '$2y$10$AXL7Ir4d5gXwumqrZGj./.uhnLaBi79Re6dWh6qOwD/fAefxu.eYO', 'student'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'demo@nextgen.com');

-- Password Reset Tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email)
);

-- User Sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    user_agent TEXT,
    ip_address VARCHAR(45),
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token)
);

-- Contacts
CREATE TABLE IF NOT EXISTS contacts (
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
);

-- Courses
CREATE TABLE IF NOT EXISTS courses (
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
);

-- Seed sample courses (idempotent)
INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active)
SELECT 'Full-Stack Web Development', 'Build modern web applications using PHP, MySQL, HTML, CSS, and JavaScript with real-world project experience.', 'Development', '12 weeks', 'intermediate', 'Ariana Nash', '', 'published', 1
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'Full-Stack Web Development');

INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active)
SELECT 'Data Analytics with Python', 'Learn data analysis, visualization, and reporting using Python, Pandas, and real datasets.', 'Data Science', '10 weeks', 'beginner', 'Jordan Lee', '', 'published', 1
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'Data Analytics with Python');

INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active)
SELECT 'UI/UX Design Essentials', 'Master design fundamentals, user research, and interface prototyping for modern web and mobile apps.', 'Design', '8 weeks', 'beginner', 'Maya Chen', '', 'published', 1
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'UI/UX Design Essentials');

INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active)
SELECT 'Cloud Infrastructure & DevOps', 'Deploy, monitor, and automate scalable applications using cloud platforms and DevOps best practices.', 'Cloud', '14 weeks', 'advanced', 'Noah Rivera', '', 'published', 1
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'Cloud Infrastructure & DevOps');

INSERT INTO courses (title, description, category, duration, level, instructor, image_url, status, is_active)
SELECT 'Digital Marketing Growth', 'Build brand awareness and drive conversions through SEO, social media, and paid advertising.', 'Marketing', '6 weeks', 'beginner', 'Lina Patel', '', 'published', 1
WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = 'Digital Marketing Growth');

-- Enrollments
CREATE TABLE IF NOT EXISTS enrollments (
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
);

