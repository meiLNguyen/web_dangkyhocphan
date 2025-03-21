<?php
require_once '../includes/config.php';

// Get all students
$sql = "SELECT sv.*, nh.TenNganh FROM SinhVien sv 
        JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh";
$result = $conn->query($sql);

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Danh sách sinh viên</h2>
    <a href="create.php" class="btn btn-success">Thêm sinh viên mới</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Mã SV</th>
                <th>Họ tên</th>
                <th>Hình</th>
                <th>Giới tính</th>
                <th>Ngày sinh</th>
                <th>Ngành học</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaSV'] ?></td>
                        <td><?= $row['HoTen'] ?></td>
                        <td>
                            <img src="<?= $row['Hinh'] ?>" alt="<?= $row['HoTen'] ?>" class="student-img" style="max-width: 100px;">
                        </td>
                        <td><?= $row['GioiTinh'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['NgaySinh'])) ?></td>
                        <td><?= $row['TenNganh'] ?></td>
                        <td>
                            <a href="detail.php?id=<?= $row['MaSV'] ?>" class="btn btn-info btn-sm">Chi tiết</a>
                            <a href="edit.php?id=<?= $row['MaSV'] ?>" class="btn btn-primary btn-sm">Sửa</a>
                            <a href="delete.php?id=<?= $row['MaSV'] ?>" class="btn btn-danger btn-sm">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Không có sinh viên nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>