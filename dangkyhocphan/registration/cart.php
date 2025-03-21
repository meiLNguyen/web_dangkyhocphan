<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

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

// Handle remove from cart
if (isset($_POST['remove_course'])) {
    $courseId = sanitize($_POST['course_id']);
    $key = array_search($courseId, $_SESSION['cart']);
    
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        $_SESSION['success'] = "Đã xóa học phần khỏi giỏ đăng ký!";
    } else {
        $_SESSION['error'] = "Không tìm thấy học phần trong giỏ đăng ký!";
    }
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit();
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['success'] = "Đã xóa toàn bộ học phần khỏi giỏ đăng ký!";
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit();
}

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Giỏ đăng ký học phần</h2>
    <div>
        <a href="../courses/index.php" class="btn btn-secondary me-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Tiếp tục đăng ký
        </a>
        <?php if (!empty($_SESSION['cart'])): ?>
            <a href="checkout.php" class="btn btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                </svg>
                Lưu đăng ký
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (empty($_SESSION['cart'])): ?>
    <div class="alert alert-info">
        Giỏ đăng ký của bạn đang trống. <a href="../courses/index.php">Quay lại trang đăng ký học phần</a>
    </div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Thông tin đăng ký</h5>
        </div>
        <div class="card-body">
            <p><strong>Số học phần:</strong> <?= count($cartCourses) ?></p>
            <p><strong>Tổng số tín chỉ:</strong> <?= $totalCredits ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Mã HP</th>
                    <th>Tên học phần</th>
                    <th>Số tín chỉ</th>
                    <th>Chức năng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartCourses as $course): ?>
                    <tr>
                        <td><?= $course['MaHP'] ?></td>
                        <td><?= $course['TenHP'] ?></td>
                        <td><?= $course['SoTinChi'] ?></td>
                        <td>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="course_id" value="<?= $course['MaHP'] ?>">
                                <button type="submit" name="remove_course" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa học phần này khỏi giỏ đăng ký?');">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <form method="POST" action="" class="d-inline">
            <button type="submit" name="clear_cart" class="btn btn-warning" onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả học phần khỏi giỏ đăng ký?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
                Xóa đăng ký
            </button>
        </form>
        <a href="checkout.php" class="btn btn-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
            </svg>
            Lưu đăng ký
        </a>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>