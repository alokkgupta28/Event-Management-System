<?php 
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/partials/header.php';

// Fetch featured events (limit to 6 most recent events)
$stmt = $pdo->query('SELECT id, title, description, date, location, image, category, amount FROM events ORDER BY created_at DESC LIMIT 6');
$featuredEvents = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero" data-aos="fade-up" style="background: linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.45)), url('https://images.unsplash.com/photo-1582711012124-a56cf82307a0?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') center/cover no-repeat;">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6">
				<h1>Discover Amazing Events Near You</h1>
				<p class="lead">From workshops to conferences, find and book the best events in your city</p>
			</div>
			<div class="col-lg-6">
				<form class="search-bar d-flex" action="pages/events.php" method="get">
					<input name="q" type="search" placeholder="Search for events, workshops, conferences...">
					<button type="submit">
						<i class="fas fa-search me-2"></i>Search
					</button>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Category Tabs -->
<div class="container">
	<div class="category-tabs" data-aos="fade-up" data-aos-delay="200">
		<a href="pages/events.php" class="category-tab active">
			<i class="fas fa-calendar me-2"></i>All Events
		</a>
		<a href="pages/events.php?category=Conference" class="category-tab">
			<i class="fas fa-users me-2"></i>Conferences
		</a>
		<a href="pages/events.php?category=Workshop" class="category-tab">
			<i class="fas fa-tools me-2"></i>Workshops
		</a>
		<a href="pages/events.php?category=Meetup" class="category-tab">
			<i class="fas fa-handshake me-2"></i>Meetups
		</a>
		<a href="pages/events.php?category=Retreat" class="category-tab">
			<i class="fas fa-mountain me-2"></i>Retreats
		</a>
	</div>
</div>

<!-- Featured Events Section -->
<section class="py-5">
	<div class="container">
		<div class="section-header" data-aos="fade-up">
			<h2>Featured Events</h2>
			<p>Handpicked events happening this week in your city</p>
		</div>
		
		<div class="row g-4">
			<?php if (empty($featuredEvents)): ?>
				<div class="col-12">
					<div class="card" data-aos="fade-up">
						<div class="card-body text-center py-5">
							<i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
							<h5 class="text-muted">No events available</h5>
							<p class="text-muted">Check back later for upcoming events.</p>
						</div>
					</div>
				</div>
			<?php else: ?>
				<?php foreach ($featuredEvents as $index => $event): ?>
				<div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
					<div class="card event-card">
						<?php if (!empty($event['image'])): ?>
							<img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($event['image'], ENT_QUOTES); ?>" 
								 class="card-img-top" alt="Event image">
						<?php else: ?>
							<img src="https://picsum.photos/seed/event<?php echo $event['id']; ?>/600/400" 
								 class="card-img-top" alt="Event image">
						<?php endif; ?>
						<div class="card-body">
							<div class="d-flex justify-content-between align-items-start mb-2">
								<?php if (!empty($event['category'])): ?>
									<span class="badge bg-primary"><?php echo htmlspecialchars($event['category'], ENT_QUOTES); ?></span>
								<?php endif; ?>
								<span class="text-muted small">
									<?php if (!empty($event['amount']) && $event['amount'] > 0): ?>
										₹<?php echo number_format($event['amount'], 2); ?> onwards
									<?php else: ?>
										Free Event
									<?php endif; ?>
								</span>
							</div>
							<h5 class="card-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?></h5>
							<p class="card-text"><?php echo substr(htmlspecialchars($event['description'], ENT_QUOTES), 0, 100); ?><?php if (strlen($event['description']) > 100): ?>...<?php endif; ?></p>
							<div class="d-flex align-items-center mb-3">
								<i class="fas fa-calendar text-primary me-2"></i>
								<small class="text-muted"><?php echo date('M j, Y', strtotime($event['date'])); ?></small>
								<i class="fas fa-map-marker-alt text-primary ms-3 me-2"></i>
								<small class="text-muted"><?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?></small>
							</div>
							<a href="pages/event.php?id=<?php echo (int)$event['id']; ?>" class="btn btn-primary w-100">
								<i class="fas fa-ticket-alt me-2"></i>View Details
							</a>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<div class="text-center mt-5" data-aos="fade-up">
			<a href="pages/events.php" class="btn btn-outline-primary btn-lg">
				<i class="fas fa-calendar me-2"></i>View All Events
			</a>
		</div>
	</div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-white">
	<div class="container">
		<div class="row text-center" data-aos="fade-up">
			<div class="col-md-3 mb-4">
				<div class="h2 text-primary fw-bold">500+</div>
				<p class="text-muted">Events Hosted</p>
			</div>
			<div class="col-md-3 mb-4">
				<div class="h2 text-primary fw-bold">10K+</div>
				<p class="text-muted">Happy Attendees</p>
			</div>
			<div class="col-md-3 mb-4">
				<div class="h2 text-primary fw-bold">50+</div>
				<p class="text-muted">Cities Covered</p>
			</div>
			<div class="col-md-3 mb-4">
				<div class="h2 text-primary fw-bold">4.9★</div>
				<p class="text-muted">Average Rating</p>
			</div>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
