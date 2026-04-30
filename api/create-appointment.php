<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    global $pdo;
    $controller = new AppointmentController($pdo);

    // Prepare data for appointment creation
    $data = [
        'requester_id' => $_POST['requester_id'] ?? null,
        'provider_id' => $_POST['provider_id'] ?? null,
        'service_id' => $_POST['service_id'] ?? null,
        'status_id' => $_POST['status_id'] ?? 1,
        'visit_date' => $_POST['visit_date'] ?? null,
        'start_time' => $_POST['start_time'] ?? null,
        'end_time' => $_POST['end_time'] ?? null,
        'max_students' => $_POST['max_students'] ?? null,
        'notes' => $_POST['notes'] ?? null
    ];

    error_log("=== POST Data ===");
    error_log(print_r($_POST, true));

    $result = $controller->createAppointment($data);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
