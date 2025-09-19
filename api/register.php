<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
	echo json_encode(['success' => false, 'message' => 'Please login to register.']);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Invalid request.']);
	exit;
}

try {
	verify_csrf();
} catch (Throwable $e) {
	echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
	exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$tickets = max(1, (int)($_POST['tickets'] ?? 1));
$userId = (int)($_SESSION['user']['id'] ?? 0);

if ($eventId <= 0) {
	echo json_encode(['success' => false, 'message' => 'Invalid event.']);
	exit;
}

// Ensure event exists
$stmt = $pdo->prepare('SELECT id FROM events WHERE id = ?');
$stmt->execute([$eventId]);
if (!$stmt->fetch()) {
	echo json_encode(['success' => false, 'message' => 'Event not found.']);
	exit;
}

try {
	$stmt = $pdo->prepare('INSERT INTO registrations (event_id, user_id, tickets) VALUES (?, ?, ?)');
	$stmt->execute([$eventId, $userId, $tickets]);
	echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} catch (PDOException $e) {
	if ((int)$e->getCode() === 23000) {
		echo json_encode(['success' => false, 'message' => 'You are already registered for this event.']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to register.']);
	}
}
