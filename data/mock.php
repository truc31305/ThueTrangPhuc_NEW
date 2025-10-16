<?php
// Danh mục demo
$MOCK_CATEGORIES = [
	['id' => 'truyenthong', 'name' => 'Truyền thống'],
	['id' => 'cosplay', 'name' => 'Cosplay'],
	['id' => 'diano', 'name' => 'Dạ hội'],
	['id' => 'sukien', 'name' => 'Biểu diễn/Sự kiện'],
];

// Sản phẩm demo
$MOCK_PRODUCTS = [];
for ($i = 1; $i <= 24; $i++) {
	$cat = $MOCK_CATEGORIES[$i % count($MOCK_CATEGORIES)]['id'];
	$colors = ['Đỏ','Xanh','Đen','Trắng','Vàng'];
	$sizes = ['S','M','L','XL'];
	$images = [
		"https://picsum.photos/seed/{$i}a/800/600",
		"https://picsum.photos/seed/{$i}b/800/600",
		"https://picsum.photos/seed/{$i}c/800/600"
	];
	$MOCK_PRODUCTS[] = [
		'id' => $i,
		'name' => 'Trang phục #' . $i,
		'category' => $cat,
		'pricePerDay' => 150000 + ($i % 5) * 25000,
		'deposit' => 200000 + ($i % 3) * 50000,
		'shortDesc' => 'Trang phục phù hợp nhiều dịp, chất liệu bền đẹp.',
		'material' => 'Poly-cotton cao cấp',
		'sizes' => $sizes,
		'colors' => $colors,
		'events' => ['Chụp ảnh','Biểu diễn','Sự kiện','Cosplay'],
		'images' => $images,
		'reviews' => [
			['name'=>'Hà','rating'=>5,'comment'=>'Đồ đẹp, giao nhanh.'],
			['name'=>'Minh','rating'=>4,'comment'=>'Phù hợp giá, chất liệu ok.']
		],
	];
}

function findProductById($id) {
	global $MOCK_PRODUCTS;
	foreach ($MOCK_PRODUCTS as $p) {
		if ($p['id'] === (int)$id) return $p;
	}
	return null;
}

function findRelatedProducts($category, $excludeId, $limit = 6) {
	global $MOCK_PRODUCTS;
	$rel = array_values(array_filter($MOCK_PRODUCTS, function($p) use ($category, $excludeId){
		return $p['category'] === $category && $p['id'] !== (int)$excludeId;
	}));
	return array_slice($rel, 0, $limit);
}

?>


