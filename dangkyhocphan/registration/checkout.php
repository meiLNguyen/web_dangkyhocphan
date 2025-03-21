<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Giỏ đăng ký của bạn đang trống!";
    header("Location: cart.php");
    exit();
}

// Get current user
$studentId = $_SESSION['user_id']; // Assuming user_id stores the MaSV

// Get courses in cart
$cartCourses = [];
if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $conn->prepare("SELECT * FROM HocPhan WHERE MaHP IN ($placeholders)");
    
    // Bind all parameters
    $types = str_repeat('s', count($_SESSION['cart']));
    $stmt->bind_param($types, ...$_SESSION['cart']);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cartCourses[] = $row;
    }
}

// Calculate total credits
$totalCredits = 0;
foreach ($cartCourses as $course) {
    $totalCredits += $course['SoTinChi'];
}

// Process registration on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_registration'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create registration entry
        $stmt = $conn->prepare("INSERT INTO DangKy (NgayDK, MaSV) VALUES (NOW(), ?)");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        
        // Get the registration ID
        $registrationId = $conn->insert_id;
        
        // Add each course to the registration detail
        foreach ($_SESSION['cart'] as $courseId) {
            // Insert into ChiTietDangKy
            $stmt = $conn->prepare("INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)");
            $stmt->bind_param("is", $registrationId, $courseId);
            $stmt->execute();
            
            // Update course available slots (assuming HocPhan has SoLuongSV column)
            $stmt = $conn->prepare("UPDATE HocPhan SET SoLuongSV = SoLuongSV - 1 WHERE MaHP = ? AND SoLuongSV > 0");
            $stmt->bind_param("s", $courseId);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart after successful registration
        $_SESSION['cart'] = [];
        $_SESSION['success'] = "Đăng ký học phần thành công!";
        
        // Redirect to courses page
        header("Location: ../courses/index.php");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Lỗi khi đăng ký học phần: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Xác nhận đăng ký học phần</h2>
    <div>
        <a href="cart.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Quay lại giỏ đăng ký
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Thông tin sinh viên</h5>
    </div>
    <div class="card-body">
        <?php
        // Get student information
        $stmt = $conn->prepare("SELECT sv.*, ng.TenNganh FROM SinhVien sv 
                               LEFT JOIN NganhHoc ng ON sv.MaNganh = ng.MaNganh 
                               WHERE sv.MaSV = ?");
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        ?>
        
        <div class="row">
            <div class="col-md-2">
                <?php if (!empty($student['Hinh'])): ?>
                    <img src="<?= $student['Hinh'] ?>" alt="<?= $student['HoTen'] ?>" class="img-thumbnail">
                <?php else: ?>
                    <div class="text-center p-3 bg-light">No Image</div>
                <?php endif; ?>
            </div>
            <div class="col-md-10">
                <p><strong>Mã sinh viên:</strong> <?= $student['MaSV'] ?></p>
                <p><strong>Họ tên:</strong> <?= $student['HoTen'] ?></p>
                <p><strong>Ngành học:</strong> <?= $student['TenNganh'] ?></p>
                <p><strong>Ngày đăng ký:</strong> <?= date('d/m/Y') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Thông tin đăng ký</h5>
    </div>
    <div class="card-body">
        <p><strong>Số học phần:</strong> <?= count($cartCourses) ?></p>
        <p><strong>Tổng số tín chỉ:</strong> <?= $totalCredits ?></p>
    </div>
</div>

<div class="table-responsive mb-4">
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Mã HP</th>
                <th>Tên học phần</th>
                <th>Số tín chỉ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartCourses as $course): ?>
                <tr>
                    <td><?= $course['MaHP'] ?></td>
                    <td><?= $course['TenHP'] ?></td>
                    <td><?= $course['SoTinChi'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<form method="POST" action="">
    <div class="alert alert-warning">
        <p><strong>Lưu ý:</strong> Sau khi xác nhận đăng ký, bạn sẽ không thể thay đổi thông tin đăng ký học phần này.</p>
    </div>
    
    <div class="d-flex justify-content-between">
        <a href="cart.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Quay lại giỏ đăng ký
        </a>
        <button type="submit" name="confirm_registration" class="btn btn-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
            </svg>
            Xác nhận đăng ký
        </button>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>