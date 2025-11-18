<?php
require_once __DIR__ . '/../inc/storage.php';
function findProductPriceById($id){
    $all = readJsonFile(__DIR__ . '/../data/products.json');
    foreach ($all as $p) { if ((int)($p['id'] ?? 0) === (int)$id) return (int)($p['pricePerDay'] ?? 0); }
    return 0;
}
if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	$action = $_POST['action'];
    if ($action === 'add') {
		$pid = intval($_POST['id'] ?? 0);
        $price = findProductPriceById($pid) ?: 200000;
		$item = [
			'id' => $pid,
			'size' => $_POST['size'] ?? 'M',
			'from' => $_POST['from'] ?? '',
			'days' => intval($_POST['days'] ?? 1),
            'qty' => max(1, intval($_POST['qty'] ?? 1)),
            'pricePerDay' => (int)$price,
            'accessories' => []
		];
        // Collect selected accessories
        $sel = isset($_POST['acc_sel']) && is_array($_POST['acc_sel']) ? array_map('intval', $_POST['acc_sel']) : [];
        foreach ($sel as $idx) {
            $an = $_POST['acc_name'][$idx] ?? '';
            $ap = (int)($_POST['acc_price'][$idx] ?? 0);
            $ai = $_POST['acc_image'][$idx] ?? '';
            if ($an !== '' && $ap > 0) {
                $item['accessories'][] = ['name'=>$an,'pricePerDay'=>$ap,'image'=>$ai];
            }
        }
		$_SESSION['cart'][] = $item;
		header('Location: ?page=cart');
		exit;
	}
	if ($action === 'remove' && isset($_POST['idx'])) {
		$idx = intval($_POST['idx']);
		if (isset($_SESSION['cart'][$idx])) {
			unset($_SESSION['cart'][$idx]);
			$_SESSION['cart'] = array_values($_SESSION['cart']);
		}
		header('Location: ?page=cart');
		exit;
	}
}

$total = 0;
foreach ($_SESSION['cart'] as $it) {
    $qty = max(1, (int)($it['qty'] ?? 1));
    $days = max(1, (int)$it['days']);
    $base = $it['pricePerDay'] * $days * $qty;
    $accSum = 0;
    foreach (($it['accessories'] ?? []) as $ac) { $accSum += (int)($ac['pricePerDay'] ?? 0) * $days * $qty; }
    $total += $base + $accSum;
}
?>

<section class="section">
	<h1>Giỏ hàng/Đặt lịch</h1>
	<?php if (empty($_SESSION['cart'])): ?>
		<p>Chưa có sản phẩm nào. <a href="?page=catalog">Tiếp tục xem</a></p>
	<?php else: ?>
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>Sản phẩm</th>
					<th>Kích cỡ</th>
                    <th>Từ ngày</th>
                    <th>Số ngày</th>
                    <th>SL</th>
					<th>Đơn giá</th>
					<th>Tạm tính</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($_SESSION['cart'] as $idx => $it): ?>
					<tr>
						<td><?php echo $idx + 1; ?></td>
                        <td>
                            Trang phục #<?php echo $it['id']; ?>
                            <?php if (!empty($it['accessories'])): ?>
                            <div style="font-size:13px;color:var(--muted);margin-top:4px">
                                Phụ kiện: 
                                <?php $names = array_map(function($a){return htmlspecialchars($a['name'] ?? '');}, $it['accessories']); echo implode(', ', $names); ?>
                            </div>
                            <?php endif; ?>
                        </td>
						<td><?php echo htmlspecialchars($it['size']); ?></td>
                        <td><?php echo htmlspecialchars($it['from']); ?></td>
                        <td><?php echo (int)$it['days']; ?></td>
                        <td><?php echo (int)max(1, (int)($it['qty'] ?? 1)); ?></td>
						<td><?php echo number_format($it['pricePerDay'], 0, ',', '.'); ?>₫</td>
                        <td>
                            <?php
                            $qty = max(1,(int)($it['qty'] ?? 1));
                            $days = max(1,(int)$it['days']);
                            $base = $it['pricePerDay'] * $days * $qty;
                            $accSum = 0; foreach (($it['accessories'] ?? []) as $ac) { $accSum += (int)($ac['pricePerDay'] ?? 0) * $days * $qty; }
                            echo number_format($base + $accSum, 0, ',', '.');
                            ?>₫
                        </td>
						<td>
							<form method="post" action="?page=cart">
								<input type="hidden" name="action" value="remove">
								<input type="hidden" name="idx" value="<?php echo $idx; ?>">
								<button class="btn btn-link" type="submit">Xóa</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="cart-total">
			<strong>Tổng tiền sản phẩm: <?php echo number_format($total, 0, ',', '.'); ?>₫</strong>
		</div>
		<form class="checkout" method="post" action="?page=order">
			<h3>Thông tin khách hàng</h3>
			<div class="grid form-grid">
				<input name="customer[name]" type="text" placeholder="Họ tên" required>
				<input name="customer[phone]" type="tel" placeholder="Số điện thoại" required>
				<input name="customer[email]" type="email" placeholder="Email (không bắt buộc)">
				<input name="customer[address]" type="text" placeholder="Địa chỉ nhận hàng">
			</div>
			<h3>Vận chuyển</h3>
			<div class="grid" style="grid-template-columns:1fr 1fr;gap:12px">
				<label class="card" style="padding:12px"><input type="radio" name="shipping[method]" value="store" checked> Nhận tại cửa hàng (0₫)</label>
				<label class="card" style="padding:12px"><input type="radio" name="shipping[method]" value="delivery"> Giao tận nơi (30.000₫ nội thành)</label>
			</div>
			<h3>Thanh toán</h3>
			<div class="grid" style="grid-template-columns:1fr 1fr;gap:12px">
				<label class="card" style="padding:12px"><input type="radio" name="payment[method]" value="cod" checked> Thanh toán khi nhận (COD)</label>
				<label class="card" style="padding:12px"><input type="radio" name="payment[method]" value="bank"> Chuyển khoản ngân hàng</label>
			</div>
			<div class="card" style="margin-top:8px"><div class="card-body">
				<h4>Thông tin chuyển khoản</h4>
				<p><strong>Ngân hàng:</strong> BIDV – CN Đắk Nông PGD Nhân Cơ</p>
				<p><strong>Số tài khoản:</strong> 6353995032</p>
				<p><strong>Chủ tài khoản:</strong> LÊ THỊ HỒNG TRÚC</p>
				<p><strong>Nội dung CK:</strong> [Tên khách hàng] + [Mã đơn thuê]</p>
				<div style="display:flex;align-items:center;gap:16px;margin-top:8px">
					<img src="assets/images/qr_bidv.png" alt="QR chuyển khoản BIDV" style="width:160px;height:160px;border:1px solid var(--border);border-radius:8px;background:#fff;object-fit:contain">
					<div style="font-size:14px;color:var(--muted)">Quét QR để chuyển khoản nhanh. Vui lòng ghi đúng nội dung để đối soát.</div>
				</div>
			</div></div>
			<h3>Ghi chú</h3>
			<textarea name="note" rows="3" placeholder="Yêu cầu thêm..."></textarea>
			<div style="margin-top:12px">
				<button class="btn btn-primary" type="submit">Xác nhận đặt</button>
			</div>
		</form>
	<?php endif; ?>
</section>

