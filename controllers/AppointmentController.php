<?php
class AppointmentController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all requesters (students)
     */
    public function getRequesters()
    {
        $sql = "
            SELECT r.id, r.student_id, 
                   CONCAT(s.first_name, ' ', s.last_name) as name,
                   s.student_number,
                   s.course,
                   s.year_level
            FROM requesters r
            INNER JOIN students s ON r.student_id = s.id
            ORDER BY s.last_name, s.first_name
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get all services
     */
    public function getServices()
    {
        $sql = "
            SELECT s.id, s.service_name, s.description, ct.type_name as clinic_type
            FROM services s
            LEFT JOIN clinic_types ct ON s.clinic_type_id = ct.id
            ORDER BY s.service_name
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get appointment statuses
     */
    public function getStatuses()
    {
        $sql = "SELECT id, status_name FROM appointment_statuses ORDER BY id";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get available appointment slots for a specific date and provider
     */
    public function getAvailableSlots($date = null, $providerId = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Get slots with availability info
        $sql = "
            SELECT 
                aps.id,
                aps.start_time,
                aps.end_time,
                aps.max_patients,
                COUNT(a.id) as booked_count
            FROM appointment_slots aps
            LEFT JOIN appointments a ON aps.id = a.slot_id 
                AND DATE(a.created_at) = aps.slot_date
            WHERE aps.slot_date = :date
        ";

        if ($providerId) {
            $sql .= " AND aps.provider_id = :provider_id";
        }

        $sql .= " GROUP BY aps.id
                  HAVING booked_count < aps.max_patients
                  ORDER BY aps.start_time";

        $stmt = $this->pdo->prepare($sql);
        $params = [':date' => $date];
        if ($providerId) {
            $params[':provider_id'] = $providerId;
        }
        $stmt->execute($params);
        $slots = $stmt->fetchAll();

        $availableSlots = [];
        foreach ($slots as $slot) {
            $availableSlots[] = [
                'id' => $slot['id'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'time_range' => date('g:i A', strtotime($slot['start_time'])) . ' - ' .
                    date('g:i A', strtotime($slot['end_time'])),
                'available' => $slot['max_patients'] - $slot['booked_count'],
                'max_patients' => $slot['max_patients']
            ];
        }

        return $availableSlots;
    }

    /**
     * Create a new appointment (schedule provider visit)
     */
    public function createAppointment($data)
    {
        try {
            // Validate required fields
            if (empty($data['service_id'])) {
                return ['success' => false, 'message' => 'Service type is required'];
            }

            if (empty($data['status_id'])) {
                return ['success' => false, 'message' => 'Status is required'];
            }

            if (empty($data['visit_date'])) {
                return ['success' => false, 'message' => 'Visit date is required'];
            }

            if (empty($data['start_time'])) {
                return ['success' => false, 'message' => 'Start time is required'];
            }

            if (empty($data['end_time'])) {
                return ['success' => false, 'message' => 'End time is required'];
            }

            if (empty($data['max_students'])) {
                return ['success' => false, 'message' => 'Maximum students is required'];
            }

            // Use a default requester_id if not provided (e.g., 1 for system)
            $requesterId = !empty($data['requester_id']) ? $data['requester_id'] : 1;

            // Insert appointment with provider_id
            $sql = "INSERT INTO appointments (requester_id, provider_id, service_id, status_id, visit_date, start_time, end_time, max_students, notes) 
                VALUES (:requester_id, :provider_id, :service_id, :status_id, :visit_date, :start_time, :end_time, :max_students, :notes)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':requester_id' => $requesterId,
                ':provider_id' => $data['provider_id'] ?? null,
                ':service_id' => $data['service_id'],
                ':status_id' => $data['status_id'],
                ':visit_date' => $data['visit_date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':max_students' => $data['max_students'],
                ':notes' => $data['notes'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Provider visit scheduled successfully',
                'appointment_id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get appointments for a specific date
     */
    public function getAppointmentsByDate($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "
        SELECT 
            a.id,
            a.priority_number,
            a.notes,
            a.created_at,
            a.visit_date,
            a.start_time,
            a.end_time,
            a.max_students,
            s.service_name,
            st.status_name,
            p.name as provider_name,
            0 as registered_students
        FROM appointments a
        INNER JOIN services s ON a.service_id = s.id
        INNER JOIN appointment_statuses st ON a.status_id = st.id
        LEFT JOIN providers p ON a.provider_id = p.id
        WHERE a.visit_date = :date
        ORDER BY a.start_time, a.priority_number
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Get table columns
     */
    private function getTableColumns($table)
    {
        try {
            $stmt = $this->pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $columns;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get all providers (doctors/dentists)
     */
    public function getProviders()
    {
        $sql = "SELECT * FROM providers ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get appointments by status
     */
    public function getAppointmentsByStatus($statusId = null)
    {
        if (!$statusId) {
            return $this->getAppointmentsByDate();
        }

        $sql = "
        SELECT 
            a.id,
            a.priority_number,
            a.notes,
            a.created_at,
            a.visit_date,
            a.start_time,
            a.end_time,
            a.max_students,
            s.service_name,
            st.status_name,
            p.name as provider_name,
            0 as registered_students
        FROM appointments a
        INNER JOIN services s ON a.service_id = s.id
        INNER JOIN appointment_statuses st ON a.status_id = st.id
        LEFT JOIN providers p ON a.provider_id = p.id
        WHERE a.status_id = :status_id
        ORDER BY a.visit_date DESC, a.start_time
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status_id' => $statusId]);
        return $stmt->fetchAll();
    }

    /**
     * Get appointment by ID
     */
    public function getAppointmentById($id)
    {
        $sql = "
        SELECT 
            a.id,
            a.requester_id,
            a.provider_id,
            a.service_id,
            a.status_id,
            a.visit_date,
            a.start_time,
            a.end_time,
            a.max_students,
            a.notes,
            a.created_at,
            s.service_name,
            st.status_name,
            p.name as provider_name
        FROM appointments a
        INNER JOIN services s ON a.service_id = s.id
        INNER JOIN appointment_statuses st ON a.status_id = st.id
        LEFT JOIN providers p ON a.provider_id = p.id
        WHERE a.id = :id
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Update an existing appointment
     */
    public function updateAppointment($data)
    {
        try {
            // Validate required fields
            if (empty($data['appointment_id'])) {
                return ['success' => false, 'message' => 'Appointment ID is required'];
            }

            if (empty($data['service_id'])) {
                return ['success' => false, 'message' => 'Service type is required'];
            }

            if (empty($data['status_id'])) {
                return ['success' => false, 'message' => 'Status is required'];
            }

            if (empty($data['visit_date'])) {
                return ['success' => false, 'message' => 'Visit date is required'];
            }

            if (empty($data['start_time'])) {
                return ['success' => false, 'message' => 'Start time is required'];
            }

            if (empty($data['end_time'])) {
                return ['success' => false, 'message' => 'End time is required'];
            }

            if (empty($data['max_students'])) {
                return ['success' => false, 'message' => 'Maximum students is required'];
            }

            // Update appointment
            $sql = "UPDATE appointments 
                SET provider_id = :provider_id,
                    service_id = :service_id,
                    status_id = :status_id,
                    visit_date = :visit_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    max_students = :max_students,
                    notes = :notes
                WHERE id = :appointment_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':appointment_id' => $data['appointment_id'],
                ':provider_id' => $data['provider_id'] ?? null,
                ':service_id' => $data['service_id'],
                ':status_id' => $data['status_id'],
                ':visit_date' => $data['visit_date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':max_students' => $data['max_students'],
                ':notes' => $data['notes'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Appointment updated successfully',
                'appointment_id' => $data['appointment_id']
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get total count of appointments
     */
    public function getTotalAppointmentsCount()
    {
        $sql = "SELECT COUNT(*) as total FROM appointments";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get pending consultations count (appointments with status = 'Scheduled' or 'Confirmed')
     */
    public function getPendingConsultationsCount()
    {
        $sql = "SELECT COUNT(*) as total 
            FROM appointments a
            INNER JOIN appointment_statuses st ON a.status_id = st.id
            WHERE st.status_name IN ('Scheduled', 'Confirmed')";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Delete an appointment
     */
    public function deleteAppointment($id)
    {
        try {
            // Check if appointment exists
            $checkSql = "SELECT id FROM appointments WHERE id = :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);

            if (!$checkStmt->fetch()) {
                return ['success' => false, 'message' => 'Appointment not found'];
            }

            // Delete the appointment
            $sql = "DELETE FROM appointments WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            return [
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
