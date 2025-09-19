<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../partials/header.php';

// Get filter parameters
$search = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$where = [];
$params = [];

if ($search) {
	$where[] = '(title LIKE ? OR description LIKE ? OR location LIKE ?)';
	$searchTerm = "%$search%";
	$params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($category) {
	$where[] = 'category = ?';
	$params[] = $category;
}

$sql = 'SELECT id, title, date, location, image, category, description, amount FROM events';
if ($where) {
	$sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get unique categories for filter
$categories = $pdo->query('SELECT DISTINCT category FROM events WHERE category IS NOT NULL AND category != "" ORDER BY category')->fetchAll();
?>
<div class="section-header" data-aos="fade-up">
	<h2><i class="fas fa-calendar-alt me-2"></i>All Events</h2>
	<p>Discover amazing events happening in your city</p>
</div>

<!-- Search and Filter Section -->
<div class="container mb-5">
	<div class="search-bar d-flex mb-4" data-aos="fade-up" data-aos-delay="100">
		<form method="GET" class="d-flex w-100">
			<input type="text" name="q" placeholder="Search by title, description, or location..." value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>" class="flex-grow-1">
			<select name="category" class="me-3" style="border: none; background: transparent; color: var(--secondary-color);">
				<option value="">All Categories</option>
				<?php foreach ($categories as $cat): ?>
					<option value="<?php echo htmlspecialchars($cat['category'], ENT_QUOTES); ?>" 
							<?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
						<?php echo htmlspecialchars($cat['category'], ENT_QUOTES); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="submit">
				<i class="fas fa-search me-2"></i>Search
			</button>
		</form>
	</div>
	
	<?php if ($search || $category): ?>
		<div class="text-center mb-4" data-aos="fade-up">
			<span class="badge bg-primary me-2"><?php echo count($events); ?> events found</span>
			<a href="<?php echo BASE_URL; ?>pages/events.php" class="btn btn-outline-secondary btn-sm">
				<i class="fas fa-times me-1"></i>Clear Filters
			</a>
		</div>
	<?php endif; ?>
</div>

<!-- Events Grid -->
<?php if (!$events): ?>
	<div class="card" data-aos="fade-up">
		<div class="card-body text-center py-5">
			<i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
			<h5 class="text-muted">No events found</h5>
			<p class="text-muted">
				<?php if ($search || $category): ?>
					Try adjusting your search criteria or filters.
				<?php else: ?>
					Check back later for upcoming events.
				<?php endif; ?>
			</p>
		</div>
	</div>
<?php else: ?>
	<div class="container">
		<div class="row g-4" data-aos="fade-up" data-aos-delay="200">
			<?php foreach ($events as $index => $ev): ?>
			<div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
				<div class="card event-card">
					<?php if (!empty($ev['image'])): ?>
						<img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($ev['image'], ENT_QUOTES); ?>" 
							 class="card-img-top" alt="Event image">
					<?php else: ?>
						<img src="https://picsum.photos/seed/event<?php echo $ev['id']; ?>/600/400" 
							 class="card-img-top" alt="Event image">
					<?php endif; ?>
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-start mb-2">
							<?php if (!empty($ev['category'])): ?>
								<span class="badge bg-primary"><?php echo htmlspecialchars($ev['category'], ENT_QUOTES); ?></span>
							<?php endif; ?>
							<span class="text-muted small">
								<?php if (!empty($ev['amount']) && $ev['amount'] > 0): ?>
									₹<?php echo number_format($ev['amount'], 2); ?> onwards
								<?php else: ?>
									Free Event
								<?php endif; ?>
							</span>
						</div>
						<h5 class="card-title"><?php echo htmlspecialchars($ev['title'], ENT_QUOTES); ?></h5>
						<p class="card-text"><?php echo substr(htmlspecialchars($ev['description'], ENT_QUOTES), 0, 100); ?><?php if (strlen($ev['description']) > 100): ?>...<?php endif; ?></p>
						<div class="d-flex align-items-center mb-3">
							<i class="fas fa-calendar text-primary me-2"></i>
							<small class="text-muted"><?php echo date('M j, Y', strtotime($ev['date'])); ?></small>
							<i class="fas fa-map-marker-alt text-primary ms-3 me-2"></i>
							<small class="text-muted"><?php echo htmlspecialchars($ev['location'], ENT_QUOTES); ?></small>
						</div>
						<a href="<?php echo BASE_URL; ?>pages/event.php?id=<?php echo (int)$ev['id']; ?>" 
						   class="btn btn-primary w-100">
							<i class="fas fa-ticket-alt me-2"></i>View Details
						</a>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
