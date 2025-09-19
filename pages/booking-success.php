<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
require_once __DIR__ . '/../partials/header.php';

$bookingRef = $_GET['ref'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';

if (!$bookingRef) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

// Get booking details
$stmt = $pdo->prepare('
    SELECT b.*, e.title, e.date, e.location, e.image, e.amount, u.name as user_name, u.email as user_email
    FROM bookings b
    JOIN events e ON e.id = b.event_id
    JOIN users u ON u.id = b.user_id
    WHERE b.booking_reference = ? AND b.user_id = ?
');
$stmt->execute([$bookingRef, $_SESSION['user']['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

// Get payment details
$stmt = $pdo->prepare('SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC LIMIT 1');
$stmt->execute([$booking['id']]);
$payment = $stmt->fetch();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Header -->
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="success-icon mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h1 class="text-success mb-3">Booking Confirmed!</h1>
                <p class="lead text-muted">Your tickets have been successfully booked</p>
            </div>

            <!-- Booking Details Card -->
            <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Booking Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title"><?php echo htmlspecialchars($booking['title'], ENT_QUOTES); ?></h5>
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Date:</strong><br>
                                    <span class="text-muted"><?php echo date('l, F j, Y', strtotime($booking['date'])); ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Location:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($booking['location'], ENT_QUOTES); ?></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Booking Reference:</strong><br>
                                    <span class="text-primary fw-bold"><?php echo htmlspecialchars($booking['booking_reference'], ENT_QUOTES); ?></span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-success"><?php echo ucfirst($booking['booking_status']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if (!empty($booking['image'])): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($booking['image'], ENT_QUOTES); ?>" 
                                     class="img-fluid rounded" alt="Event image" style="max-height: 150px;">
                            <?php else: ?>
                                <img src="https://picsum.photos/seed/event<?php echo $booking['event_id']; ?>/300/200" 
                                     class="img-fluid rounded" alt="Event image">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Information -->
            <div class="card mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Number of Tickets:</span>
                                <strong><?php echo $booking['ticket_quantity']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Price per Ticket:</span>
                                <span>
                                    <?php if ($booking['amount'] > 0): ?>
                                        ₹<?php echo number_format($booking['amount'], 2); ?>
                                    <?php else: ?>
                                        Free
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between h5">
                                <span>Total Amount:</span>
                                <span class="text-primary">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if ($payment): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Payment Status:</span>
                                    <span class="badge bg-success"><?php echo ucfirst($payment['payment_status']); ?></span>
                                </div>
                                <?php if ($payment['razorpay_payment_id']): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Payment ID:</span>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['razorpay_payment_id'], ENT_QUOTES); ?></small>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between">
                                    <span>Payment Date:</span>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($payment['payment_date'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="card mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Important Information</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-ticket-alt me-2"></i>Your Tickets</h6>
                        <ul class="mb-0">
                            <li>Please arrive at the venue 15 minutes before the event starts</li>
                            <li>Bring a valid ID and this booking confirmation</li>
                            <li>Your booking reference is: <strong><?php echo htmlspecialchars($booking['booking_reference'], ENT_QUOTES); ?></strong></li>
                            <li>Keep this confirmation safe as it serves as your ticket</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Cancellation Policy</h6>
                        <p class="mb-0">Tickets are non-refundable. Please contact us at least 24 hours before the event for any changes or special requests.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mb-5" data-aos="fade-up" data-aos-delay="400">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>View Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/events.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-calendar me-2"></i>Browse More Events
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-print me-2"></i>Print Confirmation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .btn, .alert-warning {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        margin-bottom: 20px !important;
    }
    .success-icon {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
