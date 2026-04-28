<?php
require_once __DIR__ . '/../config/database.php';

class HealthRecordController
{
    private $pdo;
    private $auth;

    public function __construct($auth)
    {
        $this->auth = $auth;
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get health record by student ID
     */
    public function getHealthRecordByStudentId($studentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM health_records WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all health records with student information
     */
    public function getAllHealthRecords()
    {
        $stmt = $this->pdo->prepare("
            SELECT hr.*, s.first_name, s.last_name, s.student_number, s.course, s.year_level
            FROM health_records hr
            JOIN students s ON hr.student_id = s.id
            ORDER BY s.last_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update health record for a student
     */
    public function saveHealthRecord($studentId, $allergies, $medicalHistory)
    {
        // Check if record exists
        $existing = $this->getHealthRecordByStudentId($studentId);

        if ($existing) {
            // Update existing record
            $stmt = $this->pdo->prepare("
                UPDATE health_records 
                SET allergies = ?, medical_history = ?
                WHERE student_id = ?
            ");
            return $stmt->execute([$allergies, $medicalHistory, $studentId]);
        } else {
            // Insert new record
            $stmt = $this->pdo->prepare("
                INSERT INTO health_records (student_id, allergies, medical_history)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$studentId, $allergies, $medicalHistory]);
        }
    }

    /**
     * Delete health record
     */
    public function deleteHealthRecord($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM health_records WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get students without health records
     */
    public function getStudentsWithoutHealthRecords()
    {
        $stmt = $this->pdo->prepare("
            SELECT s.* 
            FROM students s
            LEFT JOIN health_records hr ON s.id = hr.student_id
            WHERE hr.id IS NULL
            ORDER BY s.last_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update health record by record ID
     */
    public function updateHealthRecord($recordId, $allergies, $medicalHistory)
    {
        $stmt = $this->pdo->prepare("
            UPDATE health_records 
            SET allergies = ?, medical_history = ?
            WHERE id = ?
        ");
        return $stmt->execute([$allergies, $medicalHistory, $recordId]);
    }
}
