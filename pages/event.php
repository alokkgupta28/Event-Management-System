<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../partials/header.php';

$eventId = (int)($_GET['id'] ?? 0);
if ($eventId <= 0) {
	echo '<div class="alert alert-danger">Invalid event.</div>';
	require_once __DIR__ . '/../partials/footer.php';
	exit;
}

$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) {
	echo '<div class="alert alert-warning">Event not found.</div>';
	require_once __DIR__ . '/../partials/footer.php';
	exit;
}
?>
<div class="row" data-aos="fade-up">
	<div class="col-lg-8">
		<div class="card mb-4">
			<?php if (!empty($event['image'])): ?>
				<img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($event['image'], ENT_QUOTES); ?>" 
					 class="card-img-top" alt="Event image" style="height:400px;object-fit:cover;">
			<?php else: ?>
				<img src="https://picsum.photos/seed/event<?php echo $event['id']; ?>/1000/400" 
					 class="card-img-top" alt="Event image" style="height:400px;object-fit:cover;">
			<?php endif; ?>
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-start mb-3">
					<div>
						<h1 class="card-title mb-2"><?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?></h1>
						<?php if (!empty($event['category'])): ?>
							<span class="badge bg-primary fs-6 mb-2"><?php echo htmlspecialchars($event['category'], ENT_QUOTES); ?></span>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="row mb-4">
					<div class="col-md-4">
						<div class="d-flex align-items-center mb-3">
							<i class="fas fa-calendar-alt text-primary me-3 fs-5"></i>
							<div>
								<div class="fw-bold">Event Date</div>
								<div class="text-muted"><?php echo date('l, F j, Y', strtotime($event['date'])); ?></div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="d-flex align-items-center mb-3">
							<i class="fas fa-map-marker-alt text-primary me-3 fs-5"></i>
							<div>
								<div class="fw-bold">Location</div>
								<div class="text-muted"><?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?></div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="d-flex align-items-center mb-3">
							<i class="fas fa-rupee-sign text-primary me-3 fs-5"></i>
							<div>
								<div class="fw-bold">Price</div>
								<div class="text-muted">
									<?php if (!empty($event['amount']) && $event['amount'] > 0): ?>
										₹<?php echo number_format($event['amount'], 2); ?>
									<?php else: ?>
										Free Event
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="mb-4">
					<h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>About This Event</h5>
					<div class="text-muted" style="line-height: 1.8;">
						<?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-lg-4">
		<div class="card sticky-top" style="top: 2rem;" data-aos="fade-up" data-aos-delay="200">
			<div class="card-header bg-primary text-white">
				<h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Register for Event</h5>
			</div>
			<div class="card-body">
				<?php if (!isLoggedIn()): ?>
					<div class="text-center py-4">
						<i class="fas fa-lock fa-3x text-muted mb-3"></i>
						<p class="mb-3">Please login to register for this event.</p>
						<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary">
							<i class="fas fa-sign-in-alt me-1"></i>Login
						</a>
					</div>
				<?php else: ?>
					<div class="text-center">
						<a href="<?php echo BASE_URL; ?>pages/booking.php?event_id=<?php echo (int)$event['id']; ?>" 
						   class="btn btn-primary w-100 btn-lg">
							<i class="fas fa-ticket-alt me-2"></i>Book Tickets
						</a>
						<p class="text-muted mt-3 small">
							<i class="fas fa-shield-alt me-1"></i>
							Secure booking with instant confirmation
						</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
