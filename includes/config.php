<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// MySQL credentials (update as needed)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management');

// App settings
define('APP_NAME', 'Event Management');
define('BASE_URL', '/Event Management/');

function isLoggedIn(): bool {
	return isset($_SESSION['user']);
}

function isAdmin(): bool {
	return isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
}

function requireLogin(): void {
	if (!isLoggedIn()) {
		header('Location: ' . BASE_URL . 'auth/login.php');
		exit;
	}
}

function csrf_token(): string {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$token = $_POST['csrf_token'] ?? '';
		if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
			http_response_code(400);
			exit('Invalid CSRF token');
		}
	}
}
