<?php
$appName = 'SAPAQT';
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
require_once __DIR__ . '/auth.php';
$user = currentUser();
?>
<!doctype html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $appName; ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
	<header class="site-header">
		<div class="container header-inner">
			<div class="brand">
				<a class="logo" href="?page=home">
					<span class="logo-mark">S</span>
					<span class="logo-text"><?php echo $appName; ?></span>
				</a>
				<span class="slogan">Trang phục cho mọi khoảnh khắc</span>
			</div>
			<button class="hamburger" aria-label="Mở menu" aria-controls="nav" aria-expanded="false" data-menu-toggle>
				<span></span><span></span><span></span>
			</button>
			<nav id="nav" class="main-nav" aria-label="Chính">
				<a class="nav-link <?php echo $currentPage==='home'?'active':''; ?>" href="?page=home">Trang chủ</a>
				<a class="nav-link <?php echo $currentPage==='catalog'?'active':''; ?>" href="?page=catalog">Danh mục</a>
				<a class="nav-link <?php echo $currentPage==='about'?'active':''; ?>" href="?page=about">Liên hệ</a>
				<a class="nav-link <?php echo $currentPage==='policy'?'active':''; ?>" href="?page=policy">Chính sách</a>
				<a class="nav-link cart-link <?php echo $currentPage==='cart'?'active':''; ?>" href="?page=cart" aria-label="Giỏ hàng">🛒</a>
				<span class="nav-spacer"></span>
				<?php if (!empty($user) && !empty($user['isAdmin'])) { ?>
					<a class="nav-link <?php echo $currentPage==='admin'?'active':''; ?>" href="?page=admin">Admin</a>
				<?php } ?>
				<?php if ($user) { ?>
					<span class="nav-user">Xin chào, <?php echo htmlspecialchars($user['name']); ?></span>
					<a class="nav-link" href="?page=logout&redirect=<?php echo urlencode('?page=home'); ?>">Đăng xuất</a>
				<?php } else { ?>
					<div class="auth-menu">
						<a class="nav-link" href="#" data-auth-toggle>Đăng nhập/Đăng ký</a>
						<div class="auth-dropdown" id="authDropdown" aria-hidden="true">
							<form method="post" action="?page=login" class="form auth-form small">
								<div class="form-row">
									<label for="dd-email">Email</label>
									<input type="email" id="dd-email" name="email" required>
								</div>
								<div class="form-row">
									<label for="dd-password">Mật khẩu</label>
									<input type="password" id="dd-password" name="password" required>
								</div>
								<div class="form-actions">
									<button type="submit" class="btn btn-primary">Đăng nhập</button>
								</div>
							</form>
							<div class="auth-note">Chưa có tài khoản? <a href="?page=register">Tạo tài khoản</a></div>
						</div>
					</div>
				<?php } ?>
			</nav>
		</div>
	</header>

	<main class="site-main container">

