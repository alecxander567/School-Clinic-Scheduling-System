<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$appointmentId = $_GET['id'] ?? 0;

if (!$appointmentId) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
    exit;
}

try {
    $sql = "SELECT a.*, 
                   s.service_name, 
                   p.name as provider_name,
                   ap.status_name,
                   COALESCE((SELECT COUNT(*) FROM appointment_queues aq WHERE aq.appointment_id = a.id AND aq.status = 'pending'), 0) as registered_students
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN providers p ON a.provider_id = p.id
            JOIN appointment_statuses ap ON a.status_id = ap.id
            WHERE a.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $appointmentId]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        echo json_encode([
            'success' => true,
            'appointment' => $appointment
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
