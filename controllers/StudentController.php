<?php
class StudentController
{
    private $db;
    private $auth;
    private $user;
    private $userRole;

    public function __construct($auth)
    {
        $this->auth = $auth;
        $this->user = $auth->getCurrentUser();
        $this->userRole = $this->user['role'] ?? 'admin';

        // Get database connection correctly
        $this->initDatabase();
    }

    private function initDatabase()
    {
        try {
            // Require the database config file and get the PDO connection
            $dbConnection = require_once __DIR__ . '/../config/database.php';

            // The file returns the PDO object, but require_once returns 1 (true)
            // So we need to check if the variable $pdo exists in global scope
            global $pdo;

            if (isset($pdo) && $pdo instanceof PDO) {
                $this->db = $pdo;
            } else if ($dbConnection instanceof PDO) {
                $this->db = $dbConnection;
            } else {
                // Last resort: require the file without _once to get the return value
                $this->db = require __DIR__ . '/../config/database.php';
            }

            // Verify we have a valid PDO connection
            if (!$this->db instanceof PDO) {
                throw new Exception("Database connection failed: PDO object not available");
            }
        } catch (Exception $e) {
            die("Database initialization error: " . $e->getMessage());
        }
    }

    // Check if user has permission to add students
    public function hasAddPermission()
    {
        return in_array($this->userRole, ['admin', 'nurse']);
    }

    // Get user data for the view
    public function getUserData()
    {
        return [
            'user' => $this->user,
            'userRole' => $this->userRole,
            'userName' => $this->user['name'] ?? 'Admin User'
        ];
    }

    // Process student addition
    public function addStudent($postData)
    {
        $error = '';
        $success = '';
        $formData = [
            'student_number' => '',
            'first_name' => '',
            'last_name' => '',
            'course' => '',
            'year_level' => '',
            'contact_number' => ''
        ];

        // Check permission first
        if (!$this->hasAddPermission()) {
            $_SESSION['error_message'] = "You don't have permission to add students.";
            header('Location: ../dashboard.php');
            exit();
        }

        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate inputs
            $student_number = trim($postData['student_number'] ?? '');
            $first_name = trim($postData['first_name'] ?? '');
            $last_name = trim($postData['last_name'] ?? '');
            $course = trim($postData['course'] ?? '');
            $year_level = trim($postData['year_level'] ?? '');
            $contact_number = trim($postData['contact_number'] ?? '');

            // Store in form data for repopulation
            $formData = [
                'student_number' => $student_number,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'course' => $course,
                'year_level' => $year_level,
                'contact_number' => $contact_number
            ];

            // Validation
            $errors = [];

            if (empty($first_name)) {
                $errors[] = "First name is required.";
            }

            if (empty($last_name)) {
                $errors[] = "Last name is required.";
            }

            if (empty($student_number)) {
                $errors[] = "Student number is required.";
            }

            if (empty($course)) {
                $errors[] = "Course is required.";
            }

            if (empty($year_level)) {
                $errors[] = "Year level is required.";
            } elseif (!is_numeric($year_level) || $year_level < 1 || $year_level > 4) {
                $errors[] = "Year level must be between 1 and 4.";
            }

            if (empty($contact_number)) {
                $errors[] = "Contact number is required.";
            }

            // If no validation errors, proceed with database insertion
            if (empty($errors)) {
                try {
                    // Check if student number already exists
                    $checkQuery = "SELECT id FROM students WHERE student_number = :student_number";
                    $checkStmt = $this->db->prepare($checkQuery);
                    $checkStmt->execute([':student_number' => $student_number]);

                    if ($checkStmt->rowCount() > 0) {
                        $error = "A student with this student number already exists.";
                    } else {
                        // Insert new student
                        $query = "INSERT INTO students (student_number, first_name, last_name, course, year_level, contact_number) 
                                  VALUES (:student_number, :first_name, :last_name, :course, :year_level, :contact_number)";

                        $stmt = $this->db->prepare($query);
                        $result = $stmt->execute([
                            ':student_number' => $student_number,
                            ':first_name' => $first_name,
                            ':last_name' => $last_name,
                            ':course' => $course,
                            ':year_level' => $year_level,
                            ':contact_number' => $contact_number
                        ]);

                        if ($result) {
                            $success = "Student added successfully!";
                            // Clear form data on success
                            $formData = [
                                'student_number' => '',
                                'first_name' => '',
                                'last_name' => '',
                                'course' => '',
                                'year_level' => '',
                                'contact_number' => ''
                            ];

                            // Optional: redirect after 2 seconds
                            // header('refresh:2; url=list.php');
                        } else {
                            $error = "Failed to add student. Please try again.";
                        }
                    }
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = implode(" ", $errors);
            }
        }

        return [
            'error' => $error,
            'success' => $success,
            'formData' => $formData
        ];
    }

    // Get all students (for list page)
    public function getAllStudents()
    {
        try {
            // Check if database connection is valid
            if (!$this->db instanceof PDO) {
                error_log("Database connection not properly initialized");
                return [];
            }

            $query = "SELECT id, student_number, first_name, last_name, course, year_level, contact_number 
                      FROM students 
                      ORDER BY last_name, first_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result ?: [];
        } catch (PDOException $e) {
            error_log("Error getting students: " . $e->getMessage());
            return [];
        }
    }

    // Get student by ID
    public function getStudentById($id)
    {
        try {
            if (!$this->db instanceof PDO) {
                return null;
            }

            $query = "SELECT id, student_number, first_name, last_name, course, year_level, contact_number 
                      FROM students 
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student by ID: " . $e->getMessage());
            return null;
        }
    }

    // Delete a student
    public function deleteStudent($id)
    {
        try {
            $query = "DELETE FROM students WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting student: " . $e->getMessage());
            return false;
        }
    }

    // Update a student
    public function updateStudent($id, $postData)
    {
        try {
            $query = "UPDATE students 
                      SET student_number = :student_number,
                          first_name = :first_name,
                          last_name = :last_name,
                          course = :course,
                          year_level = :year_level,
                          contact_number = :contact_number
                      WHERE id = :id";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':student_number' => $postData['student_number'],
                ':first_name' => $postData['first_name'],
                ':last_name' => $postData['last_name'],
                ':course' => $postData['course'],
                ':year_level' => $postData['year_level'],
                ':contact_number' => $postData['contact_number']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating student: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total students count with optional time period
     */
    public function getTotalStudentsCount($period = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM students";

            if ($period === 'last_month') {
                $sql .= " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            }

            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total students count: " . $e->getMessage());
            return 0;
        }
    }
}
