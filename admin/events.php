<?php
require_once __DIR__ . '/../includes/db.php';
if (!isAdmin()) { header('Location: ' . BASE_URL . 'auth/login.php'); exit; }
require_once __DIR__ . '/../partials/header.php';

$errors = [];
$success = '';

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	$title = trim($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$date = trim($_POST['date'] ?? '');
	$location = trim($_POST['location'] ?? '');
	$category = trim($_POST['category'] ?? '');
	$amount = trim($_POST['amount'] ?? '');
	$imageName = null;

	// Optional image upload
	if (!empty($_FILES['image']['name'])) {
		$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
		$allowed = ['jpg','jpeg','png','gif','webp'];
		if (!in_array($ext, $allowed, true)) {
			$errors['image'] = 'Invalid image type.';
		} else {
			$imageName = uniqid('ev_', true) . '.' . $ext;
			$destDir = __DIR__ . '/../uploads';
			if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
			move_uploaded_file($_FILES['image']['tmp_name'], $destDir . '/' . $imageName);
		}
	}

	if ($action === 'create' && !$errors) {
		$stmt = $pdo->prepare('INSERT INTO events (title, description, date, location, image, category, amount) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([$title, $description, $date, $location, $imageName, $category ?: null, $amount ?: null]);
		$success = 'Event created.';
	}

	if ($action === 'update' && !$errors) {
		$id = (int)($_POST['id'] ?? 0);
		// Get existing image
		$curr = $pdo->prepare('SELECT image FROM events WHERE id = ?');
		$curr->execute([$id]);
		$currImg = $curr->fetchColumn();
		if (!$imageName) { $imageName = $currImg; }
		$stmt = $pdo->prepare('UPDATE events SET title=?, description=?, date=?, location=?, image=?, category=?, amount=? WHERE id=?');
		$stmt->execute([$title, $description, $date, $location, $imageName, $category ?: null, $amount ?: null, $id]);
		$success = 'Event updated.';
	}
}

if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
	verify_csrf();
	$id = (int)$_GET['id'];
	$pdo->prepare('DELETE FROM events WHERE id=?')->execute([$id]);
	$success = 'Event deleted.';
}

