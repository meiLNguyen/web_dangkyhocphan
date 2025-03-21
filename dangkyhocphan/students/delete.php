<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Không tìm thấy ID sinh viên";
    header("Location: index.php");
    exit();
}

$studentId = sanitize($_GET['id']);

// Get student data
$student = getStudentById($conn, $studentId);

if (!$student) {
    $_SESSION['error'] = "Không tìm thấy sinh viên";
    header("Location: index.php");
    exit();
}

// Process deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if student has any registrations
    $sql = "SELECT * FROM DangKy WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Không thể xóa sinh viên vì đã có đăng ký học phần.";
    } else {
        // Delete student
        $sql = "DELETE FROM SinhVien WHERE MaSV = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $studentId);
        
        if ($stmt->execute()) {
            // Delete image file if it's not the default
            if ($student['Hinh'] != "/Content/images/default.jpg" && file_exists(".." . $student['Hinh'])) {
                unlink(".." . $student['Hinh']);
            }
            
            $_SESSION['success'] = "Xóa sinh viên thành công!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Xóa sinh viên thất bại: " . $conn->error;
        }
    }
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Xóa sinh viên</h2>
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <p>Bạn có chắc chắn muốn xóa sinh viên này không?</p>
        </div>
        
        <div class="row">
            <div class="col-md-2">
                <img src="<?= $student['Hinh'] ?>" alt="<?= $student['HoTen'] ?>" class="img-fluid mb-3">
            </div>
            <div class="col-md-10">
                <table class="table">
                    <tr>
                        <th>Mã sinh viên:</th>
                        <td><?= $student['MaSV'] ?></td>
                    </tr>
                    <tr>
                        <th>Họ tên:</th>
                        <td><?= $student['HoTen'] ?></td>
                    </tr>
                    <tr>
                        <th>Giới tính:</th>
                        <td><?= $student['GioiTinh'] ?></td>
                    </tr>
                    <tr>
                        <th>Ngày sinh:</th>
                        <td><?= date('d/m/Y', strtotime($student['NgaySinh'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <a href="index.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-danger">Xóa</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>