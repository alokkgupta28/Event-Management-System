		</div>
	</main>
	<footer class="bg-light border-top mt-5">
		<div class="container py-5">
			<div class="row">
				<div class="col-md-4 mb-4">
					<h5 class="mb-3 text-dark"><?php echo APP_NAME; ?></h5>
					<p class="text-muted">Discover, book, and manage events with ease. From workshops to conferences, we bring the best events to you.</p>
				</div>
				<div class="col-md-2 mb-4">
					<h6 class="text-uppercase text-muted">Explore</h6>
					<ul class="list-unstyled">
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>pages/events.php">All Events</a></li>
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>pages/about.php">About Us</a></li>
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>pages/contact.php">Contact</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-4">
					<h6 class="text-uppercase text-muted">For Organizers</h6>
					<ul class="list-unstyled">
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>admin/events.php">Create Event</a></li>
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>admin/registrations.php">Manage Registrations</a></li>
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>admin/users.php">Manage Users</a></li>
					</ul>
				</div>
				<div class="col-md-3 mb-4">
					<h6 class="text-uppercase text-muted">Account</h6>
					<ul class="list-unstyled">
						<?php if (!isLoggedIn()): ?>
							<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
							<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>auth/register.php">Sign Up</a></li>
						<?php else: ?>
							<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>pages/dashboard.php">Dashboard</a></li>
							<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
						<?php endif; ?>
						<li><a class="text-decoration-none" href="<?php echo BASE_URL; ?>auth/forgot.php">Forgot Password</a></li>
					</ul>
				</div>
			</div>
			<div class="border-top pt-3 d-flex justify-content-between align-items-center">
				<small class="text-muted">© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</small>
				<div class="d-flex gap-3">
					<a href="#" class="text-muted"><i class="fab fa-facebook"></i></a>
					<a href="#" class="text-muted"><i class="fab fa-twitter"></i></a>
					<a href="#" class="text-muted"><i class="fab fa-instagram"></i></a>
				</div>
			</div>
		</div>
	</footer>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
	<script>if (window.AOS) { AOS.init({ once: true, duration: 600, easing: 'ease-out' }); }</script>
	<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>
