<?php
require_once __DIR__ . '/../config/database.php';

class ReportsController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get daily report data
     */
    public function getDailyReport($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Total appointments for the day
        $appointments = $this->getAppointmentsByDate($date);

        // Total students served
        $studentsServed = $this->getStudentsServedByDate($date);

        // Service breakdown
        $serviceBreakdown = $this->getServiceBreakdownByDate($date);

        // Common procedures (for dental)
        $commonProcedures = $this->getCommonProceduresByDate($date);

        // Hourly distribution
        $hourlyDistribution = $this->getHourlyDistribution($date);

        // Peak hours
        $peakHours = $this->getPeakHours($date);

        // Provider performance
        $providerPerformance = $this->getProviderPerformanceByDate($date);

        return [
            'date' => $date,
            'total_appointments' => count($appointments),
            'students_served' => $studentsServed,
            'service_breakdown' => $serviceBreakdown,
            'common_procedures' => $commonProcedures,
            'hourly_distribution' => $hourlyDistribution,
            'peak_hours' => $peakHours,
            'provider_performance' => $providerPerformance,
            'appointments' => $appointments
        ];
    }

    /**
     * Get monthly report data
     */
    public function getMonthlyReport($year = null, $month = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }

        // Daily breakdown for the month
        $dailyBreakdown = $this->getDailyBreakdownByMonth($year, $month);

        // Monthly totals
        $monthlyTotals = $this->getMonthlyTotals($year, $month);

        // Service popularity over month
        $servicePopularity = $this->getServicePopularityByMonth($year, $month);

        // Student visit frequency
        $studentVisitFrequency = $this->getStudentVisitFrequency($year, $month);

        // Day-of-week analysis
        $dayOfWeekAnalysis = $this->getDayOfWeekAnalysis($year, $month);

        // Trend analysis (compare with previous months)
        $trendAnalysis = $this->getTrendAnalysis($year, $month);

        // Top students by visit count
        $topStudents = $this->getTopStudentsByVisits($year, $month);

        // Procedure statistics (for dental)
        $procedureStats = $this->getMonthlyProcedureStats($year, $month);

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            'total_days_active' => count($dailyBreakdown),
            'daily_breakdown' => $dailyBreakdown,
            'monthly_totals' => $monthlyTotals,
            'service_popularity' => $servicePopularity,
            'student_visit_frequency' => $studentVisitFrequency,
            'day_of_week_analysis' => $dayOfWeekAnalysis,
            'trend_analysis' => $trendAnalysis,
            'top_students' => $topStudents,
            'procedure_stats' => $procedureStats
        ];
    }

    /**
     * Get appointments by date
     */
    private function getAppointmentsByDate($date)
    {
        $sql = "
            SELECT 
                a.id,
                a.visit_date,
                a.start_time,
                a.end_time,
                a.max_students,
                a.notes,
                COALESCE(s.service_name, 'Unknown Service') as service_name,
                COALESCE(st.status_name, 'Unknown Status') as status_name,
                COALESCE(p.name, 'Unassigned') as provider_name,
                (SELECT COUNT(*) FROM visits v WHERE v.appointment_id = a.id) as actual_students_served
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            LEFT JOIN appointment_statuses st ON a.status_id = st.id
            LEFT JOIN providers p ON a.provider_id = p.id
            WHERE DATE(a.visit_date) = :date
            ORDER BY a.start_time
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Log the query and results
        error_log("Daily Report Query for date: $date, found " . count($results) . " appointments");

        return $results;
    }

    /**
     * Get students served on a specific date
     */
    private function getStudentsServedByDate($date)
    {
        $sql = "
            SELECT COUNT(DISTINCT v.student_id) as total
            FROM visits v
            WHERE DATE(v.visit_date) = :date
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get service breakdown for a specific date
     */
    private function getServiceBreakdownByDate($date)
    {
        $sql = "
            SELECT 
                COALESCE(s.service_name, 'Unknown Service') as service_name,
                COUNT(DISTINCT a.id) as appointment_count,
                COUNT(DISTINCT v.student_id) as students_served
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            LEFT JOIN visits v ON v.appointment_id = a.id AND DATE(v.visit_date) = :date
            WHERE DATE(a.visit_date) = :date
            GROUP BY s.id, s.service_name
            ORDER BY appointment_count DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get common procedures for a specific date
     */
    private function getCommonProceduresByDate($date)
    {
        $sql = "
            SELECT 
                dp.procedure_name,
                COUNT(*) as procedure_count
            FROM dental_procedures dp
            INNER JOIN visits v ON dp.visit_id = v.id
            WHERE DATE(v.visit_date) = :date
            GROUP BY dp.procedure_name
            ORDER BY procedure_count DESC
            LIMIT 10
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get hourly distribution of appointments
     */
    private function getHourlyDistribution($date)
    {
        $sql = "
            SELECT 
                HOUR(a.start_time) as hour,
                COUNT(*) as appointment_count
            FROM appointments a
            WHERE DATE(a.visit_date) = :date
                AND a.start_time IS NOT NULL
            GROUP BY HOUR(a.start_time)
            ORDER BY hour
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $distribution = [];
        for ($i = 8; $i <= 17; $i++) {
            $distribution[$i] = 0;
        }

        foreach ($results as $result) {
            $distribution[(int)$result['hour']] = (int)$result['appointment_count'];
        }

        return $distribution;
    }

    /**
     * Get peak hours for a specific date
     */
    private function getPeakHours($date)
    {
        $sql = "
            SELECT 
                CONCAT(
                    DATE_FORMAT(MIN(a.start_time), '%l %p'), 
                    ' - ', 
                    DATE_FORMAT(MAX(a.end_time), '%l %p')
                ) as hour_range,
                COUNT(*) as appointment_count
            FROM appointments a
            WHERE DATE(a.visit_date) = :date
                AND a.start_time IS NOT NULL
            GROUP BY HOUR(a.start_time)
            ORDER BY appointment_count DESC
            LIMIT 3
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get provider performance for a specific date
     */
    private function getProviderPerformanceByDate($date)
    {
        $sql = "
            SELECT 
                COALESCE(p.name, 'Unassigned') as provider_name,
                COUNT(DISTINCT a.id) as appointments_scheduled,
                SUM(CASE WHEN v.id IS NOT NULL THEN 1 ELSE 0 END) as appointments_completed,
                COUNT(DISTINCT v.student_id) as students_served
            FROM appointments a
            LEFT JOIN providers p ON a.provider_id = p.id
            LEFT JOIN visits v ON v.appointment_id = a.id
            WHERE DATE(a.visit_date) = :date
            GROUP BY p.id, p.name
            ORDER BY students_served DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get daily breakdown for a specific month
     */
    private function getDailyBreakdownByMonth($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                DATE(a.visit_date) as date,
                COUNT(DISTINCT a.id) as total_appointments,
                COUNT(DISTINCT v.student_id) as students_served
            FROM appointments a
            LEFT JOIN visits v ON v.appointment_id = a.id AND DATE(v.visit_date) = DATE(a.visit_date)
            WHERE DATE(a.visit_date) BETWEEN :start_date AND :end_date
                AND a.visit_date IS NOT NULL
            GROUP BY DATE(a.visit_date)
            ORDER BY date
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dailyBreakdown = [];
        $daysInMonth = date('t', strtotime($startDate));

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%s-%s-%02d', $year, $month, $day);
            $dailyBreakdown[$date] = [
                'appointments' => 0,
                'students_served' => 0
            ];
        }

        foreach ($results as $result) {
            $dailyBreakdown[$result['date']] = [
                'appointments' => (int)$result['total_appointments'],
                'students_served' => (int)$result['students_served']
            ];
        }

        return $dailyBreakdown;
    }

    /**
     * Get monthly totals
     */
    private function getMonthlyTotals($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                COUNT(DISTINCT a.id) as total_appointments,
                COUNT(DISTINCT v.id) as total_visits,
                COUNT(DISTINCT v.student_id) as unique_students,
                COALESCE(AVG(a.max_students), 0) as avg_capacity,
                COUNT(DISTINCT p.id) as active_providers
            FROM appointments a
            LEFT JOIN visits v ON v.appointment_id = a.id
            LEFT JOIN providers p ON a.provider_id = p.id
            WHERE DATE(a.visit_date) BETWEEN :start_date AND :end_date
                AND a.visit_date IS NOT NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate utilization rate
        $sql = "
            SELECT 
                SUM(a.max_students) as total_capacity,
                COUNT(v.id) as actual_usage
            FROM appointments a
            LEFT JOIN visits v ON v.appointment_id = a.id
            WHERE DATE(a.visit_date) BETWEEN :start_date AND :end_date
                AND a.visit_date IS NOT NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $capacityData = $stmt->fetch(PDO::FETCH_ASSOC);
        $utilizationRate = 0;
        if ($capacityData['total_capacity'] && $capacityData['total_capacity'] > 0) {
            $utilizationRate = ($capacityData['actual_usage'] / $capacityData['total_capacity']) * 100;
        }

        $result['utilization_rate'] = round($utilizationRate, 2);

        return $result;
    }

    /**
     * Get service popularity by month
     */
    private function getServicePopularityByMonth($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                COALESCE(s.service_name, 'Unknown Service') as service_name,
                COUNT(DISTINCT a.id) as appointment_count,
                COUNT(DISTINCT v.student_id) as students_served
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            LEFT JOIN visits v ON v.appointment_id = a.id
            WHERE DATE(a.visit_date) BETWEEN :start_date AND :end_date
                AND a.visit_date IS NOT NULL
            GROUP BY s.id, s.service_name
            ORDER BY appointment_count DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get student visit frequency
     */
    private function getStudentVisitFrequency($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                v.student_id,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                COUNT(*) as visit_count
            FROM visits v
            INNER JOIN students s ON v.student_id = s.id
            WHERE DATE(v.visit_date) BETWEEN :start_date AND :end_date
            GROUP BY v.student_id, s.first_name, s.last_name
            ORDER BY visit_count DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate frequency distribution
        $frequency = [
            '1_visit' => 0,
            '2_visits' => 0,
            '3_visits' => 0,
            '4_plus_visits' => 0
        ];

        foreach ($visits as $visit) {
            if ($visit['visit_count'] == 1) {
                $frequency['1_visit']++;
            } elseif ($visit['visit_count'] == 2) {
                $frequency['2_visits']++;
            } elseif ($visit['visit_count'] == 3) {
                $frequency['3_visits']++;
            } else {
                $frequency['4_plus_visits']++;
            }
        }

        return [
            'distribution' => $frequency,
            'top_visitors' => array_slice($visits, 0, 10)
        ];
    }

    /**
     * Get day of week analysis
     */
    private function getDayOfWeekAnalysis($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                DAYOFWEEK(a.visit_date) as day_num,
                DAYNAME(a.visit_date) as day_name,
                COUNT(*) as appointment_count
            FROM appointments a
            WHERE DATE(a.visit_date) BETWEEN :start_date AND :end_date
                AND a.visit_date IS NOT NULL
            GROUP BY DAYOFWEEK(a.visit_date), DAYNAME(a.visit_date)
            ORDER BY day_num
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $analysis = [];

        foreach ($days as $day) {
            $analysis[$day] = 0;
        }

        foreach ($results as $result) {
            $analysis[$result['day_name']] = (int)$result['appointment_count'];
        }

        return $analysis;
    }

    /**
     * Get trend analysis (compare with previous months)
     */
    private function getTrendAnalysis($year, $month)
    {
        $currentStart = "$year-$month-01";
        $currentEnd = date('Y-m-t', strtotime($currentStart));

        // Previous month
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        }
        $prevStart = "$prevYear-$prevMonth-01";
        $prevEnd = date('Y-m-t', strtotime($prevStart));

        // Current month stats
        $sql = "
            SELECT 
                COUNT(DISTINCT a.id) as appointments,
                COUNT(DISTINCT v.student_id) as students
            FROM appointments a
            LEFT JOIN visits v ON v.appointment_id = a.id
            WHERE DATE(a.visit_date) BETWEEN :start AND :end
                AND a.visit_date IS NOT NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $currentStart, ':end' => $currentEnd]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        // Previous month stats
        $stmt->execute([':start' => $prevStart, ':end' => $prevEnd]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate trends
        $appointmentTrend = 0;
        if ($previous['appointments'] > 0) {
            $appointmentTrend = (($current['appointments'] - $previous['appointments']) / $previous['appointments']) * 100;
        }

        $studentTrend = 0;
        if ($previous['students'] > 0) {
            $studentTrend = (($current['students'] - $previous['students']) / $previous['students']) * 100;
        }

        return [
            'current_month' => [
                'appointments' => (int)$current['appointments'],
                'students' => (int)$current['students']
            ],
            'previous_month' => [
                'appointments' => (int)$previous['appointments'],
                'students' => (int)$previous['students']
            ],
            'trends' => [
                'appointments' => round($appointmentTrend, 2),
                'students' => round($studentTrend, 2)
            ]
        ];
    }

    /**
     * Get top students by visits
     */
    private function getTopStudentsByVisits($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                s.student_number,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                s.course,
                s.year_level,
                COUNT(v.id) as visit_count
            FROM students s
            INNER JOIN visits v ON v.student_id = s.id
            WHERE DATE(v.visit_date) BETWEEN :start_date AND :end_date
            GROUP BY s.id, s.student_number, s.first_name, s.last_name, s.course, s.year_level
            ORDER BY visit_count DESC
            LIMIT 10
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get monthly procedure statistics
     */
    private function getMonthlyProcedureStats($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "
            SELECT 
                dp.procedure_name,
                COUNT(*) as procedure_count,
                COUNT(DISTINCT v.student_id) as patients_treated
            FROM dental_procedures dp
            INNER JOIN visits v ON dp.visit_id = v.id
            WHERE DATE(v.visit_date) BETWEEN :start_date AND :end_date
            GROUP BY dp.procedure_name
            ORDER BY procedure_count DESC
            LIMIT 15
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Export report data to CSV
     */
    public function exportToCSV($data, $type = 'daily')
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if ($type == 'daily') {
            // Write daily report headers
            fputcsv($output, ['Daily Report - ' . $data['date']]);
            fputcsv($output, []);
            fputcsv($output, ['Summary']);
            fputcsv($output, ['Total Appointments', $data['total_appointments']]);
            fputcsv($output, ['Students Served', $data['students_served']]);
            fputcsv($output, []);
            fputcsv($output, ['Service Breakdown']);
            fputcsv($output, ['Service Name', 'Appointments', 'Students Served']);
            foreach ($data['service_breakdown'] as $service) {
                fputcsv($output, [$service['service_name'], $service['appointment_count'], $service['students_served']]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Peak Hours']);
            fputcsv($output, ['Time Range', 'Appointments']);
            foreach ($data['peak_hours'] as $peak) {
                fputcsv($output, [$peak['hour_range'], $peak['appointment_count']]);
            }
        } else {
            // Write monthly report headers
            fputcsv($output, ['Monthly Report - ' . $data['month_name'] . ' ' . $data['year']]);
            fputcsv($output, []);
            fputcsv($output, ['Summary']);
            fputcsv($output, ['Total Appointments', $data['monthly_totals']['total_appointments']]);
            fputcsv($output, ['Total Visits', $data['monthly_totals']['total_visits']]);
            fputcsv($output, ['Unique Students', $data['monthly_totals']['unique_students']]);
            fputcsv($output, ['Utilization Rate', $data['monthly_totals']['utilization_rate'] . '%']);
            fputcsv($output, []);
            fputcsv($output, ['Service Popularity']);
            fputcsv($output, ['Service Name', 'Appointments', 'Students Served']);
            foreach ($data['service_popularity'] as $service) {
                fputcsv($output, [$service['service_name'], $service['appointment_count'], $service['students_served']]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Top Students']);
            fputcsv($output, ['Student #', 'Name', 'Course', 'Year Level', 'Visits']);
            foreach ($data['top_students'] as $student) {
                fputcsv($output, [$student['student_number'], $student['student_name'], $student['course'], $student['year_level'], $student['visit_count']]);
            }
        }

        fclose($output);
        exit();
    }
}
