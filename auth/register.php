<?php
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';

	$old['name'] = $name;
	$old['email'] = $email;

	if ($name === '') { $errors['name'] = 'Name is required.'; }
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
	if (strlen($password) < 6) { $errors['password'] = 'Password must be at least 6 characters.'; }
	if ($password !== $confirm) { $errors['confirm_password'] = 'Passwords do not match.'; }

	if (!$errors) {
		// Check for duplicate email
		$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
		$stmt->execute([$email]);
		if ($stmt->fetch()) {
			$errors['email'] = 'An account with this email already exists.';
		} else {
			$hash = password_hash($password, PASSWORD_BCRYPT);
			$stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
			$stmt->execute([$name, $email, $hash]);
			$_SESSION['user'] = [
				'id' => (int)$pdo->lastInsertId(),
				'name' => $name,
				'email' => $email,
				'role' => 'user',
			];
			header('Location: ' . BASE_URL . 'pages/dashboard.php');
			exit;
		}
	}
}
require_once __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
	<div class="col-lg-6">
		<div class="card shadow-sm" data-aos="fade-up">
			<div class="card-body p-4 p-md-5">
				<div class="text-center mb-4">
					<i class="fas fa-user-plus fa-3x text-primary mb-2"></i>
					<h2 class="mb-1">Create your account</h2>
					<p class="text-muted">Join to register and manage your event bookings</p>
				</div>
				<form method="post" class="needs-validation" novalidate>
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
					<div class="mb-3">
						<label class="form-label">Name</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-user"></i></span>
							<input type="text" name="name" class="form-control<?php echo isset($errors['name']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($old['name'] ?? '', ENT_QUOTES); ?>" required>
							<div class="invalid-feedback"><?php echo $errors['name'] ?? 'Please provide your name.'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-envelope"></i></span>
							<input type="email" name="email" class="form-control<?php echo isset($errors['email']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES); ?>" required>
							<div class="invalid-feedback"><?php echo $errors['email'] ?? 'Please provide a valid email.'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Password</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-lock"></i></span>
							<input type="password" name="password" minlength="6" class="form-control<?php echo isset($errors['password']) ? ' is-invalid' : ''; ?>" required>
							<div class="invalid-feedback"><?php echo $errors['password'] ?? 'Please provide a password (min 6 chars).'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Confirm Password</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-lock"></i></span>
							<input type="password" name="confirm_password" class="form-control<?php echo isset($errors['confirm_password']) ? ' is-invalid' : ''; ?>" required>
							<div class="invalid-feedback"><?php echo $errors['confirm_password'] ?? 'Please confirm your password.'; ?></div>
						</div>
					</div>
					<button class="btn btn-primary w-100" type="submit"><i class="fas fa-user-plus me-2"></i>Register</button>
					<div class="text-center mt-3">
						<span class="text-muted">Already have an account?</span>
						<a class="ms-1" href="<?php echo BASE_URL; ?>auth/login.php">Login</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
