<?php
require_once __DIR__ . '/../includes/db.php';

$errors = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$old['email'] = $email;

	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
	if ($password === '') { $errors['password'] = 'Password is required.'; }

	if (!$errors) {
		$stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
		$stmt->execute([$email]);
		$user = $stmt->fetch();
		if (!$user || !password_verify($password, $user['password'])) {
			$errors['general'] = 'Invalid email or password.';
		} else {
			$_SESSION['user'] = [
				'id' => (int)$user['id'],
				'name' => $user['name'],
				'email' => $user['email'],
				'role' => $user['role'],
			];
			if ($user['role'] === 'admin') {
				header('Location: ' . BASE_URL . 'admin/index.php');
				exit;
			}
			header('Location: ' . BASE_URL . 'pages/dashboard.php');
			exit;
		}
	}
}
require_once __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
	<div class="col-lg-5">
		<div class="card shadow-sm" data-aos="fade-up">
			<div class="card-body p-4 p-md-5">
				<div class="text-center mb-4">
					<i class="fas fa-user-circle fa-3x text-primary mb-2"></i>
					<h2 class="mb-1">Welcome Back</h2>
					<p class="text-muted">Login to manage your account and bookings</p>
				</div>
				<?php if (!empty($errors['general'])): ?>
					<div class="alert alert-danger"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES); ?></div>
				<?php endif; ?>
				
				<?php if (!empty($_SESSION['flash_success'])): ?>
					<div class="alert alert-success alert-dismissible fade show">
						<i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
					</div>
					<?php unset($_SESSION['flash_success']); ?>
				<?php endif; ?>
				<form method="post" class="needs-validation" novalidate>
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
					<div class="mb-3">
						<label class="form-label">Email</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-envelope"></i></span>
							<input type="email" name="email" class="form-control<?php echo isset($errors['email']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($old['email'] ?? '', ENT_QUOTES); ?>" required>
							<div class="invalid-feedback"><?php echo $errors['email'] ?? 'Please provide your email.'; ?></div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Password</label>
						<div class="input-group">
							<span class="input-group-text"><i class="fas fa-lock"></i></span>
							<input type="password" name="password" class="form-control<?php echo isset($errors['password']) ? ' is-invalid' : ''; ?>" required>
							<div class="invalid-feedback"><?php echo $errors['password'] ?? 'Please provide your password.'; ?></div>
						</div>
					</div>
					<div class="d-flex justify-content-between align-items-center mb-3">
						<a class="small" href="<?php echo BASE_URL; ?>auth/forgot.php">Forgot password?</a>
					</div>
					<button class="btn btn-primary w-100" type="submit"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
					<div class="text-center mt-3">
						<span class="text-muted">Don't have an account?</span>
						<a class="ms-1" href="<?php echo BASE_URL; ?>auth/register.php">Create account</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
