<?php
class ScheduleController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all appointments with registered students count
     */
    public function getAllAppointments()
    {
        $sql = "
            SELECT 
                a.id,
                a.priority_number as appointment_priority,
                a.notes,
                a.created_at,
                a.visit_date,
                a.start_time,
                a.end_time,
                a.max_students,
                a.registered_students,
                a.qr_code_token,
                a.qr_code_url,
                s.service_name,
                st.status_name,
                p.name as provider_name,
                CASE 
                    WHEN a.registered_students >= a.max_students THEN 'Full'
                    WHEN a.visit_date < CURDATE() THEN 'Completed'
                    WHEN a.visit_date = CURDATE() AND a.start_time < CURTIME() THEN 'In Progress'
                    ELSE 'Available'
                END as appointment_status
            FROM appointments a
            INNER JOIN services s ON a.service_id = s.id
            INNER JOIN appointment_statuses st ON a.status_id = st.id
            LEFT JOIN providers p ON a.provider_id = p.id
            WHERE a.visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY a.visit_date DESC, a.start_time
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get registered students for a specific appointment
     */
    public function getRegisteredStudents($appointmentId)
    {
        $sql = "
            SELECT 
                aq.id,
                aq.priority_number,
                aq.status as queue_status,
                aq.created_at as registration_date,
                aq.form_data,
                s.id as student_id,
                s.student_number,
                s.first_name,
                s.last_name,
                s.course,
                s.year_level,
                s.email,
                s.contact_number
            FROM appointment_queues aq
            INNER JOIN students s ON aq.student_id = s.id
            WHERE aq.appointment_id = :appointment_id AND aq.status = 'pending'
            ORDER BY aq.priority_number
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':appointment_id' => $appointmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single appointment by ID
     */
    public function getAppointmentById($appointmentId)
    {
        $appointments = $this->getAllAppointments();
        foreach ($appointments as $appointment) {
            if ($appointment['id'] == $appointmentId) {
                return $appointment;
            }
        }
        return null;
    }

    /**
     * Generate QR code for appointment
     */
    public function generateQRCode($appointmentId)
    {
        try {
            // Check for existing token
            $existing = $this->pdo->prepare("SELECT qr_code_token, qr_code_url FROM appointments WHERE id = :id AND qr_code_token IS NOT NULL");
            $existing->execute([':id' => $appointmentId]);
            $row = $existing->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['qr_code_token'])) {
                return ['url' => $row['qr_code_url']];
            }

            // Generate new token
            $token = bin2hex(random_bytes(32));
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $qrCodeUrl = $protocol . '://' . $host . '/students/queue-form.php?token=' . $token;

            $sql = "UPDATE appointments SET qr_code_token = :token, qr_code_url = :url WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token, ':url' => $qrCodeUrl, ':id' => $appointmentId]);

            return ['url' => $qrCodeUrl];
        } catch (Exception $e) {
            error_log("QR Token Generation Error: " . $e->getMessage());
            return ['error' => 'Failed to generate QR code: ' . $e->getMessage()];
        }
    }

    /**
     * Get appointment statistics
     */
    public function getStatistics()
    {
        $sql = "
            SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN visit_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_appointments,
                SUM(CASE WHEN registered_students >= max_students THEN 1 ELSE 0 END) as fully_booked,
                SUM(registered_students) as total_registered_students
            FROM appointments
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
