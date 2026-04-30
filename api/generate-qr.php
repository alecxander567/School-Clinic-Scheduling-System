<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Load required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/QueueController.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please login first.']);
    exit;
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['appointment_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request. Appointment ID required.']);
    exit;
}

$appointmentId = (int)$data['appointment_id'];

try {
    // Check if appointment exists
    $checkSql = "SELECT id FROM appointments WHERE id = :id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':id' => $appointmentId]);

    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Appointment not found']);
        exit;
    }

    // Create QueueController instance
    $queueController = new QueueController($pdo);

    // Generate QR token
    $result = $queueController->generateQRToken($appointmentId);

    if ($result && isset($result['url'])) {
        // Get current queue count for this appointment
        $countSql = "SELECT COUNT(*) as count FROM appointment_queues 
                     WHERE appointment_id = :id AND status = 'pending'";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([':id' => $appointmentId]);
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'qr_url' => $result['url'],
            'qr_token' => $result['token'],
            'priority_count' => $count['count'] ?? 0
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to generate QR code']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
