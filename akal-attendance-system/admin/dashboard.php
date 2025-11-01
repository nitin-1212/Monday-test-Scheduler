<?php
/**
 * Admin Dashboard
 * Akal University Attendance Management System
 */

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check authentication and role
requireRole('admin');

$pageTitle = 'Admin Dashboard';

// Get statistics
$conn = getDBConnection();

// Total counts
$totalDepartments = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
$totalClasses = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND is_active = 1")->fetch_assoc()['count'];
$totalStaff = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff' AND is_active = 1")->fetch_assoc()['count'];

// Today's attendance summary
$today = date('Y-m-d');
$todayAttendance = $conn->query("
    SELECT 
        COUNT(DISTINCT student_id) as total_marked,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
    FROM attendance 
    WHERE attendance_date = '$today'
")->fetch_assoc();

closeDBConnection($conn);

include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-earmark-text"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="audit_logs.php">
                            <i class="bi bi-clock-history"></i> Audit Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="bi bi-people"></i> Manage Users
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-calendar"></i> <?php echo date('d M Y'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Departments</h6>
                                    <h2 class="mb-0"><?php echo $totalDepartments; ?></h2>
                                </div>
                                <i class="bi bi-building" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Classes</h6>
                                    <h2 class="mb-0"><?php echo $totalClasses; ?></h2>
                                </div>
                                <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Students</h6>
                                    <h2 class="mb-0"><?php echo $totalStudents; ?></h2>
                                </div>
                                <i class="bi bi-person-badge" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Staff</h6>
                                    <h2 class="mb-0"><?php echo $totalStaff; ?></h2>
                                </div>
                                <i class="bi bi-person-workspace" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Today's Attendance Summary -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Today's Attendance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h3 class="text-primary"><?php echo $todayAttendance['total_marked'] ?? 0; ?></h3>
                                    <p class="text-muted">Total Marked</p>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-success"><?php echo $todayAttendance['present_count'] ?? 0; ?></h3>
                                    <p class="text-muted">Present</p>
                                </div>
                                <div class="col-md-4">
                                    <h3 class="text-danger"><?php echo $todayAttendance['absent_count'] ?? 0; ?></h3>
                                    <p class="text-muted">Absent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="reports.php?report=class_wise" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-file-earmark-bar-graph"></i><br>
                                        Class-wise Reports
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="reports.php?report=subject_wise" class="btn btn-outline-success w-100">
                                        <i class="bi bi-book"></i><br>
                                        Subject-wise Reports
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="reports.php?report=defaulters" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-exclamation-triangle"></i><br>
                                        Defaulters Report
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="audit_logs.php" class="btn btn-outline-info w-100">
                                        <i class="bi bi-clock-history"></i><br>
                                        View Audit Logs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
