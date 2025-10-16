<?php
require_once __DIR__ . '/../inc/storage.php';
// Simple demo confirmation page: summarize order and persist to JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ?page=cart');
	exit;
}

$customer = $_POST['customer'] ?? [];
$shipping = $_POST['shipping']['method'] ?? 'store';
$payment  = $_POST['payment']['method'] ?? 'cod';
$note     = $_POST['note'] ?? '';

$shippingFee = $shipping === 'delivery' ? 30000 : 0;

// Calculate totals from session cart
$items = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($items as $it) { $subtotal += $it['pricePerDay'] * max(1,(int)$it['days']); }
$total = $subtotal + $shippingFee;

// Generate a demo order code
$orderCode = 'TP' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

// Save order to JSON
$ordersPath = __DIR__ . '/../data/orders.json';
$orders = readJsonFile($ordersPath);
$orders[] = [
	'code' => $orderCode,
	'customer' => $customer,
	'items' => $items,
	'subtotal' => $subtotal,
	'shippingFee' => $shippingFee,
	'total' => $total,
	'payment' => $payment,
	'shipping' => $shipping,
	'note' => $note,
	'createdAt' => date('c')
];
writeJsonFile($ordersPath, $orders);
?>

<section class="section">
	<h1>Đặt hàng thành công</h1>
	<p>Cảm ơn bạn đã đặt hàng. Mã đơn: <strong><?php echo $orderCode; ?></strong></p>
	<?php if ($payment === 'bank'): ?>
	<div class="card"><div class="card-body">
		<h3>Thông tin chuyển khoản</h3>
		<p><strong>Ngân hàng:</strong> BIDV – CN Đắk Nông PGD Nhân Cơ</p>
		<p><strong>Số tài khoản:</strong> 6353995032</p>
		<p><strong>Chủ tài khoản:</strong> LÊ THỊ HỒNG TRÚC</p>
		<p><strong>Nội dung CK:</strong> <?php echo htmlspecialchars($customer['name'] ?? ''); ?> + <?php echo $orderCode; ?></p>
		<div style="display:flex;align-items:center;gap:16px;margin-top:8px">
			<img src="assets/images/qr_bidv.png" alt="QR chuyển khoản BIDV" style="width:160px;height:160px;border:1px solid var(--border);border-radius:8px;background:#fff;object-fit:contain">
			<div style="font-size:14px;color:var(--muted)">Quét QR để chuyển khoản nhanh. Vui lòng ghi đúng nội dung để đối soát.</div>
		</div>
	</div></div>
	<?php endif; ?>
	<h3>Thông tin đơn</h3>
	<div class="card"><div class="card-body">
		<p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($customer['name'] ?? ''); ?> - <?php echo htmlspecialchars($customer['phone'] ?? ''); ?></p>
		<p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? ''); ?></p>
		<p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($customer['address'] ?? ''); ?></p>
		<p><strong>Vận chuyển:</strong> <?php echo $shipping === 'delivery' ? 'Giao tận nơi' : 'Nhận tại cửa hàng'; ?> (Phí: <?php echo number_format($shippingFee,0,',','.'); ?>₫)</p>
		<p><strong>Thanh toán:</strong> <?php echo $payment === 'bank' ? 'Chuyển khoản' : 'COD'; ?></p>
		<p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($note)); ?></p>
	</div></div>

	<h3>Danh sách sản phẩm</h3>
	<table class="table">
		<thead><tr><th>#</th><th>Sản phẩm</th><th>Kích cỡ</th><th>Từ ngày</th><th>Số ngày</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
		<tbody>
			<?php foreach ($items as $i => $it): ?>
			<tr>
				<td><?php echo $i+1; ?></td>
				<td>Trang phục #<?php echo (int)$it['id']; ?></td>
				<td><?php echo htmlspecialchars($it['size']); ?></td>
				<td><?php echo htmlspecialchars($it['from']); ?></td>
				<td><?php echo (int)$it['days']; ?></td>
				<td><?php echo number_format($it['pricePerDay'],0,',','.'); ?>₫</td>
				<td><?php echo number_format($it['pricePerDay']*max(1,(int)$it['days']),0,',','.'); ?>₫</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p><strong>Tạm tính:</strong> <?php echo number_format($subtotal,0,',','.'); ?>₫</p>
	<p><strong>Phí vận chuyển:</strong> <?php echo number_format($shippingFee,0,',','.'); ?>₫</p>
	<p><strong>Tổng cộng:</strong> <?php echo number_format($total,0,',','.'); ?>₫</p>

	<div style="margin-top:12px">
		<a class="btn" href="?page=catalog">Tiếp tục mua sắm</a>
		<a class="btn" href="?page=home">Về trang chủ</a>
	</div>
</section>
<?php $_SESSION['cart'] = []; ?>

