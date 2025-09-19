<?php
require_once __DIR__ . '/../includes/db.php';

$token = $_GET['token'] ?? '';
$errors = [];
$message = '';
$isValidToken = false;

// Validate token format
if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $errors['token'] = 'Invalid token format.';
} else {
    // Check if token exists and is valid
    $stmt = $pdo->prepare('SELECT pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    
    if (!$row) {
        $errors['token'] = 'Invalid or expired token.';
    } elseif (strtotime($row['expires_at']) < time()) {
        // Clean up expired token
        $pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
        $errors['token'] = 'Token has expired. Please request a new password reset.';
    } else {
        $isValidToken = true;
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValidToken) {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (strlen($password) < 6) { 
        $errors['password'] = 'Password must be at least 6 characters.'; 
    }
    if ($password !== $confirm) { 
        $errors['confirm_password'] = 'Passwords do not match.'; 
    }
    
    // Update password if no errors
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, (int)$row['user_id']]);
        $pdo->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
        
        $_SESSION['flash_success'] = 'Password has been reset successfully. Please login with your new password.';
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

require_once __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-6">
		<div class="card" data-aos="fade-up">
			<div class="card-header text-center">
				<h2 class="mb-0"><i class="fas fa-key me-2"></i>Reset Password</h2>
			</div>
			<div class="card-body">
				<?php if (!$isValidToken): ?>
					<!-- Invalid Token Error -->
					<div class="text-center py-4">
						<i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
						<h5 class="text-danger">Invalid or Expired Token</h5>
						<p class="text-muted"><?php echo htmlspecialchars($errors['token'] ?? 'The password reset link is invalid or has expired.', ENT_QUOTES); ?></p>
						<div class="d-flex justify-content-center gap-3">
							<a href="<?php echo BASE_URL; ?>auth/forgot.php" class="btn btn-primary">
								<i class="fas fa-redo me-2"></i>Request New Reset Link
							</a>
							<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-secondary">
								<i class="fas fa-sign-in-alt me-2"></i>Back to Login
							</a>
						</div>
					</div>
				<?php else: ?>
					<!-- Valid Token - Show Reset Form -->
					<div class="text-center mb-4">
						<i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
						<p class="text-muted">Reset password for: <strong><?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?></strong></p>
					</div>
					
					<form method="post" class="needs-validation" novalidate>
						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
						
						<div class="mb-3">
							<label class="form-label">New Password <span class="text-danger">*</span></label>
							<input type="password" name="password" minlength="6" class="form-control<?php echo isset($errors['password']) ? ' is-invalid' : ''; ?>" required>
							<div class="invalid-feedback"><?php echo $errors['password'] ?? 'Minimum 6 characters required.'; ?></div>
							<div class="form-text">Password must be at least 6 characters long.</div>
						</div>
						
						<div class="mb-4">
							<label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
							<input type="password" name="confirm_password" class="form-control<?php echo isset($errors['confirm_password']) ? ' is-invalid' : ''; ?>" required>
							<div class="invalid-feedback"><?php echo $errors['confirm_password'] ?? 'Passwords must match.'; ?></div>
						</div>
						
						<div class="d-grid gap-2">
							<button class="btn btn-primary btn-lg" type="submit">
								<i class="fas fa-save me-2"></i>Update Password
							</button>
							<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-secondary">
								<i class="fas fa-arrow-left me-2"></i>Back to Login
							</a>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>


