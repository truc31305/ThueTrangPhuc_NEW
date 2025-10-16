<?php
require_once __DIR__ . '/../inc/storage.php';
$all = readJsonFile(__DIR__ . '/../data/products.json');
$featured = array_values(array_filter($all, function($p){ return !empty($p['isFeatured']); }));
return array_slice($featured, 0, 8);
?>


