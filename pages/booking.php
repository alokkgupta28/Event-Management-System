<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
require_once __DIR__ . '/../partials/header.php';

$eventId = (int)($_GET['event_id'] ?? 0);
$errors = [];
$success = '';

if ($eventId <= 0) {
    header('Location: ' . BASE_URL . 'pages/events.php');
    exit;
}

// Get event details
$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . BASE_URL . 'pages/events.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $ticketQuantity = (int)($_POST['ticket_quantity'] ?? 0);
    $attendeeName = trim($_POST['attendee_name'] ?? '');
    $attendeeEmail = trim($_POST['attendee_email'] ?? '');
    $attendeePhone = trim($_POST['attendee_phone'] ?? '');
    $specialRequests = trim($_POST['special_requests'] ?? '');
    
    // Validation
    if ($ticketQuantity < 1 || $ticketQuantity > 10) {
        $errors['ticket_quantity'] = 'Please select between 1 and 10 tickets.';
    }
    if ($attendeeName === '') {
        $errors['attendee_name'] = 'Attendee name is required.';
    }
    if ($attendeeEmail === '' || !filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['attendee_email'] = 'Valid email address is required.';
    }
    if ($attendeePhone === '') {
        $errors['attendee_phone'] = 'Phone number is required.';
    }
    
    if (!$errors) {
        try {
            // Calculate total amount
            $ticketPrice = (float)$event['amount'] ?? 0;
            $totalAmount = $ticketQuantity * $ticketPrice;
            
            // Generate booking reference
            $bookingRef = 'BK' . date('Ymd') . rand(1000, 9999);
            
            // Create booking
            $stmt = $pdo->prepare('INSERT INTO bookings (event_id, user_id, booking_reference, ticket_quantity, total_amount, booking_status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$eventId, $userId, $bookingRef, $ticketQuantity, $totalAmount, 'pending']);
            $bookingId = $pdo->lastInsertId();
            
            // Store booking details in session for payment
            $_SESSION['booking_data'] = [
                'booking_id' => $bookingId,
                'event_id' => $eventId,
                'ticket_quantity' => $ticketQuantity,
                'total_amount' => $totalAmount,
                'attendee_name' => $attendeeName,
                'attendee_email' => $attendeeEmail,
                'attendee_phone' => $attendeePhone,
                'special_requests' => $specialRequests,
                'booking_reference' => $bookingRef
            ];
            
            // Redirect to payment
            header('Location: ' . BASE_URL . 'pages/payment.php');
            exit;
            
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred. Please try again.';
            error_log('Booking error: ' . $e->getMessage());
        }
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Event Details -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Event Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if (!empty($event['image'])): ?>
                                <img src="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($event['image'], ENT_QUOTES); ?>" 
                                     class="img-fluid rounded" alt="Event image">
                            <?php else: ?>
                                <img src="https://picsum.photos/seed/event<?php echo $event['id']; ?>/400/300" 
                                     class="img-fluid rounded" alt="Event image">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($event['description'], ENT_QUOTES); ?></p>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Date:</strong><br>
                                    <span class="text-muted"><?php echo date('M j, Y', strtotime($event['date'])); ?></span>
                                </div>
                                <div class="col-6">
                                    <strong>Location:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?></span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <strong>Price:</strong>
                                <span class="h5 text-primary ms-2">
                                    <?php if (!empty($event['amount']) && $event['amount'] > 0): ?>
                                        ₹<?php echo number_format($event['amount'], 2); ?> per ticket
                                    <?php else: ?>
                                        Free Event
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Book Your Tickets</h4>
                </div>
                <div class="card-body">
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Number of Tickets <span class="text-danger">*</span></label>
                                <select name="ticket_quantity" class="form-select<?php echo isset($errors['ticket_quantity']) ? ' is-invalid' : ''; ?>" required>
                                    <option value="">Select tickets</option>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> ticket<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div class="invalid-feedback"><?php echo $errors['ticket_quantity'] ?? 'Please select number of tickets.'; ?></div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Attendee Information</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="attendee_name" class="form-control<?php echo isset($errors['attendee_name']) ? ' is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['attendee_name'] ?? '', ENT_QUOTES); ?>" required>
                                <div class="invalid-feedback"><?php echo $errors['attendee_name'] ?? 'Please enter your full name.'; ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="attendee_email" class="form-control<?php echo isset($errors['attendee_email']) ? ' is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['attendee_email'] ?? '', ENT_QUOTES); ?>" required>
                                <div class="invalid-feedback"><?php echo $errors['attendee_email'] ?? 'Please enter a valid email address.'; ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="attendee_phone" class="form-control<?php echo isset($errors['attendee_phone']) ? ' is-invalid' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['attendee_phone'] ?? '', ENT_QUOTES); ?>" required>
                                <div class="invalid-feedback"><?php echo $errors['attendee_phone'] ?? 'Please enter your phone number.'; ?></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Special Requests (Optional)</label>
                            <textarea name="special_requests" class="form-control" rows="3" 
                                      placeholder="Any special dietary requirements, accessibility needs, or other requests..."><?php echo htmlspecialchars($_POST['special_requests'] ?? '', ENT_QUOTES); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Booking Summary -->
            <div class="card sticky-top" style="top: 2rem;" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Event:</span>
                        <span class="fw-bold"><?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Date:</span>
                        <span><?php echo date('M j, Y', strtotime($event['date'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Location:</span>
                        <span><?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Price per ticket:</span>
                        <span>
                            <?php if (!empty($event['amount']) && $event['amount'] > 0): ?>
                                ₹<?php echo number_format($event['amount'], 2); ?>
                            <?php else: ?>
                                Free
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Quantity:</span>
                        <span id="quantity-display">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h5">
                        <span>Total:</span>
                        <span class="text-primary" id="total-display">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update booking summary when quantity changes
document.querySelector('select[name="ticket_quantity"]').addEventListener('change', function() {
    const quantity = parseInt(this.value) || 0;
    const pricePerTicket = <?php echo !empty($event['amount']) ? $event['amount'] : 0; ?>;
    const total = quantity * pricePerTicket;
    
    document.getElementById('quantity-display').textContent = quantity;
    document.getElementById('total-display').textContent = total > 0 ? '₹' + total.toFixed(2) : 'Free';
});

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
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
