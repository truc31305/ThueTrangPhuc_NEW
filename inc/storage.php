<?php

function readJsonFile(string $path) {
	if (!file_exists($path)) return [];
	$json = file_get_contents($path);
	$data = json_decode($json, true);
	return is_array($data) ? $data : [];
}

function writeJsonFile(string $path, array $data) {
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

?>


