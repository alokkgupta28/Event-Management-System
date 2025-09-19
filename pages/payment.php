<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();
require_once __DIR__ . '/../partials/header.php';

// Check if booking data exists
if (!isset($_SESSION['booking_data'])) {
    header('Location: ' . BASE_URL . 'pages/events.php');
    exit;
}

$bookingData = $_SESSION['booking_data'];
$eventId = $bookingData['event_id'];

// Get event details
$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    unset($_SESSION['booking_data']);
    header('Location: ' . BASE_URL . 'pages/events.php');
    exit;
}

// Razorpay configuration (replace with your actual keys)
$razorpayKeyId = 'rzp_test_1234567890'; // Replace with your Razorpay Key ID
$razorpayKeySecret = 'your_secret_key_here'; // Replace with your Razorpay Secret Key

// For free events, skip payment
if (empty($event['amount']) || $event['amount'] <= 0) {
    // Process free booking
    try {
        $pdo->beginTransaction();
        
        // Update booking status
        $stmt = $pdo->prepare('UPDATE bookings SET booking_status = ? WHERE id = ?');
        $stmt->execute(['confirmed', $bookingData['booking_id']]);
        
        // Create payment record for free event
        $stmt = $pdo->prepare('INSERT INTO payments (booking_id, amount, payment_status) VALUES (?, ?, ?)');
        $stmt->execute([$bookingData['booking_id'], 0, 'success']);
        
        $pdo->commit();
        
        // Clear session data
        unset($_SESSION['booking_data']);
        
        // Redirect to success page
        header('Location: ' . BASE_URL . 'pages/booking-success.php?ref=' . $bookingData['booking_reference']);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Free booking error: ' . $e->getMessage());
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Header -->
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header text-center">
                    <h3 class="mb-0"><i class="fas fa-credit-card me-2"></i>Complete Your Payment</h3>
                    <p class="text-muted mb-0">Secure payment powered by Razorpay</p>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="card mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6><?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?></h6>
                            <p class="text-muted mb-2"><?php echo date('M j, Y', strtotime($event['date'])); ?> at <?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?></p>
                            <p class="text-muted mb-0">Booking Reference: <strong><?php echo htmlspecialchars($bookingData['booking_reference'], ENT_QUOTES); ?></strong></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="h5 text-primary">₹<?php echo number_format($bookingData['total_amount'], 2); ?></div>
                            <small class="text-muted"><?php echo $bookingData['ticket_quantity']; ?> ticket<?php echo $bookingData['ticket_quantity'] > 1 ? 's' : ''; ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="payment-method mb-4">
                                <h6>Payment Method</h6>
                                <div class="d-flex align-items-center">
                                    <img src="https://razorpay.com/assets/razorpay-logo.svg" alt="Razorpay" style="height: 30px;" class="me-3">
                                    <span>Credit/Debit Card, UPI, Net Banking</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="security-info">
                                <h6>Security</h6>
                                <div class="d-flex align-items-center text-success">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <span>256-bit SSL encrypted</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button id="rzp-button" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Pay ₹<?php echo number_format($bookingData['total_amount'], 2); ?>
                        </button>
                        <p class="text-muted mt-3">
                            <i class="fas fa-lock me-1"></i>
                            Your payment information is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Integration -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo $razorpayKeyId; ?>",
    "amount": "<?php echo $bookingData['total_amount'] * 100; ?>", // Amount in paise
    "currency": "INR",
    "name": "<?php echo APP_NAME; ?>",
    "description": "Event Booking - <?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?>",
    "image": "<?php echo BASE_URL; ?>assets/images/logo.png",
    "order_id": "", // This will be generated by your backend
    "handler": function (response) {
        // Handle successful payment
        handlePaymentSuccess(response);
    },
    "prefill": {
        "name": "<?php echo htmlspecialchars($bookingData['attendee_name'], ENT_QUOTES); ?>",
        "email": "<?php echo htmlspecialchars($bookingData['attendee_email'], ENT_QUOTES); ?>",
        "contact": "<?php echo htmlspecialchars($bookingData['attendee_phone'], ENT_QUOTES); ?>"
    },
    "notes": {
        "booking_reference": "<?php echo htmlspecialchars($bookingData['booking_reference'], ENT_QUOTES); ?>",
        "event_id": "<?php echo $eventId; ?>"
    },
    "theme": {
        "color": "#2563eb"
    },
    "modal": {
        "ondismiss": function() {
            // Handle payment modal dismissal
            console.log('Payment cancelled');
        }
    }
};

var rzp = new Razorpay(options);

document.getElementById('rzp-button').onclick = function(e) {
    e.preventDefault();
    
    // Show loading state
    const button = document.getElementById('rzp-button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    button.disabled = true;
    
    // Create order first (you'll need to implement this endpoint)
    createRazorpayOrder().then(function(orderId) {
        options.order_id = orderId;
        rzp.open();
        
        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;
    }).catch(function(error) {
        console.error('Error creating order:', error);
        alert('Error creating payment order. Please try again.');
        
        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;
    });
};

function createRazorpayOrder() {
    return fetch('<?php echo BASE_URL; ?>api/create-razorpay-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            amount: <?php echo $bookingData['total_amount'] * 100; ?>,
            currency: 'INR',
            booking_id: <?php echo $bookingData['booking_id']; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.order_id;
        } else {
            throw new Error(data.message || 'Failed to create order');
        }
    });
}

function handlePaymentSuccess(response) {
    // Show success message
    const button = document.getElementById('rzp-button');
    button.innerHTML = '<i class="fas fa-check me-2"></i>Payment Successful!';
    button.classList.remove('btn-primary');
    button.classList.add('btn-success');
    button.disabled = true;
    
    // Verify payment with backend
    verifyPayment(response).then(function(result) {
        if (result.success) {
            // Redirect to success page
            window.location.href = '<?php echo BASE_URL; ?>pages/booking-success.php?ref=<?php echo $bookingData['booking_reference']; ?>&payment_id=' + response.razorpay_payment_id;
        } else {
            alert('Payment verification failed. Please contact support.');
        }
    }).catch(function(error) {
        console.error('Payment verification error:', error);
        alert('Payment verification failed. Please contact support.');
    });
}

function verifyPayment(response) {
    return fetch('<?php echo BASE_URL; ?>api/verify-payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_signature: response.razorpay_signature,
            booking_id: <?php echo $bookingData['booking_id']; ?>
        })
    })
    .then(response => response.json());
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
