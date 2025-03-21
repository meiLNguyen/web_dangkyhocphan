<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if already logged in
// if (isLoggedIn()) {
//     header("Location: index.php");
//     exit();
// }

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = sanitize($_POST['student_id']);
    $password = sanitize($_POST['password']);
    
    // In a real app, we would check password
    // For demo, we'll just check if the student exists
    $user = verifyLogin($conn, $studentId, $password);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['MaSV'];
        $_SESSION['user_name'] = $user['HoTen'];
        $_SESSION['success'] = "Đăng nhập thành công!";
        
        // Initialize cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Mã sinh viên hoặc mật khẩu không đúng";
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-header">
                <h4 class="text-center">Đăng nhập</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="student_id">Mã sinh viên</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">Sử dụng mã sinh viên là mật khẩu để đăng nhập (ví dụ: 0123456789)</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>