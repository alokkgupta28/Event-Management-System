<?php
require_once __DIR__ . '/../includes/db.php';
if (!isAdmin()) { header('Location: ' . BASE_URL . 'auth/login.php'); exit; }
require_once __DIR__ . '/../partials/header.php';

$success = '';
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
	verify_csrf();
	$id = (int)$_GET['id'];
	if ($id !== (int)$_SESSION['user']['id']) {
		$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
		$success = 'User deleted.';
	}
}

$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
	<h2 class="mb-0"><i class="fas fa-users me-2"></i>Manage Users</h2>
	<span class="badge bg-primary"><?php echo count($users); ?> users</span>
</div>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></div><?php endif; ?>

<div class="card" data-aos="fade-up" data-aos-delay="100">
	<div class="card-header">
		<h5 class="mb-0"><i class="fas fa-list me-2"></i>All Users</h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="usersTable" class="table table-hover">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Email</th>
						<th>Role</th>
						<th>Joined</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $u): ?>
					<tr>
						<td>
							<span class="badge bg-light text-dark">#<?php echo (int)$u['id']; ?></span>
						</td>
						<td>
							<div class="d-flex align-items-center">
								<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
									<i class="fas fa-user text-white"></i>
								</div>
								<div>
									<div class="fw-bold"><?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?></div>
									<small class="text-muted">User ID: <?php echo (int)$u['id']; ?></small>
								</div>
							</div>
						</td>
						<td>
							<a href="mailto:<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>" class="text-decoration-none">
								<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>
							</a>
						</td>
						<td>
							<?php if ($u['role'] === 'admin'): ?>
								<span class="badge bg-danger"><i class="fas fa-crown me-1"></i>Admin</span>
							<?php else: ?>
								<span class="badge bg-success"><i class="fas fa-user me-1"></i>User</span>
							<?php endif; ?>
						</td>
						<td>
							<span class="text-muted"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></span>
							<small class="d-block text-muted"><?php echo date('g:i A', strtotime($u['created_at'])); ?></small>
						</td>
						<td>
							<?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?>
								<a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo (int)$u['id']; ?>&csrf_token=<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>" 
								   onclick="return confirm('Delete this user? This will remove their registrations.')" title="Delete User">
									<i class="fas fa-trash"></i>
								</a>
							<?php else: ?>
								<span class="text-muted"><i class="fas fa-info-circle me-1"></i>Current User</span>
							<?php endif; ?>
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
	$('#usersTable').DataTable({
		responsive: true,
		pageLength: 10,
		order: [[0, 'desc']], // Sort by ID descending
		columnDefs: [
			{ orderable: false, targets: [5] } // Disable sorting on actions column
		],
		language: {
			search: "Search users:",
			lengthMenu: "Show _MENU_ users per page",
			info: "Showing _START_ to _END_ of _TOTAL_ users",
			infoEmpty: "No users found",
			infoFiltered: "(filtered from _MAX_ total users)"
		}
	});
});
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
