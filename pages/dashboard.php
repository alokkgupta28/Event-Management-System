<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
require_once __DIR__ . '/../partials/header.php';

$userId = (int)($_SESSION['user']['id'] ?? 0);
$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
	verify_csrf();
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$currentPassword = $_POST['current_password'] ?? '';
	$newPassword = $_POST['new_password'] ?? '';
	$confirmPassword = $_POST['confirm_password'] ?? '';

	// Validation
	if ($name === '') { $errors['name'] = 'Name is required.'; }
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
	
	// Check if email is already taken by another user
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
	$stmt->execute([$email, $userId]);
	if ($stmt->fetch()) { $errors['email'] = 'Email is already taken.'; }

	// Password validation if provided
	if ($newPassword !== '') {
		if (strlen($newPassword) < 6) { $errors['new_password'] = 'New password must be at least 6 characters.'; }
		if ($newPassword !== $confirmPassword) { $errors['confirm_password'] = 'Passwords do not match.'; }
		if ($currentPassword === '') { $errors['current_password'] = 'Current password is required to change password.'; }
	}

	// Verify current password if changing password
	if ($newPassword !== '' && !$errors) {
		$stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
		$stmt->execute([$userId]);
		$user = $stmt->fetch();
		if (!$user || !password_verify($currentPassword, $user['password'])) {
			$errors['current_password'] = 'Current password is incorrect.';
		}
	}

	// Update profile if no errors
	if (!$errors) {
		if ($newPassword !== '') {
			$stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
			$stmt->execute([$name, $email, password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
		} else {
			$stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
			$stmt->execute([$name, $email, $userId]);
		}
		
		// Update session data
		$_SESSION['user']['name'] = $name;
		$_SESSION['user']['email'] = $email;
		$success = 'Profile updated successfully!';
	}
}

// Get user data
$stmt = $pdo->prepare('SELECT name, email, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get bookings
$stmt = $pdo->prepare('SELECT b.id, b.booking_reference, b.ticket_quantity, b.total_amount, b.booking_status, b.booking_date, e.title, e.date, e.location, e.image
	FROM bookings b
	JOIN events e ON e.id = b.event_id
	WHERE b.user_id = ?
	ORDER BY e.date ASC');
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>
<div class="row">
	<div class="col-lg-4 mb-4">
		<!-- Profile Card -->
		<div class="card" data-aos="fade-up">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile</h5>
				<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#profileModal">
					<i class="fas fa-edit me-1"></i>Edit
				</button>
			</div>
			<div class="card-body text-center">
				<div class="mb-3">
					<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
						<i class="fas fa-user fa-2x text-white"></i>
					</div>
				</div>
				<h5 class="card-title"><?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?></h5>
				<p class="text-muted mb-2"><?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?></p>
				<small class="text-muted">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></small>
			</div>
		</div>

		<!-- Quick Stats -->
		<div class="card" data-aos="fade-up" data-aos-delay="100">
			<div class="card-header">
				<h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
			</div>
			<div class="card-body">
				<div class="row text-center">
					<div class="col-6">
						<div class="h4 text-primary mb-1"><?php echo count($bookings); ?></div>
						<small class="text-muted">Bookings</small>
					</div>
					<div class="col-6">
						<div class="h4 text-success mb-1"><?php echo array_sum(array_column($bookings, 'ticket_quantity')); ?></div>
						<small class="text-muted">Tickets</small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-8">
		<!-- Welcome Message -->
		<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
			<div>
				<h2 class="mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES); ?>!</h2>
				<p class="text-muted mb-0">Here's what's happening with your events</p>
			</div>
		</div>

		<!-- Success/Error Messages -->
		<?php if ($success): ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success, ENT_QUOTES); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		<?php endif; ?>

		<?php if ($errors): ?>
			<div class="alert alert-danger">
				<i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
				<ul class="mb-0 mt-2">
					<?php foreach ($errors as $error): ?>
						<li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<!-- Bookings Card -->
		<div class="card" data-aos="fade-up" data-aos-delay="200">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Your Event Bookings</h5>
				<a href="<?php echo BASE_URL; ?>pages/events.php" class="btn btn-sm btn-outline-primary">
					<i class="fas fa-plus me-1"></i>Browse Events
				</a>
			</div>
			<div class="card-body">
				<?php if (!$bookings): ?>
					<div class="text-center py-4">
						<i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
						<h5 class="text-muted">No bookings yet</h5>
						<p class="text-muted">Start exploring amazing events in your area!</p>
						<a href="<?php echo BASE_URL; ?>pages/events.php" class="btn btn-primary">
							<i class="fas fa-search me-2"></i>Browse Events
						</a>
					</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Event</th>
									<th>Date</th>
									<th>Location</th>
									<th>Tickets</th>
									<th>Amount</th>
									<th>Status</th>
									<th>Booked At</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($bookings as $booking): ?>
								<tr>
									<td>
										<div class="fw-bold"><?php echo htmlspecialchars($booking['title'], ENT_QUOTES); ?></div>
										<small class="text-muted">Ref: <?php echo htmlspecialchars($booking['booking_reference'], ENT_QUOTES); ?></small>
									</td>
									<td>
										<span class="badge bg-info"><?php echo date('M j, Y', strtotime($booking['date'])); ?></span>
									</td>
									<td><?php echo htmlspecialchars($booking['location'], ENT_QUOTES); ?></td>
									<td>
										<span class="badge bg-primary"><?php echo (int)$booking['ticket_quantity']; ?></span>
									</td>
									<td>
										<strong>₹<?php echo number_format($booking['total_amount'], 2); ?></strong>
									</td>
									<td>
										<span class="badge bg-<?php echo $booking['booking_status'] === 'confirmed' ? 'success' : ($booking['booking_status'] === 'pending' ? 'warning' : 'danger'); ?>">
											<?php echo ucfirst($booking['booking_status']); ?>
										</span>
									</td>
									<td>
										<small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($booking['booking_date'])); ?></small>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<!-- Profile Update Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="profileModalLabel">Update Profile</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form method="post" class="needs-validation" novalidate>
				<div class="modal-body">
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
					<input type="hidden" name="action" value="update_profile">
					
					<div class="mb-3">
						<label class="form-label">Full Name <span class="text-danger">*</span></label>
						<input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>" required>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Email Address <span class="text-danger">*</span></label>
						<input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>" required>
					</div>
					
					<hr class="my-4">
					<h6 class="mb-3">Change Password (Optional)</h6>
					
					<div class="mb-3">
						<label class="form-label">Current Password</label>
						<input type="password" name="current_password" class="form-control">
						<div class="form-text">Required only if you want to change your password</div>
					</div>
					
					<div class="mb-3">
						<label class="form-label">New Password</label>
						<input type="password" name="new_password" class="form-control" minlength="6">
						<div class="form-text">Leave empty to keep current password</div>
					</div>
					
					<div class="mb-3">
						<label class="form-label">Confirm New Password</label>
						<input type="password" name="confirm_password" class="form-control" minlength="6">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Update Profile</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
