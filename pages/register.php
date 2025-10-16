<?php
require_once __DIR__ . '/../inc/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phoneOrEmail = isset($_POST['phoneOrEmail']) ? trim($_POST['phoneOrEmail']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $confirm = isset($_POST['confirm']) ? (string)$_POST['confirm'] : '';

    if ($name === '' || $phoneOrEmail === '' || $password === '' || $confirm === '') {
		$errors[] = 'Vui lòng điền đầy đủ thông tin';
	}
    $email = null; $phone = null;
    if ($phoneOrEmail !== '') {
        if (strpos($phoneOrEmail, '@') !== false) {
            if (!filter_var($phoneOrEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            } else { $email = $phoneOrEmail; }
        } else {
            $normalizedPhone = preg_replace('/\D+/', '', $phoneOrEmail);
            if (strlen($normalizedPhone) < 9) {
                $errors[] = 'Số điện thoại không hợp lệ';
            } else { $phone = $normalizedPhone; }
        }
    }
	if (strlen($password) < 6) {
		$errors[] = 'Mật khẩu tối thiểu 6 ký tự';
	}
	if ($password !== $confirm) {
		$errors[] = 'Mật khẩu xác nhận không khớp';
	}

    if (empty($errors)) {
        list($ok, $msg) = createUser($name, $email, $phone, $gender ?: null, $dob ?: null, $password);
        if ($ok) {
            // Lưu email để tự điền khi chuyển sang đăng nhập
            $identifier = $email ?: $phoneOrEmail;
            setcookie('last_registered_email', $identifier, time() + 86400 * 7, '/');
            header('Location: ?page=home&registered=1');
            exit;
        } else {
            $errors[] = $msg ?: 'Không thể tạo tài khoản';
        }
    }
}
?>
<section class="auth-section register-page">
    <h1>Đăng ký</h1>
	<?php if (!empty($errors)) { ?>
		<div class="alert alert-error">
			<?php foreach ($errors as $e) { echo '<p>' . htmlspecialchars($e) . '</p>'; } ?>
		</div>
	<?php } ?>
    <div class="register-hero">
        <img class="sticker-img sticker-left" src="assets/images/lotso1.JPG" alt="sticker gấu hồng" aria-hidden="true">
        <img class="sticker-img sticker-right" src="assets/images/lotso1.JPG" alt="sticker gấu hồng" aria-hidden="true">
        <form method="post" class="form auth-form card">
            <div class="form-grid">
            <div class="form-row">
                <label for="name">Tên tài khoản</label>
                <input type="text" id="name" name="name" placeholder="VD: Nguyễn Văn A" required>
            </div>
            <div class="form-row">
                <label for="phoneOrEmail">Số điện thoại/Email</label>
                <input type="text" id="phoneOrEmail" name="phoneOrEmail" placeholder="0987xxxxxx hoặc email@domain.com" required>
            </div>
            <div class="form-row">
                <label for="gender">Giới tính</label>
                <select id="gender" name="gender">
                    <option value="">-- Chọn --</option>
                    <option value="male">Nam</option>
                    <option value="female">Nữ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="form-row">
                <label for="dob">Ngày sinh</label>
                <input type="date" id="dob" name="dob">
            </div>
            <div class="form-row">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
                <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:13px;color:#374151">
                    <input type="checkbox" data-toggle-password="#password"> Hiện mật khẩu
                </label>
            </div>
            <div class="form-row">
                <label for="confirm">Nhập lại mật khẩu</label>
                <input type="password" id="confirm" name="confirm" required>
                <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:13px;color:#374151">
                    <input type="checkbox" data-toggle-password="#confirm"> Hiện mật khẩu
                </label>
            </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Tạo tài khoản</button>
                <a class="btn btn-link" href="?page=login">Đã có tài khoản? Đăng nhập</a>
            </div>
        </form>
    </div>
</section>
