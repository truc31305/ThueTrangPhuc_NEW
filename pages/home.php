<?php if (isset($_GET['registered']) && $_GET['registered'] == '1') { ?>
	<div class="alert alert-success">Tạo tài khoản hoàn tất! Vui lòng đăng nhập.</div>
	<script>
		document.addEventListener('DOMContentLoaded', function(){
			var t = document.querySelector('[data-auth-toggle]');
			if (t) { t.click(); }
		});
	</script>
<?php } ?>
<section class="hero">
	<div class="hero-content" style="display:grid;grid: template columns 1.2em;fr .8fr;gap:20px;align-items:center">
		<div>
			<h1>Thuê trang phục đẹp cho mọi sự kiện</h1>
			<p>🌸 GIỚI THIỆU VỀ SAPAQT
SAPAQT là cửa hàng cho thuê trang phục chuyên nghiệp, ra đời với mong muốn mang đến cho khách hàng sự tự tin – sang trọng – phong cách trong mọi khoảnh khắc quan trọng của cuộc sống. Chúng tôi hiểu rằng, không phải lúc nào bạn cũng cần sở hữu một bộ trang phục đắt tiền, nhưng bạn vẫn cần xuất hiện thật đẹp, thật nổi bật trong những dịp đặc biệt. Và SAPAQT chính là giải pháp hoàn hảo cho bạn.

🎭 Danh mục trang phục tại SAPAQT
Trang phục truyền thống: Áo dài, áo tứ thân, áo bà ba, áo Nhật Bình, Hanbok, Kimono, Sari…
Trang phục hiện đại & sự kiện: Váy dạ hội, váy cưới, vest – suit nam, đầm dự tiệc, trang phục công sở cao cấp.
Trang phục chụp ảnh & kỷ yếu: Đồng phục nhóm, áo dài học sinh, trang phục chụp ngoại cảnh.
Trang phục biểu diễn & cosplay: Nhân vật hoạt hình, phim ảnh, ca múa nhạc, trang phục lễ hội.
Tất cả các mẫu đều được chọn lọc kỹ lưỡng, cập nhật theo xu hướng, với nhiều size và kiểu dáng khác nhau để phù hợp với mọi khách hàng.

💎 Giá trị mà SAPAQT mang lại
Giúp khách hàng tiết kiệm chi phí nhưng vẫn được trải nghiệm trang phục cao cấp.
Đảm bảo mỗi bộ trang phục đều sạch sẽ, thơm tho, như mới trước khi đến tay khách hàng.
Đội ngũ nhân viên nhiệt tình tư vấn để bạn chọn được bộ đồ phù hợp nhất.
Không chỉ cho thuê, SAPAQT còn gợi ý phụ kiện mix & match đi kèm để bạn thêm phần hoàn hảo.
SAPAQT – Nâng tầm phong cách, lưu giữ khoảnh khắc.</p>
			<a href="?page=catalog" class="btn btn-primary">Khám phá ngay</a>
		</div>
		<div class="card" style="overflow:hidden;border-radius:12px;max-width:400px;margin:0 auto">
			<img src="assets/images/baner_cuahang.jpg" alt="Banner cửa hàng" style="width:100%;height: 500px;px;object-fit:cover">
		</div>
	</div>
</section>

<?php $CATS = json_decode(@file_get_contents(__DIR__ . '/../data/categories.json'), true) ?: []; ?>
<?php $PROMOS = json_decode(@file_get_contents(__DIR__ . '/../data/promotions.json'), true) ?: []; ?>
<?php if (!empty($PROMOS)): ?>
<section class="section">
    <h2>Chương trình khuyến mãi</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
        <?php foreach ($PROMOS as $pr): if (empty($pr['active'])) continue; ?>
            <div class="card"><div class="card-body">
                <?php if (!empty($pr['banner'])): ?><div class="card-thumb"><img src="<?php echo htmlspecialchars($pr['banner']); ?>" alt="promo"></div><?php endif; ?>
                <h3><?php echo htmlspecialchars($pr['title']); ?></h3>
                <div style="color:var(--muted);font-size:14px;margin:6px 0">Hiệu lực: <?php echo htmlspecialchars(($pr['validFrom'] ?? '').' → '.($pr['validTo'] ?? '')); ?></div>
                <p><?php echo htmlspecialchars($pr['description']); ?></p>
            </div></div>
        <?php endforeach; ?>
    </div>
    <div class="alert" style="background:#fff;border:1px dashed var(--border);color:#374151;margin-top:10px">Áp dụng tự động khi đặt hàng trong thời gian khuyến mãi.</div>
</section>
<?php endif; ?>

<section class="section">
    <h2>Danh mục nổi bật</h2>
    <div class="grid categories-grid">
        <?php foreach ($CATS as $c): ?>
        <a class="card" href="?page=catalog&cat=<?php echo urlencode($c['id']); ?>">
            <div class="card-thumb"><img src="<?php echo htmlspecialchars($c['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" style="width:100%;height:100%;object-fit:cover"></div>
            <div class="card-body">
                <h3><?php echo htmlspecialchars($c['name']); ?></h3>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Gallery theo chủ đề đã được gỡ bỏ theo yêu cầu -->

<?php $PRODUCTS = require __DIR__ . '/../pages/home_products_loader.php'; ?>
<section class="section">
    <h2>Sản phẩm nổi bật</h2>
    <div class="grid products-grid">
        <?php foreach ($PRODUCTS as $p): ?>
            <a class="card" href="?page=product&id=<?php echo (int)$p['id']; ?>">
                <div class="card-thumb"><img src="<?php echo htmlspecialchars($p['images'][0] ?? ''); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%;height:100%;object-fit:cover"></div>
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    <p class="price">Từ <?php echo number_format((int)$p['pricePerDay'], 0, ',', '.'); ?>₫/ngày</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

