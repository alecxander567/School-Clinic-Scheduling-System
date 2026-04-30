<?php
class QueueController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function generateQRToken($appointmentId)
    {
        try {
            // Return existing token if already generated
            $existing = $this->pdo->prepare("SELECT qr_code_token, qr_code_url FROM appointments WHERE id = :id AND qr_code_token IS NOT NULL");
            $existing->execute([':id' => $appointmentId]);
            $row = $existing->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['qr_code_token'])) {
                return ['token' => $row['qr_code_token'], 'url' => $row['qr_code_url']];
            }

            // Only generate a new token if one doesn't exist
            $token = bin2hex(random_bytes(32));
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $qrCodeUrl = $protocol . '://' . $host . '/students/queue-form.php?token=' . $token;

            $sql = "UPDATE appointments SET qr_code_token = :token, qr_code_url = :url WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token, ':url' => $qrCodeUrl, ':id' => $appointmentId]);

            return ['token' => $token, 'url' => $qrCodeUrl];
        } catch (Exception $e) {
            error_log("QR Token Generation Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAppointmentByToken($token)
    {
        $sql = "SELECT a.*, s.service_name, p.name as provider_name, 
                       ap.status_name,
                       (SELECT COUNT(*) FROM appointment_queues aq 
                        WHERE aq.appointment_id = a.id AND aq.status = 'pending') as current_queue_count
                FROM appointments a
                JOIN services s ON a.service_id = s.id
                JOIN providers p ON a.provider_id = p.id
                JOIN appointment_statuses ap ON a.status_id = ap.id
                WHERE a.qr_code_token = :token 
                AND a.visit_date >= CURDATE()";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addToQueue($appointmentId, $studentId, $formData)
    {
        // Check if already registered
        $checkSql = "SELECT id FROM appointment_queues 
                     WHERE appointment_id = :appointment_id 
                     AND student_id = :student_id 
                     AND status = 'pending'";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([
            ':appointment_id' => $appointmentId,
            ':student_id' => $studentId
        ]);

        if ($checkStmt->fetch()) {
            return ['error' => 'You have already registered for this appointment'];
        }

        // Check if appointment is full
        $capacitySql = "SELECT max_students, COALESCE(registered_students, 0) as registered_students 
                        FROM appointments 
                        WHERE id = :id";
        $capacityStmt = $this->pdo->prepare($capacitySql);
        $capacityStmt->execute([':id' => $appointmentId]);
        $capacity = $capacityStmt->fetch(PDO::FETCH_ASSOC);

        if ($capacity && $capacity['registered_students'] >= $capacity['max_students']) {
            return ['error' => 'This appointment is already fully booked'];
        }

        // Get next priority number
        $prioritySql = "SELECT COALESCE(MAX(priority_number), 0) + 1 as next_priority 
                        FROM appointment_queues 
                        WHERE appointment_id = :appointment_id AND status = 'pending'";
        $priorityStmt = $this->pdo->prepare($prioritySql);
        $priorityStmt->execute([':appointment_id' => $appointmentId]);
        $priority = $priorityStmt->fetch(PDO::FETCH_ASSOC);
        $priorityNumber = $priority['next_priority'] ?? 1;

        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // Add to queue
            $sql = "INSERT INTO appointment_queues (appointment_id, student_id, priority_number, form_data, status) 
                    VALUES (:appointment_id, :student_id, :priority_number, :form_data, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':appointment_id' => $appointmentId,
                ':student_id' => $studentId,
                ':priority_number' => $priorityNumber,
                ':form_data' => json_encode($formData)
            ]);

            // Update the appointments table to increment registered_students
            $updateSql = "UPDATE appointments 
                          SET registered_students = COALESCE(registered_students, 0) + 1 
                          WHERE id = :appointment_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':appointment_id' => $appointmentId]);

            // Commit transaction
            $this->pdo->commit();

            return [
                'success' => true,
                'priority_number' => $priorityNumber,
                'queue_id' => $this->pdo->lastInsertId()
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['error' => 'Failed to register: ' . $e->getMessage()];
        }
    }
}