$events = $pdo->query('SELECT * FROM events ORDER BY date DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
	<h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Manage Events</h2>
	<button class="btn btn-primary" onclick="resetForm()" data-bs-toggle="modal" data-bs-target="#eventModal">
		<i class="fas fa-plus me-1"></i>Add Event
	</button>
</div>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger">Please fix the errors below.</div><?php endif; ?>
<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="eventModalLabel">Add / Edit Event</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
				<div class="modal-body">
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
					<input type="hidden" name="action" value="create" id="formAction">
					<input type="hidden" name="id" id="eventId">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Title <span class="text-danger">*</span></label>
							<input type="text" name="title" id="title" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Date <span class="text-danger">*</span></label>
							<input type="date" name="date" id="date" class="form-control" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Location <span class="text-danger">*</span></label>
							<input type="text" name="location" id="location" class="form-control" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Category</label>
							<input type="text" name="category" id="category" class="form-control" placeholder="e.g., Conference, Workshop">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Amount (₹)</label>
							<input type="number" name="amount" id="amount" class="form-control" placeholder="0.00" step="0.01" min="0">
							<div class="form-text">Leave empty for free events</div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Image</label>
						<input type="file" name="image" class="form-control" accept="image/*">
						<div class="form-text">Supported formats: JPG, PNG, GIF, WebP</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Description <span class="text-danger">*</span></label>
						<textarea name="description" id="description" rows="4" class="form-control" required></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">Save Event</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Events Table -->
<div class="card" data-aos="fade-up" data-aos-delay="100">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h5 class="mb-0"><i class="fas fa-list me-2"></i>All Events</h5>
		<span class="badge bg-primary"><?php echo count($events); ?> events</span>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="eventsTable" class="table table-hover">
				<thead>
					<tr>
						<th>Image</th>
						<th>Title</th>
						<th>Date</th>
						<th>Location</th>
						<th>Category</th>
						<th>Amount</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($events as $ev): ?>
					<tr data-event-id="<?php echo (int)$ev['id']; ?>">
						<td>
							<?php if (!empty($ev['image'])): ?>
								<img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($ev['image'], ENT_QUOTES); ?>" 
									 alt="Event image" class="rounded" style="width:50px;height:50px;object-fit:cover;">
							<?php else: ?>
								<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
									<i class="fas fa-image text-muted"></i>
								</div>
							<?php endif; ?>
						</td>
						<td>
							<div class="fw-bold event-title"><?php echo htmlspecialchars($ev['title'], ENT_QUOTES); ?></div>
							<small class="text-muted"><?php echo substr(htmlspecialchars($ev['description'], ENT_QUOTES), 0, 50); ?>...</small>
						</td>
						<td>
							<span class="badge bg-info event-date" data-date="<?php echo $ev['date']; ?>"><?php echo date('M j, Y', strtotime($ev['date'])); ?></span>
						</td>
						<td class="event-location"><?php echo htmlspecialchars($ev['location'], ENT_QUOTES); ?></td>
						<td>
							<?php if (!empty($ev['category'])): ?>
								<span class="badge bg-secondary event-category"><?php echo htmlspecialchars($ev['category'], ENT_QUOTES); ?></span>
							<?php else: ?>
								<span class="text-muted event-category">-</span>
							<?php endif; ?>
						</td>
						<td class="event-amount">
							<?php if (!empty($ev['amount']) && $ev['amount'] > 0): ?>
								<span class="badge bg-success">₹<?php echo number_format($ev['amount'], 2); ?></span>
							<?php else: ?>
								<span class="badge bg-info">Free</span>
							<?php endif; ?>
						</td>
						<td>
							<div class="btn-group" role="group">
								<button class="btn btn-sm btn-outline-primary" onclick="editEvent(<?php echo (int)$ev['id']; ?>)" title="Edit">
									<i class="fas fa-edit"></i>
								</button>
								<a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo (int)$ev['id']; ?>&csrf_token=<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>" 
								   onclick="return confirm('Delete this event?')" title="Delete">
									<i class="fas fa-trash"></i>
								</a>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
$(document).ready(function() {
	// Initialize DataTable
	$('#eventsTable').DataTable({
		responsive: true,
		pageLength: 10,
		order: [[2, 'desc']], // Sort by date descending
		columnDefs: [
			{ orderable: false, targets: [0, 6] } // Disable sorting on image and actions columns
		],
		language: {
			search: "Search events:",
			lengthMenu: "Show _MENU_ events per page",
			info: "Showing _START_ to _END_ of _TOTAL_ events",
			infoEmpty: "No events found",
			infoFiltered: "(filtered from _MAX_ total events)"
		}
	});
});

function editEvent(id) {
	// Get event data from the table row
	const row = document.querySelector(`tr[data-event-id="${id}"]`);
	if (!row) return;
	
	document.getElementById('formAction').value = 'update';
	document.getElementById('eventId').value = id;
	document.getElementById('title').value = row.querySelector('.event-title').textContent.trim();
	document.getElementById('date').value = row.querySelector('.event-date').textContent.trim();
	document.getElementById('location').value = row.querySelector('.event-location').textContent.trim();
	document.getElementById('category').value = row.querySelector('.event-category').textContent.trim();
	
	// Handle amount field
	const amountElement = row.querySelector('.event-amount .badge');
	let amountValue = '';
	if (amountElement && !amountElement.textContent.includes('Free')) {
		amountValue = amountElement.textContent.replace('₹', '').trim();
	}
	document.getElementById('amount').value = amountValue;
	
	// Show modal
	const modal = new bootstrap.Modal(document.getElementById('eventModal'));
	modal.show();
}

function resetForm() {
	document.getElementById('formAction').value = 'create';
	document.getElementById('eventId').value = '';
	document.querySelector('form').reset();
	
	// Show modal
	const modal = new bootstrap.Modal(document.getElementById('eventModal'));
	modal.show();
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
