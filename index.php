<?php
session_start();

// Development error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

// Whitelist available pages
	$routes = [
	'home'     => 'pages/home.php',
	'catalog'  => 'pages/catalog.php',
	'product'  => 'pages/product.php',
	'cart'     => 'pages/cart.php',
	'order'    => 'pages/order.php',
	'about'    => 'pages/about.php',
	'admin'    => 'pages/admin.php',
	'feedback' => 'pages/feedback.php',
		'@feedback' => 'pages/@feedback.php',
	'policy'   => 'pages/policy.php',
	'login'    => 'pages/login.php',
	'logout'   => 'pages/logout.php',
	'register' => 'pages/register.php',
];

$target = isset($routes[$page]) ? $routes[$page] : $routes['home'];

// Shared layout
require __DIR__ . '/inc/header.php';

// Route content
require __DIR__ . '/' . $target;
//🌸
require __DIR__ . '/inc/footer.php';

?>


