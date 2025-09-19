<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../partials/header.php';

$success = '';
$errors = [];
$name = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$message = trim($_POST['message'] ?? '');
	if ($name === '') $errors['name'] = 'Name is required';
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required';
	if ($message === '') $errors['message'] = 'Message is required';
	if (!$errors) {
		$success = 'Thanks for reaching out. We will get back to you soon!';
		$name = $email = $message = '';
	}
}
?>

<div class="section-header" data-aos="fade-up">
	<h2>Contact Us</h2>
	<p>We'd love to hear from you. Send a message and we'll respond shortly.</p>
</div>

<?php if ($success): ?>
	<div class="alert alert-success" data-aos="fade-up"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></div>
<?php endif; ?>

<div class="row g-4" data-aos="fade-up" data-aos-delay="100">
	<div class="col-lg-5">
		<div class="card h-100">
			<div class="card-body p-4">
				<h5 class="mb-3"><i class="fas fa-headset me-2 text-primary"></i>Support</h5>
				<p class="text-muted mb-4">Our team is available Mon–Fri, 10:00 AM – 6:00 PM IST.</p>
				<div class="d-flex align-items-start mb-3">
					<i class="fas fa-envelope text-primary me-3 mt-1"></i>
					<div>
						<div class="fw-semibold">Email</div>
						<a href="mailto:support@eventmanagement.com" class="text-decoration-none">support@eventmanagement.com</a>
					</div>
				</div>
				<div class="d-flex align-items-start mb-3">
					<i class="fas fa-phone text-primary me-3 mt-1"></i>
					<div>
						<div class="fw-semibold">Phone</div>
						<span>+91 98765 43210</span>
					</div>
				</div>
				<div class="d-flex align-items-start">
					<i class="fas fa-map-marker-alt text-primary me-3 mt-1"></i>
					<div>
						<div class="fw-semibold">Address</div>
						<span>Knowledge Park III,Greater Noida, Uttar Pradesh, India</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-7">
		<div class="card h-100">
			<div class="card-body p-4">
				<h5 class="mb-3"><i class="fas fa-paper-plane me-2 text-primary"></i>Send a message</h5>
				<form method="post" class="needs-validation" novalidate>
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
					<div class="mb-3">
						<label class="form-label">Your Name</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-user"></i></span>
							<input type="text" name="name" class="form-control<?php echo isset($errors['name']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>" required>
							<div class="invalid-feedback"><?php echo $errors['name'] ?? 'Please enter your name.'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-envelope"></i></span>
							<input type="email" name="email" class="form-control<?php echo isset($errors['email']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>" required>
							<div class="invalid-feedback"><?php echo $errors['email'] ?? 'Please enter a valid email.'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Message</label>
						<textarea name="message" rows="5" class="form-control<?php echo isset($errors['message']) ? ' is-invalid' : ''; ?>" required><?php echo htmlspecialchars($message, ENT_QUOTES); ?></textarea>
						<div class="invalid-feedback"><?php echo $errors['message'] ?? 'Please enter a message.'; ?></div>
					</div>
					<button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="mt-4" data-aos="fade-up" data-aos-delay="150">
	<div class="card">
		<div class="card-body p-0">
			<div style="width: 100%; height: 300px; background: url('https://images.unsplash.com/photo-1582711012124-a56cf82307a0?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') center/cover no-repeat;"></div>
		</div>
	</div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
