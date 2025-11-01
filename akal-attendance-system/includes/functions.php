<?php
/**
 * Common Functions
 * Akal University Attendance Management System
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get All Departments
 * 
 * @return array Array of departments
 */
function getAllDepartments() {
    $conn = getDBConnection();
    $sql = "SELECT * FROM departments ORDER BY department_name";
    $result = $conn->query($sql);
    
    $departments = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
    }
    
    closeDBConnection($conn);
    return $departments;
}

/**
 * Get Department by ID
 * 
 * @param int $departmentId Department ID
 * @return array|null Department data or null
 */
function getDepartmentById($departmentId) {
    $conn = getDBConnection();
    $sql = "SELECT * FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $department = null;
    if ($result->num_rows === 1) {
        $department = $result->fetch_assoc();
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return $department;
}

/**
 * Get Classes by Department
 * 
 * @param int $departmentId Department ID
 * @return array Array of classes
 */
function getClassesByDepartment($departmentId) {
    $conn = getDBConnection();
    $sql = "SELECT c.*, s.semester_name, d.department_name
            FROM classes c
            JOIN semesters s ON c.semester_id = s.semester_id
            JOIN departments d ON c.department_id = d.department_id
            WHERE c.department_id = ?
            ORDER BY c.year, c.section";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return $classes;
}

/**
 * Get Classes Assigned to Staff
 * 
 * @param int $staffId Staff user ID
 * @return array Array of classes
 */
function getClassesByStaff($staffId) {
    $conn = getDBConnection();
    $sql = "SELECT DISTINCT c.*, d.department_name, s.semester_name
            FROM classes c
            JOIN class_subjects cs ON c.class_id = cs.class_id
            JOIN departments d ON c.department_id = d.department_id
            JOIN semesters s ON c.semester_id = s.semester_id
            WHERE cs.staff_id = ?
            ORDER BY c.class_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return $classes;
}

/**
 * Get Subjects by Staff
 * 
 * @param int $staffId Staff user ID
 * @return array Array of subjects
 */
function getSubjectsByStaff($staffId) {
    $conn = getDBConnection();
    $sql = "SELECT DISTINCT s.*, cs.class_id, c.class_name
            FROM subjects s
            JOIN class_subjects cs ON s.subject_id = cs.subject_id
            JOIN classes c ON cs.class_id = c.class_id
            WHERE cs.staff_id = ?
            ORDER BY s.subject_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return $subjects;
}

/**
 * Get Students by Class
 * 
 * @param int $classId Class ID
 * @return array Array of students
 */
function getStudentsByClass($classId) {
    $conn = getDBConnection();
    $sql = "SELECT u.userid, u.name, u.email, sc.enrollment_date
            FROM users u
            JOIN student_classes sc ON u.userid = sc.student_id
            WHERE sc.class_id = ? AND sc.is_active = 1 AND u.is_active = 1
            ORDER BY u.name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    
    $stmt->close();
    closeDBConnection($conn);
    return $students;
}

/**
 * Check if Staff can Mark Attendance for Class and Subject
 * 
 * @param int $staffId Staff user ID
 * @param int $classId Class ID
 * @param int $subjectId Subject ID
 * @return bool True if authorized, false otherwise
 */
function canStaffMarkAttendance($staffId, $classId, $subjectId) {
    $conn = getDBConnection();
    $sql = "SELECT COUNT(*) as count
            FROM class_subjects
            WHERE staff_id = ? AND class_id = ? AND subject_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $staffId, $classId, $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    closeDBConnection($conn);
    
    return $row['count'] > 0;
}

/**
 * Log Attendance Audit
 * 
 * @param int $attendanceId Attendance record ID (null for insert)
 * @param int $studentId Student ID
 * @param int $classId Class ID
 * @param int $subjectId Subject ID
 * @param string $attendanceDate Attendance date
 * @param string $oldStatus Old status (null for insert)
 * @param string $newStatus New status
 * @param string $actionType Action type (insert, update, delete)
 * @param int $performedBy User ID who performed the action
 * @param string $remarks Remarks
 * @return bool True on success, false on failure
 */
function logAttendanceAudit($attendanceId, $studentId, $classId, $subjectId, $attendanceDate, 
                            $oldStatus, $newStatus, $actionType, $performedBy, $remarks = null) {
    $conn = getDBConnection();
    $sql = "INSERT INTO attendance_audit_log 
            (attendance_id, student_id, class_id, subject_id, attendance_date, 
             old_status, new_status, action_type, performed_by, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiissssss", $attendanceId, $studentId, $classId, $subjectId, 
                      $attendanceDate, $oldStatus, $newStatus, $actionType, $performedBy, $remarks);
    
    $success = $stmt->execute();
    
    $stmt->close();
    closeDBConnection($conn);
    
    return $success;
}

/**
 * Format Date for Display
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'd-m-Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate Attendance Percentage
 * 
 * @param int $present Number of present days
 * @param int $total Total number of days
 * @return float Percentage
 */
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($present / $total) * 100, 2);
}

/**
 * Get Attendance Status Badge Class
 * 
 * @param string $status Attendance status
 * @return string Bootstrap badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'present':
            return 'bg-success';
        case 'absent':
            return 'bg-danger';
        case 'late':
            return 'bg-warning';
        case 'excused':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

/**
 * Generate CSV from Array
 * 
 * @param array $data Data array
 * @param string $filename Filename
 */
function generateCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Output headers
        fputcsv($output, array_keys($data[0]));
        
        // Output data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

/**
 * Show Alert Message
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 * @return string HTML alert
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}
?>
