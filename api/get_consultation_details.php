<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Load required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/ConsultationsController.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';
require_once __DIR__ . '/../controllers/QueueController.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please login first.']);
    exit;
}

// Check if ID parameter is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Consultation ID is required']);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Get database connection
    global $pdo;

    // Create controller instance
    $controller = new ConsultationsController($pdo);

    // Get consultation details
    $details = $controller->getConsultationDetails($id);

    if ($details) {
        echo json_encode(['success' => true, 'data' => $details]);
    } else {
        echo json_encode(['error' => 'Consultation not found']);
    }
} catch (Exception $e) {
    error_log("Error in get_consultation_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
