<?php
require_once __DIR__ . '/../includes/db.php';
if (!isAdmin()) { header('Location: ' . BASE_URL . 'auth/login.php'); exit; }
require_once __DIR__ . '/../partials/header.php';

$rows = $pdo->query('SELECT r.id, r.tickets, r.timestamp, e.id as event_id, e.title, e.date, u.name as user_name, u.email as user_email
	FROM registrations r
	JOIN events e ON e.id = r.event_id
	JOIN users u ON u.id = r.user_id
	ORDER BY e.date DESC, r.timestamp DESC')->fetchAll();

$grouped = [];
foreach ($rows as $r) {
	$grouped[$r['event_id']]['event'] = [ 'title' => $r['title'], 'date' => $r['date'] ];
	$grouped[$r['event_id']]['items'][] = $r;
}
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
	<h2 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Event Registrations</h2>
	<span class="badge bg-primary"><?php echo count($rows); ?> total registrations</span>
</div>
<?php if (!$grouped): ?>
	<div class="card" data-aos="fade-up">
		<div class="card-body text-center py-5">
			<i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
			<h5 class="text-muted">No registrations found</h5>
			<p class="text-muted">When users register for events, they will appear here.</p>
		</div>
	</div>
<?php endif; ?>

<?php foreach ($grouped as $eventId => $data): ?>
	<div class="card mb-4" data-aos="fade-up" data-aos-delay="<?php echo $eventId * 100; ?>">
		<div class="card-header d-flex justify-content-between align-items-center">
			<div>
				<h5 class="mb-1"><i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($data['event']['title'], ENT_QUOTES); ?></h5>
				<span class="text-muted"><i class="fas fa-clock me-1"></i><?php echo date('M j, Y', strtotime($data['event']['date'])); ?></span>
			</div>
			<div class="text-end">
				<span class="badge bg-primary fs-6"><?php echo count($data['items']); ?> participants</span>
				<?php 
				$totalTickets = array_sum(array_column($data['items'], 'tickets'));
				if ($totalTickets > count($data['items'])): 
				?>
					<span class="badge bg-success ms-2"><?php echo $totalTickets; ?> total tickets</span>
				<?php endif; ?>
			</div>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover registrations-table">
					<thead>
						<tr>
							<th><i class="fas fa-user me-1"></i>Participant</th>
							<th><i class="fas fa-envelope me-1"></i>Email</th>
							<th><i class="fas fa-ticket-alt me-1"></i>Tickets</th>
							<th><i class="fas fa-clock me-1"></i>Registered</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data['items'] as $row): ?>
						<tr>
							<td>
								<div class="d-flex align-items-center">
									<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:35px;height:35px;">
										<i class="fas fa-user text-white small"></i>
									</div>
									<div>
										<div class="fw-bold"><?php echo htmlspecialchars($row['user_name'], ENT_QUOTES); ?></div>
										<small class="text-muted">Registration #<?php echo (int)$row['id']; ?></small>
									</div>
								</div>
							</td>
							<td>
								<a href="mailto:<?php echo htmlspecialchars($row['user_email'], ENT_QUOTES); ?>" class="text-decoration-none">
									<?php echo htmlspecialchars($row['user_email'], ENT_QUOTES); ?>
								</a>
							</td>
							<td>
								<span class="badge bg-info"><?php echo (int)$row['tickets']; ?> ticket<?php echo (int)$row['tickets'] > 1 ? 's' : ''; ?></span>
							</td>
							<td>
								<span class="text-muted"><?php echo date('M j, Y', strtotime($row['timestamp'])); ?></span>
								<small class="d-block text-muted"><?php echo date('g:i A', strtotime($row['timestamp'])); ?></small>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
<?php endforeach; ?>

<script>
$(document).ready(function() {
	$('.registrations-table').DataTable({
		responsive: true,
		pageLength: 10,
		order: [[3, 'desc']], // Sort by registration date descending
		columnDefs: [
			{ orderable: false, targets: [0] } // Disable sorting on participant column
		],
		language: {
			search: "Search participants:",
			lengthMenu: "Show _MENU_ participants per page",
			info: "Showing _START_ to _END_ of _TOTAL_ participants",
			infoEmpty: "No participants found",
			infoFiltered: "(filtered from _MAX_ total participants)"
		}
	});
});
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
