<?php
require_once __DIR__ . '/../inc/storage.php';
$all = readJsonFile(__DIR__ . '/../data/products.json');
$cats = readJsonFile(__DIR__ . '/../data/categories.json');
$cat = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : 'tatca';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$filtered = array_values(array_filter($all, function($p) use ($cat, $q){
    $okCat = ($cat === 'tatca') || ($p['category'] === $cat);
    $okQ = $q === '' || stripos($p['name'], $q) !== false;
    return $okCat && $okQ;
}));
$currentCat = null; foreach ($cats as $c) { if (($c['id'] ?? '') === $cat) { $currentCat = $c; break; } }
?>
<section class="section">
	<h1>Danh mục: <?php echo $cat === 'tatca' ? 'Tất cả' : ucfirst($cat); ?></h1>
    <?php if ($currentCat && !empty($currentCat['images'])): $imgs=$currentCat['images']; $showMore = count($imgs) > 5; ?>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin:12px 0" id="catGallery">
        <?php foreach (array_slice($imgs, 0, 5) as $im): ?>
            <div class="card"><div class="card-thumb"><img src="<?php echo htmlspecialchars($im); ?>" alt="cat"></div></div>
        <?php endforeach; ?>
        <?php if ($showMore): ?>
            <div id="catMore" style="display:none">
                <?php foreach (array_slice($imgs, 5) as $im): ?>
                    <div class="card"><div class="card-thumb"><img src="<?php echo htmlspecialchars($im); ?>" alt="cat"></div></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($showMore): ?>
    <div style="text-align:center;margin-bottom:12px">
        <button class="btn" id="btnShowMore">Xem thêm</button>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn=document.getElementById('btnShowMore'); var more=document.getElementById('catMore');
        if(btn&&more){ btn.addEventListener('click', function(){ more.style.display='block'; btn.style.display='none'; }); }
    });
    </script>
    <?php endif; endif; ?>
	<div class="catalog-toolbar">
		<form method="get" class="filters">
			<input type="hidden" name="page" value="catalog">
			<select name="cat">
				<option value="tatca" <?php echo $cat==='tatca'?'selected':''; ?>>Tất cả</option>
				<option value="truyenthong" <?php echo $cat==='truyenthong'?'selected':''; ?>>Truyền thống</option>
				<option value="cosplay" <?php echo $cat==='cosplay'?'selected':''; ?>>Cosplay</option>
				<option value="diano" <?php echo $cat==='diano'?'selected':''; ?>>Dạ hội</option>
				<option value="sukien" <?php echo $cat==='sukien'?'selected':''; ?>>Biểu diễn/Sự kiện</option>
			</select>
			<input type="text" name="q" placeholder="Tìm kiếm..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
			<button class="btn" type="submit">Lọc</button>
		</form>
	</div>
	<div class="grid products-grid">
        <?php foreach ($filtered as $p): ?>
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

<?php if (!empty($cats)): ?>
<section class="section">
    <h2>Sản phẩm theo danh mục</h2>
    <?php foreach ($cats as $c): $cid = $c['id'] ?? ''; $cname = $c['name'] ?? $cid; if (!$cid) continue; $group = array_values(array_filter($all, function($p) use ($cid){ return ($p['category'] ?? '') === $cid; })); if (!$group) continue; ?>
        <h3><?php echo htmlspecialchars($cname); ?></h3>
        <div class="grid products-grid" style="margin-bottom:16px">
            <?php foreach ($group as $p): ?>
                <a class="card" href="?page=product&id=<?php echo (int)$p['id']; ?>">
                    <div class="card-thumb"><img src="<?php echo htmlspecialchars($p['images'][0] ?? ''); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%;height:100%;object-fit:cover"></div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p class="price">Từ <?php echo number_format((int)$p['pricePerDay'], 0, ',', '.'); ?>₫/ngày</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

