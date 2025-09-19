<?php
require_once __DIR__ . '/../includes/db.php';

$message = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Remove any existing tokens for this user
                $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([(int)$user['id']]);
                
                // Store new token
                $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                $stmt->execute([(int)$user['id'], $token, $expiresAt]);
                
                // Generate reset link
                $resetLink = BASE_URL . 'auth/reset.php?token=' . $token;
                
                // For development/testing - store in session to display
                $_SESSION['debug_reset_link'] = $resetLink;
                $_SESSION['debug_user_name'] = $user['name'];
                
                $success = true;
                $message = 'Password reset link has been generated successfully!';
            } else {
                // For security, don't reveal if email exists or not
                $success = true;
                $message = 'If an account exists for this email, a password reset link has been sent.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred. Please try again later.';
            error_log('Password reset error: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/../partials/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-6">
		<div class="card" data-aos="fade-up">
			<div class="card-header text-center">
				<h2 class="mb-0"><i class="fas fa-key me-2"></i>Forgot Password</h2>
			</div>
			<div class="card-body">
				<?php if ($success): ?>
					<!-- Success Message -->
					<div class="text-center py-4">
						<i class="fas fa-check-circle fa-3x text-success mb-3"></i>
						<h5 class="text-success">Reset Link Generated!</h5>
						<p class="text-muted"><?php echo htmlspecialchars($message, ENT_QUOTES); ?></p>
						
						<?php if (!empty($_SESSION['debug_reset_link'])): ?>
							<div class="alert alert-info">
								<h6><i class="fas fa-info-circle me-2"></i>Development Mode</h6>
								<p class="mb-2">For testing purposes, here's your reset link:</p>
								<div class="input-group">
									<input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['debug_reset_link'], ENT_QUOTES); ?>" readonly id="resetLink">
									<button class="btn btn-outline-secondary" type="button" onclick="copyResetLink()">
										<i class="fas fa-copy"></i>
									</button>
								</div>
								<small class="text-muted">Click the copy button to copy the link, then open it in a new tab.</small>
							</div>
							<div class="d-flex justify-content-center gap-3">
								<a href="<?php echo $_SESSION['debug_reset_link']; ?>" class="btn btn-primary" target="_blank">
									<i class="fas fa-external-link-alt me-2"></i>Open Reset Link
								</a>
								<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-secondary">
									<i class="fas fa-arrow-left me-2"></i>Back to Login
								</a>
							</div>
							<?php unset($_SESSION['debug_reset_link'], $_SESSION['debug_user_name']); ?>
						<?php else: ?>
							<div class="d-flex justify-content-center">
								<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary">
									<i class="fas fa-arrow-left me-2"></i>Back to Login
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php else: ?>
					<!-- Forgot Password Form -->
					<div class="text-center mb-4">
						<i class="fas fa-user-lock fa-3x text-primary mb-3"></i>
						<p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
					</div>
					
					<?php if (!empty($errors['general'])): ?>
						<div class="alert alert-danger">
							<i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($errors['general'], ENT_QUOTES); ?>
						</div>
					<?php endif; ?>
					
					<form method="post" class="needs-validation" novalidate id="forgotForm">
						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
						
						<div class="mb-4">
							<label class="form-label">Email Address <span class="text-danger">*</span></label>
							<div class="input-group">
								<span class="input-group-text"><i class="fas fa-envelope"></i></span>
								<input type="email" name="email" class="form-control<?php echo isset($errors['email']) ? ' is-invalid' : ''; ?>" 
									   placeholder="Enter your email address" required>
								<div class="invalid-feedback"><?php echo $errors['email'] ?? 'Please provide a valid email address.'; ?></div>
							</div>
						</div>
						
						<div class="d-grid gap-2">
							<button class="btn btn-primary btn-lg" type="submit" id="submitBtn">
								<i class="fas fa-paper-plane me-2"></i>Send Reset Link
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

<script>
function copyResetLink() {
	const resetLink = document.getElementById('resetLink');
	resetLink.select();
	resetLink.setSelectionRange(0, 99999); // For mobile devices
	document.execCommand('copy');
	
	// Show feedback
	const button = event.target.closest('button');
	const originalHTML = button.innerHTML;
	button.innerHTML = '<i class="fas fa-check"></i>';
	button.classList.add('btn-success');
	button.classList.remove('btn-outline-secondary');
	
	setTimeout(() => {
		button.innerHTML = originalHTML;
		button.classList.remove('btn-success');
		button.classList.add('btn-outline-secondary');
	}, 2000);
}

// Form validation
(function() {
	'use strict';
	window.addEventListener('load', function() {
		const forms = document.getElementsByClassName('needs-validation');
		Array.prototype.filter.call(forms, function(form) {
			form.addEventListener('submit', function(event) {
				if (form.checkValidity() === false) {
					event.preventDefault();
					event.stopPropagation();
				} else {
					// Show loading state
					const submitBtn = document.getElementById('submitBtn');
					if (submitBtn) {
						submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
						submitBtn.disabled = true;
					}
				}
				form.classList.add('was-validated');
			}, false);
		});
	}, false);
})();
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>


