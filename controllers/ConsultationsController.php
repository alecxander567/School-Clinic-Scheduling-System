<?php
class ConsultationsController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Fetch filtered consultations (completed appointments).
     * Set $dateFrom and $dateTo to null to show all appointments
     */
    public function getConsultations(?string $dateFrom = null, ?string $dateTo = null, string $filterType = 'all', string $search = ''): array
    {
        $sql = "
            SELECT
                a.id,
                a.visit_date,
                a.start_time,
                a.end_time,
                a.notes AS appointment_notes,
                a.created_at,
                a.registered_students,
                a.max_students,
                s.service_name,
                st.status_name,
                p.id   AS provider_id,
                p.name AS provider_name,
                p.specialization,
                COALESCE(a.registered_students, 0) AS total_students,
                GROUP_CONCAT(
                    CONCAT(s2.first_name, ' ', s2.last_name, ' (Priority: ', aq.priority_number, ')')
                    SEPARATOR '||'
                ) AS student_list
            FROM appointments a
            INNER JOIN services s              ON a.service_id  = s.id
            INNER JOIN appointment_statuses st ON a.status_id   = st.id
            LEFT  JOIN providers p             ON a.provider_id = p.id
            LEFT  JOIN appointment_queues aq   ON a.id = aq.appointment_id AND aq.status = 'pending'
            LEFT  JOIN students s2             ON aq.student_id = s2.id
            WHERE LOWER(st.status_name) = 'completed'
              AND a.visit_date IS NOT NULL
        ";

        // Add date range filter only if both dates are provided
        if ($dateFrom !== null && $dateTo !== null) {
            $sql .= " AND a.visit_date BETWEEN :date_from AND :date_to";
        }

        // Add type filter
        if ($filterType === 'doctor') {
            $sql .= " AND (
                        LOWER(p.name) LIKE '%doctor%' 
                        OR LOWER(p.name) LIKE '%dr.%'
                        OR LOWER(p.specialization) LIKE '%doctor%' 
                        OR LOWER(p.specialization) LIKE '%physician%'
                        OR LOWER(p.specialization) LIKE '%general medicine%'
                    )";
        } elseif ($filterType === 'dentist') {
            $sql .= " AND (
                        LOWER(p.name) LIKE '%dentist%' 
                        OR LOWER(p.specialization) LIKE '%dentist%' 
                        OR LOWER(p.specialization) LIKE '%dental%'
                    )";
        }

        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search 
                     OR s.service_name LIKE :search 
                     OR s2.first_name LIKE :search 
                     OR s2.last_name LIKE :search)";
        }

        $sql .= " GROUP BY a.id ORDER BY a.visit_date DESC, a.start_time DESC";

        $stmt = $this->pdo->prepare($sql);
        $params = [];

        // Add date parameters only if both dates are provided
        if ($dateFrom !== null && $dateTo !== null) {
            $params[':date_from'] = $dateFrom;
            $params[':date_to'] = $dateTo;
        }

        if (!empty($search)) {
            $params[':search'] = "%$search%";
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch summary statistics for the given date range.
     * Set $dateFrom and $dateTo to null to show all appointments
     */
    public function getStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "
            SELECT
                COUNT(DISTINCT a.id)                    AS total_consultations,
                COUNT(DISTINCT p.id)                    AS total_providers,
                SUM(COALESCE(a.registered_students, 0)) AS total_students_served,
                AVG(COALESCE(a.registered_students, 0)) AS avg_students_per_session
            FROM appointments a
            INNER JOIN appointment_statuses st ON a.status_id = st.id
            LEFT  JOIN providers p             ON a.provider_id = p.id
            WHERE LOWER(st.status_name) = 'completed'
              AND a.visit_date IS NOT NULL
        ";

        // Add date range filter only if both dates are provided
        if ($dateFrom !== null && $dateTo !== null) {
            $sql .= " AND a.visit_date BETWEEN :date_from AND :date_to";
        }

        $stmt = $this->pdo->prepare($sql);
        $params = [];

        if ($dateFrom !== null && $dateTo !== null) {
            $params[':date_from'] = $dateFrom;
            $params[':date_to'] = $dateTo;
        }

        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [
            'total_consultations' => 0,
            'total_providers' => 0,
            'total_students_served' => 0,
            'avg_students_per_session' => 0
        ];
    }

    /**
     * Get a single consultation by ID with full details
     */
    public function getConsultationDetails(int $id): ?array
    {
        $sql = "
            SELECT
                a.id,
                a.visit_date,
                a.start_time,
                a.end_time,
                a.notes AS appointment_notes,
                a.created_at,
                a.registered_students,
                a.max_students,
                s.id AS service_id,
                s.service_name,
                s.description AS service_description,
                st.status_name,
                p.id AS provider_id,
                p.name AS provider_name,
                p.specialization,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        s2.first_name, ' ', s2.last_name, 
                        ' (', s2.student_number, ') - Priority: ', aq.priority_number
                    ) ORDER BY aq.priority_number SEPARATOR '||'
                ) AS student_list,
                COUNT(DISTINCT aq.id) AS total_registered
            FROM appointments a
            INNER JOIN services s ON a.service_id = s.id
            INNER JOIN appointment_statuses st ON a.status_id = st.id
            LEFT JOIN providers p ON a.provider_id = p.id
            LEFT JOIN appointment_queues aq ON a.id = aq.appointment_id AND aq.status = 'pending'
            LEFT JOIN students s2 ON aq.student_id = s2.id
            WHERE a.id = :id
            GROUP BY a.id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Determine whether a provider is a doctor or dentist.
     */
    public static function getProviderType(array $consultation): string
    {
        $spec = strtolower($consultation['specialization'] ?? '');
        $name = strtolower($consultation['provider_name'] ?? '');

        // Check for doctor
        if (
            str_contains($name, 'doctor') ||
            str_contains($name, 'dr.') ||
            str_contains($spec, 'doctor') ||
            str_contains($spec, 'physician') ||
            str_contains($spec, 'general medicine')
        ) {
            return 'doctor';
        }

        // Check for dentist
        if (
            str_contains($name, 'dentist') ||
            str_contains($spec, 'dentist') ||
            str_contains($spec, 'dental')
        ) {
            return 'dentist';
        }

        // Default based on name if no specialization
        if (str_contains($name, 'dental')) {
            return 'dentist';
        }

        return 'doctor';
    }
}
