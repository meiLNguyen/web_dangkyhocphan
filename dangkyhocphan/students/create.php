<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get all majors for dropdown
$majors = getAllMajors($conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = sanitize($_POST['student_id']);
    $fullName = sanitize($_POST['full_name']);
    $gender = sanitize($_POST['gender']);
    $birthDate = sanitize($_POST['birth_date']);
    $majorId = sanitize($_POST['major_id']);
    
    // File upload handling
    $targetDir = "../Content/images/";
    $targetFile = "";
    $uploadOk = 1;
    
    if(isset($_FILES["image"]) && $_FILES["image"]["name"] != "") {
        $fileName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $_SESSION['error'] = "File không phải là hình ảnh.";
            $uploadOk = 0;
        }
        
        // Check file size (limit to 5MB)
        if ($_FILES["image"]["size"] > 5000000) {
            $_SESSION['error'] = "File quá lớn, vui lòng chọn file nhỏ hơn 5MB.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $_SESSION['error'] = "Chỉ chấp nhận file JPG, JPEG, PNG & GIF.";
            $uploadOk = 0;
        }
        
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $_SESSION['error'] = "File của bạn không được tải lên.";
        } else {
            // if everything is ok, try to upload file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // $targetFile = str_replace("../", "/", $targetFile);
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi tải file lên.";
                $uploadOk = 0;
            }
        }
    } else {
        // No image uploaded, use default
        $targetFile = "../Content/images/default.jpg";
    }
    
    // If upload is ok, insert into database
    if ($uploadOk) {
        $sql = "INSERT INTO SinhVien (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $studentId, $fullName, $gender, $birthDate, $targetFile, $majorId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Thêm sinh viên thành công!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Thêm sinh viên thất bại: " . $conn->error;
        }
    }
}

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Thêm sinh viên mới</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="student_id">Mã sinh viên</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">Họ tên</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label>Giới tính</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Nam" checked>
                    <label class="form-check-label" for="gender_male">Nam</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Nữ">
                    <label class="form-check-label" for="gender_female">Nữ</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="birth_date">Ngày sinh</label>
                <input type="date" class="form-control" id="birth_date" name="birth_date" required>
            </div>
            
            <div class="form-group">
                <label for="major_id">Ngành học</label>
                <select class="form-control" id="major_id" name="major_id" required>
                    <option value="">-- Chọn ngành học --</option>
                    <?php foreach ($majors as $major): ?>
                        <option value="<?= $major['MaNganh'] ?>"><?= $major['TenNganh'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Hình ảnh</label>
                <input type="file" class="form-control-file" id="image" name="image">
                <small class="form-text text-muted">Chọn file hình ảnh JPG, PNG, JPEG hoặc GIF.</small>
            </div>
            
            <div class="form-group">
                <a href="index.php" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>