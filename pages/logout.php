<?php
require_once __DIR__ . '/../inc/auth.php';

logoutUser();

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '?page=home';
header('Location: ' . $redirect);
exit;
