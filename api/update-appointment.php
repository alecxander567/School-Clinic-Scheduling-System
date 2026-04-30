<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';

header('Content-Type: application/json');

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

    $data = [
        'appointment_id' => $_POST['appointment_id'] ?? null,
        'provider_id' => $_POST['provider_id'] ?? null,
        'service_id' => $_POST['service_id'] ?? null,
        'status_id' => $_POST['status_id'] ?? null,
        'visit_date' => $_POST['visit_date'] ?? null,
        'start_time' => $_POST['start_time'] ?? null,
        'end_time' => $_POST['end_time'] ?? null,
        'max_students' => $_POST['max_students'] ?? null,
        'notes' => $_POST['notes'] ?? null
    ];

    $result = $controller->updateAppointment($data);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
