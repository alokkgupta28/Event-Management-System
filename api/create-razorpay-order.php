<?php
require_once __DIR__ . '/../includes/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['amount']) || !isset($input['booking_id'])) {
        throw new Exception('Invalid input data');
    }
    
    $amount = (int)$input['amount'];
    $bookingId = (int)$input['booking_id'];
    $currency = $input['currency'] ?? 'INR';
    
    // Verify booking belongs to current user
    $stmt = $pdo->prepare('SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ? AND booking_status = ?');
    $stmt->execute([$bookingId, $_SESSION['user']['id'], 'pending']);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        throw new Exception('Invalid booking');
    }
    
    // Verify amount matches
    if ($amount !== (int)($booking['total_amount'] * 100)) {
        throw new Exception('Amount mismatch');
    }
    
    // Razorpay configuration (replace with your actual keys)
    $razorpayKeyId = 'rzp_test_RJAMPPwg1DCWkn'; // Replace with your Razorpay Key ID
    $razorpayKeySecret = 'B63QriyxQ1DPDxxnfz3tCqDc'; // Replace with your Razorpay Secret Key
    
    // Create Razorpay order
    $orderData = [
        'amount' => $amount,
        'currency' => $currency,
        'receipt' => 'booking_' . $bookingId,
        'notes' => [
            'booking_id' => $bookingId,
            'user_id' => $_SESSION['user']['id']
        ]
    ];
    
    // For demo purposes, we'll generate a mock order ID
    // In production, you would make an API call to Razorpay
    $orderId = 'order_' . uniqid() . '_' . time();
    
    // Store order details in database
    $stmt = $pdo->prepare('INSERT INTO payments (booking_id, razorpay_order_id, amount, currency, payment_status) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$bookingId, $orderId, $booking['total_amount'], $currency, 'pending']);
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency
    ]);
    
} catch (Exception $e) {
    error_log('Razorpay order creation error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
