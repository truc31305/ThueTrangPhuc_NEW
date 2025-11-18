<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/storage.php';

if (!isAdmin()) {
	header('Location: ?page=login');
	exit;
}

$tab = $_GET['tab'] ?? 'dashboard';

?>
<section class="section">
	<h1>Admin Dashboard</h1>
	<nav class="tabs" style="margin-bottom:12px">
		<a class="btn <?php echo $tab==='dashboard'?'btn-primary':''; ?>" href="?page=admin&tab=dashboard">Tổng quan</a>
		<a class="btn <?php echo $tab==='promotions'?'btn-primary':''; ?>" href="?page=admin&tab=promotions">Chủ đề, Khuyến mãi</a>
		<a class="btn <?php echo $tab==='products'?'btn-primary':''; ?>" href="?page=admin&tab=products">Sản phẩm</a>
		<a class="btn <?php echo $tab==='orders'?'btn-primary':''; ?>" href="?page=admin&tab=orders">Đơn hàng</a>
		<a class="btn <?php echo $tab==='users'?'btn-primary':''; ?>" href="?page=admin&tab=users">Người dùng</a>
		<a class="btn <?php echo $tab==='feedback'?'btn-primary':''; ?>" href="?page=admin&tab=feedback">Feedback</a>
	</nav>

	<?php if ($tab === 'dashboard'): ?>
		<?php $orders = readJsonFile(__DIR__ . '/../data/orders.json'); $feedback = readJsonFile(__DIR__ . '/../data/feedback.json'); $users = loadUsers(); $products = readJsonFile(__DIR__ . '/../data/products.json'); ?>
		<div class="grid" style="grid-template-columns:repeat(5,1fr);gap:12px">
			<div class="card"><div class="card-body"><strong>Sản phẩm</strong><div style="font-size:24px"><?php echo count($products); ?></div></div></div>
			<div class="card"><div class="card-body"><strong>Đơn hàng</strong><div style="font-size:24px"><?php echo count($orders); ?></div></div></div>
			<div class="card"><div class="card-body"><strong>Người dùng</strong><div style="font-size:24px"><?php echo count($users); ?></div></div></div>
			<div class="card"><div class="card-body"><strong>Feedback</strong><div style="font-size:24px"><?php echo count($feedback); ?></div></div></div>
            <?php $totalItemsOrdered = 0; foreach ($orders as $o) { foreach (($o['items'] ?? []) as $it) { $totalItemsOrdered += max(1,(int)($it['qty'] ?? 1)); } } ?>
            <div class="card"><div class="card-body"><strong>Sản phẩm đã đặt</strong><div style="font-size:24px"><?php echo (int)$totalItemsOrdered; ?></div></div></div>
		</div>

		<?php
		// Prepare 14-day orders chart
		$days = [];
		for ($i=13;$i>=0;$i--) { $k = date('Y-m-d', strtotime("-{$i} days")); $days[$k] = 0; }
		foreach ($orders as $o) {
			$day = substr($o['createdAt'] ?? '', 0, 10);
			if (isset($days[$day])) { $days[$day] += (int)($o['total'] ?? 0); }
		}
		$maxVal = max($days ?: [0]); if ($maxVal <= 0) { $maxVal = 1; }
		// Prepare totals by category (bao gồm phụ kiện và số lượng)
		$pidToCat = [];
		foreach ($products as $p) { $pidToCat[(int)$p['id']] = $p['category'] ?? 'khac'; }
		$catTotals = [];
		foreach ($orders as $o) {
			foreach (($o['items'] ?? []) as $it) {
				$cat = $pidToCat[(int)($it['id'] ?? 0)] ?? 'khac';
				$qty = max(1,(int)($it['qty'] ?? 1));
				$days = max(1,(int)($it['days'] ?? 1));
				$base = ((int)($it['pricePerDay'] ?? 0)) * $days * $qty;
				$acc = 0; foreach (($it['accessories'] ?? []) as $ac) { $acc += (int)($ac['pricePerDay'] ?? 0) * $days * $qty; }
				$catTotals[$cat] = ($catTotals[$cat] ?? 0) + $base + $acc;
			}
		}
		arsort($catTotals);
		$catMax = max($catTotals ?: [0]); if ($catMax <= 0) { $catMax = 1; }
		?>
		<div class="grid" style="grid-template-columns:2fr 1fr;gap:12px;margin-top:12px">
			<div class="card"><div class="card-body">
				<h3>Doanh thu 14 ngày gần nhất</h3>
				<div class="chart chart-bars">
					<?php foreach ($days as $d => $val): $h = (int)round(($val/$maxVal)*120); ?>
						<div class="bar" title="<?php echo htmlspecialchars($d.' — '.number_format($val,0,',','.').'₫'); ?>">
							<div class="fill" style="height:<?php echo $h; ?>px"></div>
							<div class="label"><?php echo htmlspecialchars(substr($d,5)); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div></div>
			<div class="card"><div class="card-body">
				<h3>Doanh thu theo danh mục</h3>
				<div class="chart chart-bars horizontal">
					<?php foreach ($catTotals as $cat => $val): $w = (int)round(($val/$catMax)*220); ?>
						<div class="hbar" title="<?php echo htmlspecialchars($cat.' — '.number_format($val,0,',','.').'₫'); ?>">
							<div class="hlabel"><?php echo htmlspecialchars($cat); ?></div>
							<div class="hfill" style="width:<?php echo $w; ?>px"></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div></div>
		</div>
	<?php elseif ($tab === 'categories'): ?>

	<?php elseif ($tab === 'promotions'): ?>
		<?php
		$ppath = __DIR__ . '/../data/promotions.json';
		$promos = readJsonFile($ppath);
		$editPromo = isset($_GET['pid']) ? $_GET['pid'] : null;
		$editingPromo = null; if ($editPromo) { foreach ($promos as $pr) { if (($pr['id']??'') === $editPromo) { $editingPromo = $pr; break; } } }
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$action = $_POST['action'] ?? '';
			if ($action === 'create' || $action === 'update') {
				$id = $action==='create' ? uniqid('pr_', true) : trim($_POST['id']);
				$uploaded = '';
				if (!empty($_FILES['banner']) && ($_FILES['banner']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
					$uploadDir = __DIR__ . '/../assets/uploads'; if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);
					$name = $_FILES['banner']['name']; $tmp = $_FILES['banner']['tmp_name']; $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
					if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
						$dest = $uploadDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/','-', pathinfo($name, PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $ext;
						if (move_uploaded_file($tmp, $dest)) { $uploaded = 'assets/uploads/' . basename($dest); }
					}
				}
				// Validate required date range
				$vf = trim($_POST['validFrom'] ?? '');
				$vt = trim($_POST['validTo'] ?? '');
				$promoError = null;
				if ($vf === '' || $vt === '') { $promoError = 'Vui lòng nhập đầy đủ thời gian hiệu lực (Từ ngày - Đến ngày).'; }
				$record = [
					'id' => $id,
					'title' => trim($_POST['title'] ?? ''),
					'description' => trim($_POST['description'] ?? ''),
					'category' => trim($_POST['category'] ?? ''),
					'percent' => (int)($_POST['percent'] ?? 0),
					'fixed' => (int)($_POST['fixed'] ?? 0),
					'validFrom' => $vf,
					'validTo' => $vt,
					'banner' => $uploaded ?: trim($_POST['bannerUrl'] ?? ''),
					'active' => isset($_POST['active']) ? true : false
				];
				if ($promoError === null) {
					$found = false;
					foreach ($promos as &$p) { if (($p['id']??'') === $id) { $p = $record; $found = true; break; } }
					if (!$found) { $promos[] = $record; }
					writeJsonFile($ppath, $promos);
					header('Location: ?page=admin&tab=promotions'); exit;
				}
			}
			if ($action === 'delete') {
				$id = trim($_POST['id'] ?? '');
				$promos = array_values(array_filter($promos, function($pp) use ($id){ return ($pp['id']??'') !== $id; }));
				writeJsonFile($ppath, $promos);
				header('Location: ?page=admin&tab=promotions'); exit;
			}
		}
		?>
		<div class="card"><div class="card-body">
			<h3><?php echo $editingPromo ? 'Sửa khuyến mãi' : 'Thêm khuyến mãi'; ?></h3>
			<?php if (!empty($promoError)): ?><div class="alert alert-error"><?php echo htmlspecialchars($promoError); ?></div><?php endif; ?>
			<form method="post" class="form" enctype="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo $editingPromo ? 'update' : 'create'; ?>">
				<?php if ($editingPromo): ?><input type="hidden" name="id" value="<?php echo htmlspecialchars($editingPromo['id']); ?>"><?php endif; ?>
				<div class="grid" style="grid-template-columns:repeat(2,1fr);gap:12px">
					<label>Tiêu đề<input name="title" required value="<?php echo htmlspecialchars($editingPromo['title'] ?? ''); ?>" placeholder="Siêu sale 11/11"></label>
					<label>Danh mục áp dụng<input name="category" value="<?php echo htmlspecialchars($editingPromo['category'] ?? ''); ?>" placeholder="vd: truyenthong (để trống = áp dụng tất cả)"></label>
					<label>Giảm %<input type="number" name="percent" min="0" max="100" value="<?php echo htmlspecialchars((string)($editingPromo['percent'] ?? '0')); ?>"></label>
					<label>Giảm cố định (₫)<input type="number" name="fixed" min="0" value="<?php echo htmlspecialchars((string)($editingPromo['fixed'] ?? '0')); ?>"></label>
					<label>Từ ngày<input type="date" name="validFrom" value="<?php echo htmlspecialchars($editingPromo['validFrom'] ?? ''); ?>"></label>
					<label>Đến ngày<input type="date" name="validTo" value="<?php echo htmlspecialchars($editingPromo['validTo'] ?? ''); ?>"></label>
					<label class="full">Mô tả<textarea name="description" rows="3" placeholder="SALE 30% khi mua 3 bộ cùng chủ đề..."><?php echo htmlspecialchars($editingPromo['description'] ?? ''); ?></textarea></label>
					<label>Banner (URL)<input name="bannerUrl" placeholder="https://..." value="<?php echo htmlspecialchars($editingPromo['banner'] ?? ''); ?>"></label>
					<label>Tải banner<input type="file" name="banner" accept="image/*"></label>
					<label><input type="checkbox" name="active" value="1" <?php echo !empty($editingPromo['active'])?'checked':''; ?>> Kích hoạt</label>
				</div>
				<div class="form-actions"><button class="btn btn-primary" type="submit">Lưu</button></div>
			</form>
		</div></div>
		<h3 style="margin-top:16px">Danh sách khuyến mãi</h3>
		<table class="table">
			<thead><tr><th>Tiêu đề</th><th>Danh mục</th><th>Hiệu lực</th><th>Trạng thái</th><th></th></tr></thead>
			<tbody>
				<?php foreach ($promos as $pr): ?>
				<tr>
					<td><?php echo htmlspecialchars($pr['title'] ?? ''); ?></td>
					<td><?php echo htmlspecialchars($pr['category'] ?: 'Tất cả'); ?></td>
					<td><?php echo htmlspecialchars(($pr['validFrom'] ?? '').' → '.($pr['validTo'] ?? '')); ?></td>
					<td><?php echo !empty($pr['active']) ? 'Đang bật' : 'Tắt'; ?></td>
					<td>
						<a class="btn" href="?page=admin&tab=promotions&pid=<?php echo urlencode($pr['id']); ?>">Sửa</a>
						<form method="post" style="display:inline" onsubmit="return confirm('Xoá khuyến mãi?')">
							<input type="hidden" name="action" value="delete">
							<input type="hidden" name="id" value="<?php echo htmlspecialchars($pr['id']); ?>">
							<button class="btn">Xoá</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
        <?php
        $cpath = __DIR__ . '/../data/categories.json';
        $cats = readJsonFile($cpath);
        $editCatId = isset($_GET['edit_cat']) ? trim($_GET['edit_cat']) : null;
        $editingCat = null; if ($editCatId) { foreach ($cats as $cc) { if (($cc['id']??'')===$editCatId) { $editingCat = $cc; break; } } }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create' || $action === 'update') {
                $id = trim($_POST['id'] ?? '');
                if ($id === '') { $id = strtolower(preg_replace('/\s+/', '', $_POST['name'] ?? '')); }
                // handle uploads
                $uploadedUrls = [];
                if (!empty($_FILES['cat_upload_images']) && is_array($_FILES['cat_upload_images']['name'])) {
                    $uploadDir = __DIR__ . '/../assets/uploads';
                    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                    $allowedExt = ['jpg','jpeg','png','gif','webp'];
                    for ($i=0; $i < count($_FILES['cat_upload_images']['name']); $i++) {
                        $err = $_FILES['cat_upload_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                        if ($err !== UPLOAD_ERR_OK) { continue; }
                        $name = $_FILES['cat_upload_images']['name'][$i];
                        $tmp = $_FILES['cat_upload_images']['tmp_name'][$i];
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExt, true)) { continue; }
                        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($name, PATHINFO_FILENAME));
                        $destName = $safeBase . '-' . uniqid() . '.' . $ext;
                        $destPath = $uploadDir . '/' . $destName;
                        if (move_uploaded_file($tmp, $destPath)) {
                            $uploadedUrls[] = 'assets/uploads/' . $destName;
                        }
                    }
                }
                $textImages = array_values(array_filter(array_map('trim', explode(',', $_POST['images'] ?? ''))));
                $images = array_values(array_filter(array_merge($textImages, $uploadedUrls)));
                $cover = trim($_POST['image'] ?? '');
                if ($cover === '' && !empty($images)) { $cover = $images[0]; }
                // Keep old images if nothing provided on update
                $old = null; foreach ($cats as $ct2) { if (($ct2['id']??'') === $id) { $old = $ct2; break; } }
                if ($action === 'update' && empty($images) && isset($old['images'])) { $images = $old['images']; }
                $record = [
                    'id' => $id,
                    'name' => trim($_POST['name'] ?? ''),
                    'image' => $cover,
                    'images' => $images
                ];
                $exists = false;
                foreach ($cats as &$ct) { if ($ct['id'] === $id) { $ct = $record; $exists = true; break; } }
                if (!$exists) { $cats[] = $record; }
                writeJsonFile($cpath, $cats);
                header('Location: ?page=admin&tab=categories'); exit;
            }
            if ($action === 'delete') {
                $id = trim($_POST['id'] ?? '');
                $cats = array_values(array_filter($cats, function($c) use ($id){ return $c['id'] !== $id; }));
                writeJsonFile($cpath, $cats);
                header('Location: ?page=admin&tab=categories'); exit;
            }
        }
        ?>
        <div class="card"><div class="card-body">
            <h3><?php echo $editingCat ? 'Sửa chủ đề: '.htmlspecialchars($editingCat['name']??'') : 'Thêm / Sửa chủ đề'; ?></h3>
            <form method="post" class="form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editingCat ? 'update' : 'create'; ?>">
                <div class="grid" style="grid-template-columns:repeat(2,1fr);gap:12px">
                    <label>Mã (không dấu)<input name="id" placeholder="vd: truyenthong" value="<?php echo htmlspecialchars($editingCat['id'] ?? ''); ?>"></label>
                    <label>Tên chủ đề<input name="name" required placeholder="Truyền thống" value="<?php echo htmlspecialchars($editingCat['name'] ?? ''); ?>"></label>
                    <label class="full">Ảnh đại diện (URL)<input name="image" placeholder="https://..." value="<?php echo htmlspecialchars($editingCat['image'] ?? ''); ?>"></label>
                    <label class="full">Ảnh thư viện (nhiều URL, phân tách dấu phẩy)
                        <input name="images" placeholder="https://... , https://..." value="<?php echo htmlspecialchars(isset($editingCat['images'])?implode(',',(array)$editingCat['images']):''); ?>">
                        <span style="font-size:12px;color:var(--muted)">Có thể để trống nếu chỉ upload</span>
                    </label>
                    <label class="full">Tải ảnh thư viện từ máy (chọn nhiều)
                        <input type="file" name="cat_upload_images[]" multiple accept="image/*">
                        <span style="font-size:12px;color:var(--muted)">Ảnh sẽ lưu vào assets/uploads/</span>
                    </label>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Lưu</button></div>
            </form>
        </div></div>

        <h3 style="margin-top:16px">Danh sách chủ đề</h3>
        <table class="table">
            <thead><tr><th>Mã</th><th>Tên</th><th>Ảnh</th><th>Số ảnh</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($cats as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['id']); ?></td>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><?php echo htmlspecialchars($c['image'] ?? ''); ?></td>
                    <td><?php echo isset($c['images']) ? count($c['images']) : 0; ?></td>
                    <td>
                        <a class="btn" href="?page=admin&tab=categories&edit_cat=<?php echo urlencode($c['id']); ?>">Sửa</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Xoá chủ đề?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($c['id']); ?>">
                            <button class="btn">Xoá</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'products'): ?>
		<?php
        $path = __DIR__ . '/../data/products.json';
        $items = readJsonFile($path);
        $catPath = __DIR__ . '/../data/categories.json';
        $allCategories = readJsonFile($catPath);
        $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
        $editing = null;
        if ($editId) { foreach ($items as $it) { if ((int)$it['id'] === $editId) { $editing = $it; break; } } }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$action = $_POST['action'] ?? '';
            if ($action === 'create' || $action === 'update') {
				$id = $action==='create' ? (int)(max(array_column($items,'id') ?: [0]) + 1) : (int)$_POST['id'];
                $existing = null;
                if ($action === 'update') {
                    foreach ($items as $it) { if ((int)$it['id'] === $id) { $existing = $it; break; } }
                }
                // Handle uploaded images (multiple)
                $uploadedUrls = [];
                if (!empty($_FILES['upload_images']) && is_array($_FILES['upload_images']['name'])) {
                    $uploadDir = __DIR__ . '/../assets/uploads';
                    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                    $allowedExt = ['jpg','jpeg','png','gif','webp'];
                    for ($i=0; $i < count($_FILES['upload_images']['name']); $i++) {
                        $err = $_FILES['upload_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                        if ($err !== UPLOAD_ERR_OK) { continue; }
                        $name = $_FILES['upload_images']['name'][$i];
                        $tmp = $_FILES['upload_images']['tmp_name'][$i];
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedExt, true)) { continue; }
                        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($name, PATHINFO_FILENAME));
                        $destName = $safeBase . '-' . uniqid() . '.' . $ext;
                        $destPath = $uploadDir . '/' . $destName;
                        if (move_uploaded_file($tmp, $destPath)) {
                            $uploadedUrls[] = 'assets/uploads/' . $destName;
                        }
                    }
                }
                // Combine text URLs with uploaded URLs
                $textImages = array_values(array_filter(array_map('trim', explode(',', $_POST['images'] ?? ''))));
                $finalImages = array_values(array_filter(array_merge($textImages, $uploadedUrls)));
                if ($action === 'update' && empty($finalImages) && !empty($existing['images'])) {
                    $finalImages = $existing['images'];
                }
                // Build accessories array from parallel lists
                $accNames = array_values(array_filter(array_map('trim', explode(',', $_POST['acc_names'] ?? ''))));
                $accPrices = array_values(array_map('trim', explode(',', $_POST['acc_prices'] ?? '')));
                $accImages = array_values(array_map('trim', explode(',', $_POST['acc_images'] ?? '')));
                $accessories = [];
                $n = count($accNames);
                for ($i=0; $i<$n; $i++) {
                    $name = $accNames[$i] ?? '';
                    if ($name === '') continue;
                    $price = (int)preg_replace('/[^0-9]/','', $accPrices[$i] ?? '0');
                    $img = $accImages[$i] ?? '';
                    $accessories[] = ['name'=>$name,'pricePerDay'=>$price,'image'=>$img];
                }
				$record = [
					'id' => $id,
                    'name' => trim($_POST['name'] ?? ''),
                    'category' => trim($_POST['category'] ?? ''),
					'pricePerDay' => (int)($_POST['pricePerDay'] ?? 0),
					'deposit' => (int)($_POST['deposit'] ?? 0),
                    'shortDesc' => trim($_POST['shortDesc'] ?? ''),
                    'longDesc' => trim($_POST['longDesc'] ?? ''),
                    'material' => trim($_POST['material'] ?? ''),
					'sizes' => array_values(array_filter(array_map('trim', explode(',', $_POST['sizes'] ?? '')))),
					'colors' => array_values(array_filter(array_map('trim', explode(',', $_POST['colors'] ?? '')))),
					'events' => array_values(array_filter(array_map('trim', explode(',', $_POST['events'] ?? '')))),
                    'accessories' => $accessories,
                    'images' => $finalImages,
                    'isFeatured' => isset($_POST['isFeatured']) ? true : false,
                    'reviews' => $existing['reviews'] ?? []
				];
				$exists = false;
				foreach ($items as &$it) { if ($it['id'] === $id) { $it = $record; $exists = true; break; } }
				if (!$exists) { $items[] = $record; }
				writeJsonFile($path, $items);
				header('Location: ?page=admin&tab=products'); exit;
			}
            if ($action === 'delete') {
				$id = (int)$_POST['id'];
				$items = array_values(array_filter($items, function($p) use ($id){ return $p['id'] !== $id; }));
				writeJsonFile($path, $items);
				header('Location: ?page=admin&tab=products'); exit;
			}
            if ($action === 'toggle_featured') {
                $id = (int)$_POST['id'];
                foreach ($items as &$it) { if ($it['id'] === $id) { $it['isFeatured'] = empty($it['isFeatured']); break; } }
                writeJsonFile($path, $items);
                header('Location: ?page=admin&tab=products'); exit;
            }
		}
		?>
		<div class="card"><div class="card-body">
            <h3><?php echo $editing ? 'Sửa sản phẩm #'.(int)$editing['id'] : 'Thêm / Sửa sản phẩm'; ?></h3>
            <form method="post" class="form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
                <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>
                <div class="grid" style="grid-template-columns:repeat(2,1fr);gap:12px">
                    <label>Tên<input name="name" required value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>"></label>
                    <?php if (!empty($allCategories)): ?>
                    <label>Danh mục
                        <select name="category" required>
                            <?php foreach ($allCategories as $cat): $cid=$cat['id']??''; $cn=$cat['name']??$cid; ?>
                                <option value="<?php echo htmlspecialchars($cid); ?>" <?php echo (($editing['category']??'')===$cid)?'selected':''; ?>><?php echo htmlspecialchars($cn); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php else: ?>
                    <label>Danh mục<input name="category" required value="<?php echo htmlspecialchars($editing['category'] ?? ''); ?>" placeholder="vd: truyenthong"></label>
                    <?php endif; ?>
                    <label>Giá/ngày<input name="pricePerDay" type="number" min="0" required value="<?php echo htmlspecialchars((string)($editing['pricePerDay'] ?? '')); ?>"></label>
                    <label>Tiền cọc<input name="deposit" type="number" min="0" required value="<?php echo htmlspecialchars((string)($editing['deposit'] ?? '')); ?>"></label>
                    <label>Mô tả<input name="shortDesc" value="<?php echo htmlspecialchars($editing['shortDesc'] ?? ''); ?>"></label>
                    <label class="full">Mô tả chi tiết<textarea name="longDesc" rows="4" placeholder="Mô tả dài hiển thị ở trang sản phẩm"><?php echo htmlspecialchars($editing['longDesc'] ?? ''); ?></textarea></label>
                    <label>Chất liệu<input name="material" value="<?php echo htmlspecialchars($editing['material'] ?? ''); ?>"></label>
                    <label>Kích cỡ (phân tách dấu phẩy)<input name="sizes" placeholder="S,M,L,XL" value="<?php echo htmlspecialchars(isset($editing['sizes'])?implode(',',(array)$editing['sizes']):''); ?>"></label>
                    <label>Màu sắc (phân tách dấu phẩy)<input name="colors" placeholder="Đỏ,Xanh,Đen" value="<?php echo htmlspecialchars(isset($editing['colors'])?implode(',',(array)$editing['colors']):''); ?>"></label>
                    <label>Sự kiện (phân tách dấu phẩy)<input name="events" placeholder="Chụp ảnh,Cosplay" value="<?php echo htmlspecialchars(isset($editing['events'])?implode(',',(array)$editing['events']):''); ?>"></label>
                    <label class="full">Phụ kiện - Tên (nhiều, phân tách dấu phẩy)
                        <input name="acc_names" placeholder="Dù, Áo choàng, Mũ" value="<?php echo htmlspecialchars(isset($editing['accessories'])?implode(',', array_map(function($a){return (string)($a['name']??'');}, (array)$editing['accessories'])):''); ?>">
                    </label>
                    <label>Phụ kiện - Giá/ngày (đồng bộ thứ tự)
                        <input name="acc_prices" placeholder="20000, 30000, 15000" value="<?php echo htmlspecialchars(isset($editing['accessories'])?implode(',', array_map(function($a){return (string)($a['pricePerDay']??0);}, (array)$editing['accessories'])):''); ?>">
                    </label>
                    <label>Phụ kiện - Ảnh (URL, đồng bộ thứ tự)
                        <input name="acc_images" placeholder="https://... , https://..." value="<?php echo htmlspecialchars(isset($editing['accessories'])?implode(',', array_map(function($a){return (string)($a['image']??'');}, (array)$editing['accessories'])):''); ?>">
                    </label>
                    <label class="full">Ảnh (nhiều URL, cách nhau bởi dấu phẩy)
                        <input name="images" placeholder="https://... , https://..." value="<?php echo htmlspecialchars(isset($editing['images'])?implode(',',(array)$editing['images']):''); ?>">
                        <span style="font-size:12px;color:var(--muted)">Ví dụ: https://domain/anh1.jpg, https://domain/anh2.jpg</span>
                    </label>
                    <label class="full">Tải ảnh từ máy (chọn nhiều ảnh)
                        <input type="file" name="upload_images[]" multiple accept="image/*">
                        <span style="font-size:12px;color:var(--muted)">Ảnh sẽ lưu vào thư mục assets/uploads/ và tự thêm vào danh sách ảnh</span>
                    </label>
                    <label><input type="checkbox" name="isFeatured" value="1" <?php echo !empty($editing['isFeatured'])?'checked':''; ?>> Hiển thị trong "Sản phẩm nổi bật" (Trang chủ)</label>
				</div>
				<div class="form-actions"><button class="btn btn-primary" type="submit">Lưu</button></div>
			</form>
		</div></div>
		
		<h3 style="margin-top:16px">Danh sách sản phẩm</h3>
        <table class="table">
            <thead><tr><th>ID</th><th>Ảnh</th><th>Tên</th><th>Danh mục</th><th>Giá/ngày</th><th>Cọc</th><th>Nổi bật</th><th></th></tr></thead>
			<tbody>
				<?php foreach ($items as $p): ?>
				<tr>
					<td><?php echo (int)$p['id']; ?></td>
                    <td><?php if (!empty($p['images'][0])): ?><img src="<?php echo htmlspecialchars($p['images'][0]); ?>" alt="thumb" style="width:64px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--border)"><?php endif; ?></td>
					<td><?php echo htmlspecialchars($p['name']); ?></td>
					<td><?php echo htmlspecialchars($p['category']); ?></td>
					<td><?php echo number_format((int)$p['pricePerDay'],0,',','.'); ?>₫</td>
					<td><?php echo number_format((int)$p['deposit'],0,',','.'); ?>₫</td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="toggle_featured">
                            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                            <button class="btn" type="submit"><?php echo !empty($p['isFeatured']) ? '✔ Đang nổi bật' : 'Đặt nổi bật'; ?></button>
                        </form>
                    </td>
                    <td class="admin-actions">
                        <a class="btn" href="?page=admin&tab=products&edit=<?php echo (int)$p['id']; ?>">Sửa</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Xoá sản phẩm?')">
							<input type="hidden" name="action" value="delete">
							<input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
							<button class="btn">Xoá</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

    <?php elseif ($tab === 'orders'): ?>
        <?php $orders = readJsonFile(__DIR__ . '/../data/orders.json'); $view = $_GET['code'] ?? null; $current=null; if($view){ foreach($orders as $oo){ if(($oo['code']??'')===$view){ $current=$oo; break; } } } ?>
        <?php if ($current): ?>
            <div class="card"><div class="card-body">
                <a class="btn" href="?page=admin&tab=orders">← Quay lại</a>
                <h3>Đơn hàng: <?php echo htmlspecialchars($current['code']); ?></h3>
                <div class="grid" style="grid-template-columns:1fr 1fr;gap:12px">
                    <div class="card"><div class="card-body">
                        <h4>Khách hàng</h4>
                        <div><strong>Tên:</strong> <?php echo htmlspecialchars($current['customer']['name'] ?? ''); ?></div>
                        <div><strong>Điện thoại:</strong> <?php echo htmlspecialchars($current['customer']['phone'] ?? ''); ?></div>
                        <div><strong>Email:</strong> <?php echo htmlspecialchars($current['customer']['email'] ?? ''); ?></div>
                        <div><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($current['customer']['address'] ?? ''); ?></div>
                    </div></div>
                    <div class="card"><div class="card-body">
                        <h4>Thanh toán & Vận chuyển</h4>
                        <div><strong>Thanh toán:</strong> <?php echo htmlspecialchars($current['payment'] ?? ''); ?></div>
                        <div><strong>Vận chuyển:</strong> <?php echo htmlspecialchars($current['shipping'] ?? ''); ?></div>
                        <div><strong>Ghi chú:</strong> <?php echo htmlspecialchars($current['note'] ?? ''); ?></div>
                        <div><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($current['createdAt'] ?? ''); ?></div>
                    </div></div>
                </div>
                <h4>Danh sách món</h4>
                <table class="table"><thead><tr><th>#</th><th>Sản phẩm</th><th>Size</th><th>Từ</th><th>Ngày</th><th>SL</th><th>Đơn giá</th><th>Tạm tính</th></tr></thead><tbody>
                    <?php foreach ($current['items'] as $i => $it): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td>#<?php echo (int)$it['id']; ?></td>
                            <td><?php echo htmlspecialchars($it['size'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($it['from'] ?? ''); ?></td>
                            <td><?php echo (int)($it['days'] ?? 1); ?></td>
                            <td><?php echo (int)max(1,(int)($it['qty'] ?? 1)); ?></td>
                            <td><?php echo number_format((int)($it['pricePerDay'] ?? 0),0,',','.'); ?>₫</td>
                            <td><?php $qty=max(1,(int)($it['qty'] ?? 1)); echo number_format(((int)($it['pricePerDay'] ?? 0))*max(1,(int)($it['days'] ?? 1))*$qty,0,',','.'); ?>₫</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table>
                <div style="text-align:right"><strong>Tổng cộng: <?php echo number_format((int)($current['total'] ?? 0),0,',','.'); ?>₫</strong></div>
            </div></div>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Mã</th><th>Khách</th><th>Số SP</th><th>Tổng</th><th>Ngày</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['code'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($o['customer']['name'] ?? ''); ?></td>
                        <td><?php echo isset($o['items']) ? count($o['items']) : 0; ?></td>
                        <td><?php echo number_format((int)($o['total'] ?? 0),0,',','.'); ?>₫</td>
                        <td><?php echo htmlspecialchars($o['createdAt'] ?? ''); ?></td>
                        <td><a class="btn" href="?page=admin&tab=orders&code=<?php echo urlencode($o['code']); ?>">Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

	<?php elseif ($tab === 'users'): ?>
		<?php $users = loadUsers(); ?>
		<table class="table">
			<thead><tr><th>Tên</th><th>Email</th><th>Điện thoại</th><th>Quyền</th></tr></thead>
			<tbody>
				<?php foreach ($users as $u): ?>
				<tr>
					<td><?php echo htmlspecialchars($u['name']); ?></td>
					<td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
					<td><?php echo htmlspecialchars($u['phone'] ?? ''); ?></td>
					<td><?php echo !empty($u['isAdmin']) ? 'Admin' : 'User'; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php elseif ($tab === 'feedback'): ?>
		<?php $feedback = readJsonFile(__DIR__ . '/../data/feedback.json'); ?>
		<table class="table">
			<thead><tr><th>Tên</th><th>Email</th><th>Nội dung</th><th>Ngày</th></tr></thead>
			<tbody>
				<?php foreach ($feedback as $f): ?>
				<tr>
					<td><?php echo htmlspecialchars($f['name']); ?></td>
					<td><?php echo htmlspecialchars($f['email']); ?></td>
					<td><?php echo htmlspecialchars($f['content']); ?></td>
					<td><?php echo htmlspecialchars($f['createdAt']); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>


