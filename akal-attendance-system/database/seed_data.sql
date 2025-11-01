-- ============================================
-- Akal University Attendance Management System
-- Sample Seed Data
-- ============================================

USE akal_attendance_db;

-- ============================================
-- Insert Departments
-- ============================================
INSERT INTO departments (department_name, department_code) VALUES
('Computer Science & Engineering', 'CSE'),
('Electronics & Communication Engineering', 'ECE'),
('Mechanical Engineering', 'ME'),
('Civil Engineering', 'CE'),
('Business Administration', 'MBA');

-- ============================================
-- Insert Semesters
-- ============================================
INSERT INTO semesters (semester_name, semester_code, start_date, end_date, is_active) VALUES
('Semester 1 - 2024-25', 'SEM1-2024', '2024-08-01', '2024-12-31', 1),
('Semester 2 - 2024-25', 'SEM2-2024', '2025-01-01', '2025-05-31', 1),
('Semester 3 - 2024-25', 'SEM3-2024', '2024-08-01', '2024-12-31', 1),
('Semester 4 - 2024-25', 'SEM4-2024', '2025-01-01', '2025-05-31', 1);

-- ============================================
-- Insert Users
-- Password for all users: password123
-- Hash generated using: password_hash('password123', PASSWORD_DEFAULT)
-- ============================================

