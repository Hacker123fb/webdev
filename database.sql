-- ========================================================
-- Database Creation Script for Library Management System
-- Course: Semester V Web Development Mini Project
-- Database Engine: MySQL / MariaDB (XAMPP / WAMP)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `library_management`
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `library_management`;

-- --------------------------------------------------------
-- Table 1: users
-- Used for authentication and role-based access control.
-- Passwords must be hashed using PHP password_hash().
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table 2: students
-- Stores profile and academic details of registered students.
-- user_id links a student record 1-to-1 to a login account in users.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL UNIQUE,
    `student_id` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `semester` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table 3: books
-- Stores the catalog of books in the library.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `book_name` VARCHAR(150) NOT NULL,
    `author` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `isbn` VARCHAR(30) NOT NULL UNIQUE,
    `publisher` VARCHAR(100) NOT NULL,
    `total_copies` INT NOT NULL DEFAULT 1,
    `available_copies` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table 4: issued_books
-- Tracks book circulation, due dates, returns, and statuses.
-- Foreign keys link to students(id) and books(id).
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `issued_books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `return_date` DATE NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'issued',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_issued_student` 
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_issued_book` 
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample Seed Data
-- 
-- Default Test Logins:
-- Admin:   username = admin   | password = admin123
-- Student: username = student | password = student123
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$gjOzGlQIHHxoNC39UeIwhOFzmCMfyG41VrRM415n8swG1C/DC1hBi', 'admin'),
('student', '$2y$10$QnL8uLQzzEjNaURHW4TTQuYC81JLeMTXx9WVjPI59MBEOJsLodgAC', 'student')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed students: राहुल शर्मा (STU-2026-001) is linked to user id 2 ('student')
INSERT INTO `students` (`user_id`, `student_id`, `name`, `email`, `phone`, `department`, `semester`) VALUES
(2, 'STU-2026-001', 'Rahul Sharma', 'rahul.sharma@example.edu', '9876543210', 'Computer Engineering', 5),
(NULL, 'STU-2026-002', 'Priya Patel', 'priya.patel@example.edu', '9876543211', 'Information Technology', 5)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `books` (`book_name`, `author`, `category`, `isbn`, `publisher`, `total_copies`, `available_copies`) VALUES
('Database System Concepts', 'Abraham Silberschatz', 'Computer Science', '9780073523323', 'McGraw-Hill', 5, 4),
('Operating System Concepts', 'Peter B. Galvin', 'Computer Science', '9781118063330', 'Wiley', 4, 3),
('Web Technologies: HTML, CSS, JavaScript, PHP', 'Achyut Godbole', 'Web Development', '9781259062940', 'Tata McGraw-Hill', 6, 6)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Sample circulation entry: STU-2026-001 borrowed Database System Concepts
INSERT INTO `issued_books` (`student_id`, `book_id`, `issue_date`, `due_date`, `return_date`, `status`) VALUES
(1, 1, '2026-10-01', '2026-10-15', NULL, 'issued');
