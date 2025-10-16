<?php
require_once __DIR__ . '/../inc/storage.php';
$all = readJsonFile(__DIR__ . '/../data/products.json');
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$product = null;
foreach ($all as $p) { if ((int)($p['id'] ?? 0) === $id) { $product = $p; break; } }
if (!$product) { $product = ['id'=>$id,'name'=>'Không tìm thấy','pricePerDay'=>200000,'images'=>['https://via.placeholder.com/800x600?text=Not+found'],'shortDesc'=>'','material'=>'','sizes'=>[],'colors'=>[],'events'=>[],'deposit'=>0,'longDesc'=>'']; }
?>
<article class="product-detail">
	<div class="gallery" data-gallery>
		<div class="media" data-main>
			<img src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" data-main-img>
			<div class="zoom-lens" data-zoom-lens></div>
		</div>
		<div class="thumbs" data-thumbs>
			<?php foreach ($product['images'] as $idx => $img): ?>
				<div class="thumb <?php echo $idx===0?'active':''; ?>" data-thumb data-src="<?php echo htmlspecialchars($img); ?>">
					<img src="<?php echo htmlspecialchars($img); ?>" alt="thumb">
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="info">
		<h1><?php echo htmlspecialchars($product['name']); ?></h1>
		<p class="price">
			<?php echo number_format($product['pricePerDay'], 0, ',', '.'); ?>₫/ngày · Cọc: <?php echo number_format($product['deposit'], 0, ',', '.'); ?>₫
		</p>
		<form method="post" action="?page=cart">
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<label>
				Kích cỡ
				<select name="size" required>
					<option value="S">S</option>
					<option value="M" selected>M</option>
					<option value="L">L</option>
					<option value="XL">XL</option>
				</select>
			</label>
			<label>
				Ngày thuê
				<input type="date" name="from" required>
			</label>
			<label>
				Số ngày
				<input type="number" name="days" min="1" value="2" required>
			</label>
			<button class="btn btn-primary" type="submit">Thêm vào giỏ/Đặt</button>
		</form>
		<div class="desc">
            <h3>Mô tả</h3>
            <p><?php echo htmlspecialchars($product['longDesc'] ?: $product['shortDesc']); ?></p>
			<ul>
				<li><strong>Chất liệu:</strong> <?php echo htmlspecialchars($product['material']); ?></li>
				<li><strong>Kích cỡ:</strong> <?php echo htmlspecialchars(implode(', ', $product['sizes'])); ?></li>
				<li><strong>Màu sắc:</strong> <?php echo htmlspecialchars(implode(', ', $product['colors'])); ?></li>
				<li><strong>Phù hợp:</strong> <?php echo htmlspecialchars(implode(', ', $product['events'])); ?></li>
			</ul>
			<h3>Hướng dẫn thuê / trả</h3>
			<ol>
				<li>Chọn ngày thuê và số ngày sử dụng phù hợp.</li>
				<li>Đặt cọc theo quy định; giữ hoá đơn.</li>
				<li>Giữ gìn sản phẩm, không tự ý chỉnh sửa.</li>
				<li>Hoàn trả đúng hẹn để nhận lại tiền cọc.</li>
			</ol>
			<h3>Bảng size tham khảo</h3>
			<table class="table">
				<thead><tr><th>Size</th><th>Ngực (cm)</th><th>Eo (cm)</th><th>Mông (cm)</th></tr></thead>
				<tbody>
					<tr><td>S</td><td>80-86</td><td>62-68</td><td>86-92</td></tr>
					<tr><td>M</td><td>86-92</td><td>68-74</td><td>92-98</td></tr>
					<tr><td>L</td><td>92-98</td><td>74-80</td><td>98-104</td></tr>
					<tr><td>XL</td><td>98-104</td><td>80-86</td><td>104-110</td></tr>
				</tbody>
			</table>
			<h3>Phí & lưu ý</h3>
			<ul>
				<li>Giá thuê tính theo ngày; phụ phí khi quá hạn 30%/ngày.</li>
				<li>Tiền cọc: <?php echo number_format($product['deposit'], 0, ',', '.'); ?>₫; hoàn lại khi trả đủ & đúng hẹn.</li>
				<li>Hư hỏng/mất phụ kiện: tính phí theo mức độ (từ 20.000₫).</li>
			</ul>
			<h3>Chính sách huỷ</h3>
			<ul>
				<li>Huỷ trước 48h: hoàn 100% cọc.</li>
				<li>Huỷ trước 24-48h: hoàn 50% cọc.</li>
				<li>Huỷ dưới 24h hoặc không nhận: không hoàn cọc.</li>
			</ul>
		</div>
	</div>
</article>

<section class="section">
	<h2>Đánh giá</h2>
	<div class="grid" style="grid-template-columns:1fr 1fr;gap:12px">
		<?php foreach ($product['reviews'] as $rv): ?>
			<div class="card"><div class="card-body">
				<strong><?php echo htmlspecialchars($rv['name']); ?></strong>
				<span style="color:#f59e0b;margin-left:6px"><?php echo str_repeat('★', (int)$rv['rating']); ?><?php echo str_repeat('☆', 5-(int)$rv['rating']); ?></span>
				<div><?php echo htmlspecialchars($rv['comment']); ?></div>
			</div></div>
		<?php endforeach; ?>
	</div>
</section>

<?php $related = findRelatedProducts($product['category'], $product['id']); ?>
<section class="section">
	<h2>Sản phẩm liên quan</h2>
	<div class="grid products-grid">
		<?php foreach ($related as $rp): ?>
			<a class="card" href="?page=product&id=<?php echo $rp['id']; ?>">
				<div class="card-thumb"><img src="<?php echo htmlspecialchars($rp['images'][0]); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>" style="width:100%;height:100%;object-fit:cover"></div>
				<div class="card-body">
					<h3><?php echo htmlspecialchars($rp['name']); ?></h3>
					<p class="price">Từ <?php echo number_format($rp['pricePerDay'], 0, ',', '.'); ?>₫/ngày</p>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>

