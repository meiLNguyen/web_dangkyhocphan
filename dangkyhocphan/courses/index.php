<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireLogin();

// Get all courses
$courses = getAllCourses($conn);

// Handle add to cart action
if (isset($_POST['add_to_cart'])) {
    $courseId = sanitize($_POST['course_id']);
    
    // Check if course exists
    $courseExists = false;
    foreach ($courses as $course) {
        if ($course['MaHP'] == $courseId) {
            $courseExists = true;
            break;
        }
    }
    
    if ($courseExists) {
        // Check if course is already in cart
        if (!in_array($courseId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $courseId;
            $_SESSION['success'] = "Đã thêm học phần vào giỏ đăng ký!";
        } else {
            $_SESSION['error'] = "Học phần đã có trong giỏ đăng ký!";
        }
    } else {
        $_SESSION['error'] = "Không tìm thấy học phần!";
    }
    
    // Redirect to avoid form resubmission
    header("Location: index.php");
    exit();
}

require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Đăng ký học phần</h2>
    <div>
        <a href="../registration/cart.php" class="btn btn-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            Xem giỏ đăng ký (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Mã HP</th>
                <th>Tên học phần</th>
                <th>Số tín chỉ</th>
                <th>Số lượng SV có thể đăng ký</th>
                <th>Chức năng</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= $course['MaHP'] ?></td>
                    <td><?= $course['TenHP'] ?></td>
                    <td><?= $course['SoTinChi'] ?></td>
                    <td><?= $course['SoLuongSV'] ?></td>
                    <td>
                        <?php if ($course['SoLuongSV'] > 0): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="course_id" value="<?= $course['MaHP'] ?>">
                                <?php if (isset($_SESSION['cart']) && in_array($course['MaHP'], $_SESSION['cart'])): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>Đã thêm vào giỏ</button>
                                <?php else: ?>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">Đăng ký</button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn btn-danger btn-sm" disabled>Hết chỗ</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>