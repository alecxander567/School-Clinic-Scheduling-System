<?php
require_once __DIR__ . '/../config/database.php';

class SchedulingSystem
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTotalAppointments()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM appointments");
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getTodayAppointments()
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE DATE(schedule_date) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getPendingAppointments()
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getRecentAppointments($limit = 5)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM appointments ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

$scheduling = new SchedulingSystem($pdo);
