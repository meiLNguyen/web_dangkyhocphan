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

// Get student data with major name
$sql = "SELECT sv.*, nh.TenNganh FROM SinhVien sv 
        JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh 
        WHERE sv.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Không tìm thấy sinh viên";
    header("Location: index.php");
    exit();
}

$student = $result->fetch_assoc();

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Thông tin chi tiết sinh viên</h2>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <img src="<?= $student['Hinh'] ?>" alt="<?= $student['HoTen'] ?>" class="student-detail-img" style="max-width: 100px;>
            </div>
            <div class="col-md-9">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Mã sinh viên:</th>
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
                    <tr>
                        <th>Ngành học:</th>
                        <td><?= $student['TenNganh'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
            <a href="edit.php?id=<?= $student['MaSV'] ?>" class="btn btn-primary">Sửa</a>
            <a href="delete.php?id=<?= $student['MaSV'] ?>" class="btn btn-danger">Xóa</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>