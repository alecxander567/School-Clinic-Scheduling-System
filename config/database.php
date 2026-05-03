<?php
require_once __DIR__ . '/env.php';
Env::load();

$host     = Env::get('DB_HOST');
$dbname   = Env::get('DB_NAME');
$username = Env::get('DB_USER');
$password = Env::get('DB_PASS');
$port     = Env::get('DB_PORT', '3306');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
