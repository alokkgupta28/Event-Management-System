<?php
require_once __DIR__ . '/config.php';

try {
	$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
	http_response_code(500);
	die('Database connection failed.');
}

// Seed a default admin if none exists to ensure admin login works
try {
	$count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
	if ($count === 0) {
		$defaultPasswordHash = password_hash('admin123', PASSWORD_BCRYPT);
		$stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "admin")');
		$stmt->execute(['Administrator', 'admin@example.com', $defaultPasswordHash]);
	}
} catch (Throwable $se) {
	// ignore seeding errors silently
}