-- Admin Users
INSERT INTO users (name, email, password, role, department_id) VALUES
('Admin User', 'admin@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);

-- HOD Users (one per department)
INSERT INTO users (name, email, password, role, department_id) VALUES
('Dr. Rajesh Kumar', 'hod.cse@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hod', 1),
('Dr. Priya Sharma', 'hod.ece@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hod', 2),
('Dr. Amit Singh', 'hod.me@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hod', 3);

-- Staff Users
INSERT INTO users (name, email, password, role, department_id) VALUES
('Prof. Suresh Verma', 'suresh.verma@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('Prof. Neha Gupta', 'neha.gupta@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 1),
('Prof. Vikram Patel', 'vikram.patel@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 2),
('Prof. Anjali Reddy', 'anjali.reddy@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 2),
('Prof. Rahul Mehta', 'rahul.mehta@akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 3);

-- Student Users
INSERT INTO users (name, email, password, role, department_id) VALUES
('Arjun Sharma', 'arjun.sharma@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Priya Singh', 'priya.singh@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Rohit Kumar', 'rohit.kumar@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Sneha Patel', 'sneha.patel@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Karan Verma', 'karan.verma@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1),
('Ananya Reddy', 'ananya.reddy@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2),
('Vikash Gupta', 'vikash.gupta@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2),
('Divya Sharma', 'divya.sharma@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2),
('Rahul Singh', 'rahul.singh@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3),
('Pooja Mehta', 'pooja.mehta@student.akaluniversity.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3);

-- ============================================
-- Insert Classes
-- ============================================
INSERT INTO classes (class_name, class_code, department_id, semester_id, year, section) VALUES
('CSE 2nd Year Section A', 'CSE-2A', 1, 3, 2, 'A'),
('CSE 2nd Year Section B', 'CSE-2B', 1, 3, 2, 'B'),
('ECE 2nd Year Section A', 'ECE-2A', 2, 3, 2, 'A'),
('ME 2nd Year Section A', 'ME-2A', 3, 3, 2, 'A');

-- ============================================
-- Insert Subjects
-- ============================================
INSERT INTO subjects (subject_name, subject_code, department_id, semester_id, credits) VALUES
('Data Structures', 'CS201', 1, 3, 4),
('Database Management Systems', 'CS202', 1, 3, 4),
('Operating Systems', 'CS203', 1, 3, 4),
('Computer Networks', 'CS204', 1, 3, 3),
('Digital Signal Processing', 'EC201', 2, 3, 4),
('Microprocessors', 'EC202', 2, 3, 4),
('Thermodynamics', 'ME201', 3, 3, 4),
('Fluid Mechanics', 'ME202', 3, 3, 4);

-- ============================================
-- Insert Class-Subject Mappings with Staff
-- ============================================
INSERT INTO class_subjects (class_id, subject_id, staff_id) VALUES
-- CSE 2nd Year Section A
(1, 1, 5),  -- Data Structures - Prof. Suresh Verma
(1, 2, 6),  -- DBMS - Prof. Neha Gupta
(1, 3, 5),  -- OS - Prof. Suresh Verma
(1, 4, 6),  -- Computer Networks - Prof. Neha Gupta
-- CSE 2nd Year Section B
(2, 1, 5),  -- Data Structures - Prof. Suresh Verma
(2, 2, 6),  -- DBMS - Prof. Neha Gupta
-- ECE 2nd Year Section A
(3, 5, 7),  -- DSP - Prof. Vikram Patel
(3, 6, 8),  -- Microprocessors - Prof. Anjali Reddy
-- ME 2nd Year Section A
(4, 7, 9),  -- Thermodynamics - Prof. Rahul Mehta
(4, 8, 9);  -- Fluid Mechanics - Prof. Rahul Mehta

-- ============================================
-- Insert Student-Class Enrollments
-- ============================================
INSERT INTO student_classes (student_id, class_id, enrollment_date) VALUES
-- CSE Students in Section A
(10, 1, '2024-08-01'),
(11, 1, '2024-08-01'),
(12, 1, '2024-08-01'),
(13, 1, '2024-08-01'),
(14, 1, '2024-08-01'),
-- ECE Students in Section A
(15, 3, '2024-08-01'),
(16, 3, '2024-08-01'),
(17, 3, '2024-08-01'),
-- ME Students in Section A
(18, 4, '2024-08-01'),
(19, 4, '2024-08-01');

-- ============================================
-- Insert Sample Attendance Records
-- ============================================
INSERT INTO attendance (student_id, class_id, subject_id, attendance_date, status, marked_by, remarks) VALUES
-- Data Structures attendance for CSE Section A (Oct 2024)
(10, 1, 1, '2024-10-01', 'present', 5, NULL),
(11, 1, 1, '2024-10-01', 'present', 5, NULL),
(12, 1, 1, '2024-10-01', 'absent', 5, NULL),
(13, 1, 1, '2024-10-01', 'present', 5, NULL),
(14, 1, 1, '2024-10-01', 'late', 5, 'Arrived 15 mins late'),

(10, 1, 1, '2024-10-02', 'present', 5, NULL),
(11, 1, 1, '2024-10-02', 'absent', 5, NULL),
(12, 1, 1, '2024-10-02', 'present', 5, NULL),
(13, 1, 1, '2024-10-02', 'present', 5, NULL),
(14, 1, 1, '2024-10-02', 'present', 5, NULL),

-- DBMS attendance for CSE Section A
(10, 1, 2, '2024-10-01', 'present', 6, NULL),
(11, 1, 2, '2024-10-01', 'present', 6, NULL),
(12, 1, 2, '2024-10-01', 'present', 6, NULL),
(13, 1, 2, '2024-10-01', 'absent', 6, NULL),
(14, 1, 2, '2024-10-01', 'present', 6, NULL),

-- DSP attendance for ECE Section A
(15, 3, 5, '2024-10-01', 'present', 7, NULL),
(16, 3, 5, '2024-10-01', 'present', 7, NULL),
(17, 3, 5, '2024-10-01', 'absent', 7, NULL),

(15, 3, 5, '2024-10-02', 'present', 7, NULL),
(16, 3, 5, '2024-10-02', 'absent', 7, NULL),
(17, 3, 5, '2024-10-02', 'present', 7, NULL),

-- Thermodynamics attendance for ME Section A
(18, 4, 7, '2024-10-01', 'present', 9, NULL),
(19, 4, 7, '2024-10-01', 'present', 9, NULL),

(18, 4, 7, '2024-10-02', 'absent', 9, NULL),
(19, 4, 7, '2024-10-02', 'present', 9, NULL);

-- ============================================
-- Insert Sample Audit Log Entries
-- ============================================
INSERT INTO attendance_audit_log (attendance_id, student_id, class_id, subject_id, attendance_date, old_status, new_status, action_type, performed_by, remarks) VALUES
(1, 10, 1, 1, '2024-10-01', NULL, 'present', 'insert', 5, 'Initial attendance marking'),
(2, 11, 1, 1, '2024-10-01', NULL, 'present', 'insert', 5, 'Initial attendance marking'),
(3, 12, 1, 1, '2024-10-01', NULL, 'absent', 'insert', 5, 'Initial attendance marking');
