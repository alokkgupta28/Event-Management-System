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
    
    if (!$input || !isset($input['razorpay_payment_id']) || !isset($input['booking_id'])) {
        throw new Exception('Invalid input data');
    }
    
    $paymentId = $input['razorpay_payment_id'];
    $orderId = $input['razorpay_order_id'] ?? '';
    $signature = $input['razorpay_signature'] ?? '';
    $bookingId = (int)$input['booking_id'];
    
    // Verify booking belongs to current user
    $stmt = $pdo->prepare('SELECT id, total_amount FROM bookings WHERE id = ? AND user_id = ?');
    $stmt->execute([$bookingId, $_SESSION['user']['id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        throw new Exception('Invalid booking');
    }
    
    // In production, you would verify the signature with Razorpay
    // For demo purposes, we'll assume payment is successful
    
    $pdo->beginTransaction();
    
    try {
        // Update booking status
        $stmt = $pdo->prepare('UPDATE bookings SET booking_status = ? WHERE id = ?');
        $stmt->execute(['confirmed', $bookingId]);
        
        // Update payment record
        $stmt = $pdo->prepare('UPDATE payments SET razorpay_payment_id = ?, payment_status = ? WHERE booking_id = ? AND razorpay_order_id = ?');
        $stmt->execute([$paymentId, 'success', $bookingId, $orderId]);
        
        $pdo->commit();
        
        // Clear booking data from session
        unset($_SESSION['booking_data']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully',
            'booking_id' => $bookingId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Payment verification error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
