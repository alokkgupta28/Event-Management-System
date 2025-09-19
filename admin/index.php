<?php
require_once __DIR__ . '/../includes/db.php';
if (!isAdmin()) {
	header('Location: ' . BASE_URL . 'auth/login.php');
	exit;
}
require_once __DIR__ . '/../partials/header.php';

$eventsCount = (int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$usersCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$regsCount = (int)$pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
?>
<h2 class="mb-4">Admin Dashboard</h2>
<div class="row g-3">
	<div class="col-md-4">
		<div class="card text-bg-primary">
			<div class="card-body">
				<h5 class="card-title mb-0">Events</h5>
				<p class="display-6 mb-0"><?php echo $eventsCount; ?></p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card text-bg-success">
			<div class="card-body">
				<h5 class="card-title mb-0">Users</h5>
				<p class="display-6 mb-0"><?php echo $usersCount; ?></p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card text-bg-warning">
			<div class="card-body">
				<h5 class="card-title mb-0">Registrations</h5>
				<p class="display-6 mb-0"><?php echo $regsCount; ?></p>
			</div>
		</div>
	</div>
</div>
<div class="mt-4">
	<a class="btn btn-outline-primary me-2" href="<?php echo BASE_URL; ?>admin/events.php">Manage Events</a>
	<a class="btn btn-outline-secondary me-2" href="<?php echo BASE_URL; ?>admin/users.php">Manage Users</a>
	<a class="btn btn-outline-warning" href="<?php echo BASE_URL; ?>admin/registrations.php">View Registrations</a>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
