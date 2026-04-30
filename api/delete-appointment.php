<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is admin using is_admin session variable
$isAdmin = $_SESSION['is_admin'] ?? false;
if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Only administrators can delete appointments']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$appointmentId = $_POST['appointment_id'] ?? null;
if (!$appointmentId) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
    exit;
}

try {
    global $pdo;
    $controller = new AppointmentController($pdo);
    $result = $controller->deleteAppointment($appointmentId);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
