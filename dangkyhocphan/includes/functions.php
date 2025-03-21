<?php
// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Bạn cần đăng nhập để truy cập trang này";
        header("Location: /student-registration/login.php");
        exit();
    }
}

// Function to get student by ID
function getStudentById($conn, $id) {
    $id = sanitize($id);
    $sql = "SELECT * FROM SinhVien WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get all majors
function getAllMajors($conn) {
    $sql = "SELECT * FROM NganhHoc";
    $result = $conn->query($sql);
    $majors = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $majors[] = $row;
        }
    }
    return $majors;
}

// Function to get all courses
function getAllCourses($conn) {
    $sql = "SELECT * FROM HocPhan";
    $result = $conn->query($sql);
    $courses = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    return $courses;
}

// Function to save registration
function saveRegistration($conn, $studentId, $courses) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert into DangKy table
        $date = date("Y-m-d");
        $sql = "INSERT INTO DangKy (NgayDK, MaSV) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $date, $studentId);
        $stmt->execute();
        
        // Get the last inserted ID
        $registrationId = $conn->insert_id;
        
        // Insert into ChiTietDangKy table
        $sql = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($courses as $courseId) {
            $stmt->bind_param("is", $registrationId, $courseId);
            $stmt->execute();
            
            // Update course count (for task 6)
            $updateSql = "UPDATE HocPhan SET SoLuongSV = SoLuongSV - 1 WHERE MaHP = ? AND SoLuongSV > 0";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $courseId);
            $updateStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return false;
    }
}

// Function to get registered courses for a student
function getRegisteredCourses($conn, $studentId) {
    $sql = "SELECT hp.* FROM HocPhan hp
            JOIN ChiTietDangKy ctdk ON hp.MaHP = ctdk.MaHP
            JOIN DangKy dk ON ctdk.MaDK = dk.MaDK
            WHERE dk.MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    return $courses;
}

// Function to verify login
function verifyLogin($conn, $studentId, $password) {
    // In a real application, you would verify against hashed passwords
    // For this example, we'll just check if student ID exists
    $sql = "SELECT * FROM SinhVien WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // In a real app, check password here
        return $user;
    }
    return null;
}
?>