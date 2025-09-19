<?php
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo APP_NAME; ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
	<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body>
	<!-- Top Bar -->
	<div class="bg-primary text-white py-2">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-md-6">
					<small><i class="fas fa-map-marker-alt me-1"></i> Select Location: <strong>Delhi NCR</strong></small>
				</div>
				<div class="col-md-6 text-end">
					<small><i class="fas fa-phone me-1"></i> +91 98765 43210 | <i class="fas fa-envelope me-1"></i> support@eventmanagement.com</small>
				</div>
			</div>
		</div>
	</div>

	<!-- Main Navigation -->
	<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
		<div class="container">
			<a class="navbar-brand fw-bold fs-3 text-primary" href="<?php echo BASE_URL; ?>index.php">
				<i class="fas fa-calendar-alt me-2"></i><?php echo APP_NAME; ?>
			</a>
			
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a class="nav-link fw-semibold" href="<?php echo BASE_URL; ?>index.php">
							<i class="fas fa-home me-1"></i>Home
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link fw-semibold" href="<?php echo BASE_URL; ?>pages/events.php">
							<i class="fas fa-calendar me-1"></i>Events
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link fw-semibold" href="<?php echo BASE_URL; ?>pages/about.php">
							<i class="fas fa-info-circle me-1"></i>About
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link fw-semibold" href="<?php echo BASE_URL; ?>pages/contact.php">
							<i class="fas fa-phone me-1"></i>Contact
						</a>
					</li>
				</ul>
				
				<div class="d-flex align-items-center">
					<?php if (isLoggedIn()): ?>
						<div class="dropdown me-3">
							<button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
								<i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES); ?>
							</button>
							<ul class="dropdown-menu">
								<li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
								<?php if (isAdmin()): ?>
									<li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/index.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
								<?php endif; ?>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
							</ul>
						</div>
					<?php else: ?>
						<a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-outline-primary me-2">
							<i class="fas fa-sign-in-alt me-1"></i>Login
						</a>
						<a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary">
							<i class="fas fa-user-plus me-1"></i>Sign Up
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>
	<main class="py-4">
		<div class="container">
