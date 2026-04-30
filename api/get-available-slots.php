<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$providerId = $_GET['provider_id'] ?? null;

try {
    global $pdo;
    $controller = new AppointmentController($pdo);
    $slots = $controller->getAvailableSlots($date, $providerId);
    echo json_encode($slots);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
