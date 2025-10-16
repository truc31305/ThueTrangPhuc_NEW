<?php
require_once __DIR__ . '/../inc/auth.php';

$errors = [];
$success = null;
$prefillEmail = isset($_COOKIE['last_registered_email']) ? $_COOKIE['last_registered_email'] : '';
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
	$success = 'Tạo tài khoản thành công! Vui lòng đăng nhập.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = isset($_POST['email']) ? trim($_POST['email']) : '';
	$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

	if ($email === '' || $password === '') {
		$errors[] = 'Vui lòng nhập email và mật khẩu';
	} else {
		list($ok, $msg) = attemptLogin($email, $password);
		if ($ok) {
			$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isAdmin() ? '?page=admin' : '?page=home');
			header('Location: ' . $redirect);
			exit;
		} else {
			$errors[] = $msg ?: 'Đăng nhập thất bại';
		}
	}
}
?>
<section class="auth-section login-page">
	<h1>Đăng nhập</h1>
    <?php if ($success) { ?>
        <div class="alert alert-success"><p><?php echo htmlspecialchars($success); ?></p></div>
    <?php } ?>
    <?php if (!empty($errors)) { ?>
		<div class="alert alert-error">
			<?php foreach ($errors as $e) { echo '<p>' . htmlspecialchars($e) . '</p>'; } ?>
		</div>
	<?php } ?>
    <form method="post" class="form auth-form card">
		<div class="form-row">
            <label for="email">Email/Số điện thoại</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" required>
		</div>
		<div class="form-row">
			<label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
            <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:13px;color:#374151">
                <input type="checkbox" data-toggle-password="#password"> Hiện mật khẩu
            </label>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn primary">Đăng nhập</button>
			<a class="btn link" href="?page=register">Chưa có tài khoản? Đăng ký</a>
		</div>
	</form>
</section>
