<?php
class DentalRecordController
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    // Get all dental visits
    public function getAllDentalRecords()
    {
        $query = "SELECT DISTINCT 
                         v.id as visit_id,
                         v.visit_date,
                         v.diagnosis,
                         v.treatment,
                         s.id as student_id,
                         s.first_name,
                         s.last_name,
                         s.student_number,
                         s.course,
                         s.year_level,
                         GROUP_CONCAT(dp.procedure_name SEPARATOR ', ') as procedures,
                         GROUP_CONCAT(dp.description SEPARATOR ' | ') as procedure_details
                  FROM visits v
                  JOIN appointments a ON v.appointment_id = a.id
                  JOIN requesters r ON a.requester_id = r.id
                  JOIN students s ON r.student_id = s.id
                  LEFT JOIN dental_procedures dp ON v.id = dp.visit_id
                  GROUP BY v.id
                  ORDER BY v.visit_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create new dental visit with procedures
    public function saveDentalRecord($data)
    {
        try {
            $this->db->beginTransaction();

            // First, check if there's a requester entry for this student
            $requesterQuery = "SELECT id FROM requesters WHERE student_id = :student_id LIMIT 1";
            $requesterStmt = $this->db->prepare($requesterQuery);
            $requesterStmt->execute([':student_id' => $data['student_id']]);
            $requester = $requesterStmt->fetch(PDO::FETCH_ASSOC);

            if (!$requester) {
                // Create a requester entry for this student
                $insertRequester = "INSERT INTO requesters (student_id) VALUES (:student_id)";
                $insertStmt = $this->db->prepare($insertRequester);
                $insertStmt->execute([':student_id' => $data['student_id']]);
                $requester_id = $this->db->lastInsertId();
            } else {
                $requester_id = $requester['id'];
            }

            // Parse the visit date
            $visit_datetime = new DateTime($data['visit_date']);
            $slot_date = $visit_datetime->format('Y-m-d');
            $start_time = $visit_datetime->format('H:i:s');
            // Set end time as 1 hour after start time
            $end_datetime = clone $visit_datetime;
            $end_time = $end_datetime->modify('+1 hour')->format('H:i:s');

            // Get provider ID (use the first provider)
            $providerQuery = "SELECT id FROM providers LIMIT 1";
            $providerStmt = $this->db->prepare($providerQuery);
            $providerStmt->execute();
            $provider = $providerStmt->fetch(PDO::FETCH_ASSOC);
            $provider_id = $provider['id'];

            // Create appointment slot
            $slotQuery = "INSERT INTO appointment_slots 
                         (provider_id, slot_date, start_time, end_time, max_patients) 
                         VALUES 
                         (:provider_id, :slot_date, :start_time, :end_time, :max_patients)";

            $slotStmt = $this->db->prepare($slotQuery);
            $slotStmt->execute([
                ':provider_id' => $provider_id,
                ':slot_date' => $slot_date,
                ':start_time' => $start_time,
                ':end_time' => $end_time,
                ':max_patients' => 10
            ]);

            $slot_id = $this->db->lastInsertId();

            // Get completed status ID
            $statusQuery = "SELECT id FROM appointment_statuses WHERE status_name = 'completed' LIMIT 1";
            $statusStmt = $this->db->prepare($statusQuery);
            $statusStmt->execute();
            $status = $statusStmt->fetch(PDO::FETCH_ASSOC);
            $status_id = $status['id'];

            // Create appointment using requester_id
            $appointmentQuery = "INSERT INTO appointments 
                                (requester_id, slot_id, status_id, notes) 
                                VALUES 
                                (:requester_id, :slot_id, :status_id, :notes)";

            $appointmentStmt = $this->db->prepare($appointmentQuery);
            $appointmentResult = $appointmentStmt->execute([
                ':requester_id' => $requester_id,
                ':slot_id' => $slot_id,
                ':status_id' => $status_id,
                ':notes' => 'Dental consultation - ' . ($data['diagnosis'] ?? 'No diagnosis')
            ]);

            if (!$appointmentResult) {
                $error = $appointmentStmt->errorInfo();
                throw new Exception("Failed to create appointment: " . ($error[2] ?? 'Unknown error'));
            }

            $appointment_id = $this->db->lastInsertId();

            // Create visit linked to appointment
            $query = "INSERT INTO visits 
                      (appointment_id, visit_date, diagnosis, treatment) 
                      VALUES 
                      (:appointment_id, :visit_date, :diagnosis, :treatment)";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':appointment_id' => $appointment_id,
                ':visit_date' => $data['visit_date'],
                ':diagnosis' => $data['diagnosis'] ?? null,
                ':treatment' => $data['treatment'] ?? null
            ]);

            if (!$result) {
                $error = $stmt->errorInfo();
                throw new Exception("Failed to create visit record: " . ($error[2] ?? 'Unknown error'));
            }

            $visit_id = $this->db->lastInsertId();

            // Insert dental procedures if any
            if (!empty($data['procedures']) && is_array($data['procedures'])) {
                $procQuery = "INSERT INTO dental_procedures (visit_id, procedure_name, description) 
                              VALUES (:visit_id, :procedure_name, :description)";
                $procStmt = $this->db->prepare($procQuery);

                foreach ($data['procedures'] as $procedure) {
                    if (!empty($procedure['name'])) {
                        $procStmt->execute([
                            ':visit_id' => $visit_id,
                            ':procedure_name' => $procedure['name'],
                            ':description' => $procedure['description'] ?? null
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving dental record: " . $e->getMessage());
            return false;
        }
    }

    // Delete dental record - Completely rewritten to work with foreign keys
    public function deleteDentalRecord($visit_id)
    {
        try {
            // First, get all related IDs
            $getIdsQuery = "SELECT v.id as visit_id, v.appointment_id, 
                                   GROUP_CONCAT(dp.id) as procedure_ids
                            FROM visits v
                            LEFT JOIN dental_procedures dp ON v.id = dp.visit_id
                            WHERE v.id = :visit_id
                            GROUP BY v.id";

            $getIdsStmt = $this->db->prepare($getIdsQuery);
            $getIdsStmt->execute([':visit_id' => $visit_id]);
            $record = $getIdsStmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                error_log("Visit record not found for ID: " . $visit_id);
                return false;
            }

            error_log("Deleting visit_id: " . $record['visit_id'] . ", appointment_id: " . $record['appointment_id']);

            // Start transaction
            $this->db->beginTransaction();

            // 1. Delete dental procedures
            $deleteProcedures = "DELETE FROM dental_procedures WHERE visit_id = :visit_id";
            $procStmt = $this->db->prepare($deleteProcedures);
            $procStmt->execute([':visit_id' => $record['visit_id']]);
            error_log("Deleted procedures for visit_id: " . $record['visit_id']);

            // 2. Delete the visit
            $deleteVisit = "DELETE FROM visits WHERE id = :visit_id";
            $visitStmt = $this->db->prepare($deleteVisit);
            $visitResult = $visitStmt->execute([':visit_id' => $record['visit_id']]);

            if (!$visitResult) {
                throw new Exception("Failed to delete visit record");
            }
            error_log("Deleted visit: " . $record['visit_id']);

            // 3. Delete the appointment if it exists
            if ($record['appointment_id']) {
                $deleteAppointment = "DELETE FROM appointments WHERE id = :appointment_id";
                $apptStmt = $this->db->prepare($deleteAppointment);
                $apptStmt->execute([':appointment_id' => $record['appointment_id']]);
                error_log("Deleted appointment: " . $record['appointment_id']);
            }

            $this->db->commit();
            error_log("Successfully deleted dental record: " . $visit_id);
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting dental record: " . $e->getMessage());
            return false;
        }
    }

    // Update existing dental visit and procedures
    public function updateDentalRecord($visit_id, $data)
    {
        try {
            $this->db->beginTransaction();

            // Update visit
            $query = "UPDATE visits 
                      SET visit_date = :visit_date,
                          diagnosis = :diagnosis,
                          treatment = :treatment
                      WHERE id = :visit_id";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':visit_id' => $visit_id,
                ':visit_date' => $data['visit_date'],
                ':diagnosis' => $data['diagnosis'] ?? null,
                ':treatment' => $data['treatment'] ?? null
            ]);

            if (!$result) {
                throw new Exception("Failed to update visit record");
            }

            // Delete existing procedures
            $deleteQuery = "DELETE FROM dental_procedures WHERE visit_id = :visit_id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->execute([':visit_id' => $visit_id]);

            // Insert updated procedures
            if (!empty($data['procedures']) && is_array($data['procedures'])) {
                $procQuery = "INSERT INTO dental_procedures (visit_id, procedure_name, description) 
                              VALUES (:visit_id, :procedure_name, :description)";
                $procStmt = $this->db->prepare($procQuery);

                foreach ($data['procedures'] as $procedure) {
                    if (!empty($procedure['name'])) {
                        $procStmt->execute([
                            ':visit_id' => $visit_id,
                            ':procedure_name' => $procedure['name'],
                            ':description' => $procedure['description'] ?? null
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating dental record: " . $e->getMessage());
            return false;
        }
    }

    // Get students without dental visits
    public function getStudentsWithoutDentalRecords()
    {
        $query = "SELECT s.id, s.first_name, s.last_name, s.student_number, s.course, s.year_level
                  FROM students s
                  LEFT JOIN requesters r ON s.id = r.student_id
                  LEFT JOIN appointments a ON r.id = a.requester_id
                  LEFT JOIN visits v ON a.id = v.appointment_id
                  WHERE v.id IS NULL
                  GROUP BY s.id
                  ORDER BY s.first_name, s.last_name";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all students for dropdown
    public function getAllStudents()
    {
        $query = "SELECT id, first_name, last_name, student_number FROM students ORDER BY first_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get dental record statistics
    public function getDentalRecordStats()
    {
        $stats = [];

        $query = "SELECT COUNT(DISTINCT v.id) as total_visits FROM visits v";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_visits'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_visits'];

        $query = "SELECT COUNT(DISTINCT r.student_id) as unique_students 
                  FROM requesters r
                  JOIN appointments a ON r.id = a.requester_id
                  JOIN visits v ON a.id = v.appointment_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['unique_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['unique_students'] ?? 0;

        $query = "SELECT COUNT(*) as total_procedures FROM dental_procedures";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_procedures'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_procedures'];

        return $stats;
    }

    // Get common dental procedures
    public function getCommonProcedures()
    {
        return [
            'Oral Prophylaxis (Dental Cleaning)',
            'Tooth Extraction',
            'Dental Filling',
            'Root Canal Treatment',
            'Dental Crown',
            'Dental Bridge',
            'Orthodontic Consultation',
            'Fluoride Treatment',
            'Tooth Whitening',
            'Wisdom Tooth Extraction',
            'Gum Treatment',
            'Dental Sealants'
        ];
    }

    // Get single dental record by ID
    public function getDentalRecordById($visit_id)
    {
        $query = "SELECT DISTINCT 
                         v.*,
                         s.id as student_id,
                         s.first_name,
                         s.last_name,
                         s.student_number,
                         s.course,
                         s.year_level,
                         GROUP_CONCAT(dp.id) as procedure_ids,
                         GROUP_CONCAT(dp.procedure_name SEPARATOR '||') as procedure_names,
                         GROUP_CONCAT(dp.description SEPARATOR '||') as procedure_descriptions
                  FROM visits v
                  JOIN appointments a ON v.appointment_id = a.id
                  JOIN requesters r ON a.requester_id = r.id
                  JOIN students s ON r.student_id = s.id
                  LEFT JOIN dental_procedures dp ON v.id = dp.visit_id
                  WHERE v.id = :visit_id
                  GROUP BY v.id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':visit_id' => $visit_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
