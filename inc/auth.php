<?php

// Simple JSON-based user storage for demo. In production use a database.
const USERS_FILE = __DIR__ . '/../data/users.json';
const ADMIN_FLAG_KEY = 'isAdmin';

function loadUsers() {
	if (!file_exists(USERS_FILE)) {
		return [];
	}
	$json = file_get_contents(USERS_FILE);
	$data = json_decode($json, true);
	return is_array($data) ? $data : [];
}

function saveUsers(array $users) {
	$dir = dirname(USERS_FILE);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function findUserByEmail(string $email) {
	$users = loadUsers();
	foreach ($users as $u) {
		if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
			return $u;
		}
	}
	return null;
}

function createUser(string $name, ?string $email, ?string $phone, ?string $gender, ?string $dob, string $password) {
	$users = loadUsers();
    if ($email && findUserByEmail($email)) {
		return [false, 'Email đã tồn tại'];
	}
	$users[] = [
		'id' => uniqid('u_', true),
		'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'gender' => $gender,
        'dob' => $dob,
		'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
		'createdAt' => date('c'),
	];
	saveUsers($users);
	return [true, null];
}

function findUserByPhone(string $phone) {
    $users = loadUsers();
    foreach ($users as $u) {
        if (!empty($u['phone']) && $u['phone'] === $phone) {
            return $u;
        }
    }
    return null;
}

function attemptLogin(string $identifier, string $password) {
    $user = strpos($identifier, '@') !== false ? findUserByEmail($identifier) : findUserByPhone($identifier);
	if (!$user) {
		return [false, 'Tài khoản không tồn tại'];
	}
    // Demo-friendly: allow plain-text match OR password_verify on hash
    $stored = $user['passwordHash'];
    $isValid = ($password === $stored) || (is_string($stored) && strlen($stored) > 20 && password_verify($password, $stored));
    if (!$isValid) {
		return [false, 'Mật khẩu không đúng'];
	}
	$_SESSION['user'] = [
		'id' => $user['id'],
		'name' => $user['name'],
		'email' => $user['email'],
        ADMIN_FLAG_KEY => !empty($user[ADMIN_FLAG_KEY])
	];
	return [true, null];
}

function isLoggedIn() {
	return isset($_SESSION['user']);
}

function currentUser() {
	return isLoggedIn() ? $_SESSION['user'] : null;
}

function isAdmin() {
    return isset($_SESSION['user']) && !empty($_SESSION['user'][ADMIN_FLAG_KEY]);
}

function logoutUser() {
	unset($_SESSION['user']);
}

?>
