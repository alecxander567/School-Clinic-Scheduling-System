<?php
require_once __DIR__ . '/../config/database.php';

class ProfileController
{
    private $pdo;
    private $auth;

    public function __construct($auth, $pdo)
    {
        $this->auth = $auth;
        $this->pdo = $pdo;
    }

    /**
     * Get user profile by ID
     */
    public function getProfile($userId)
    {
        $sql = "SELECT id, name, email, role_id, created_at FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update user profile (name and email)
     */
    public function updateProfile($userId, $name, $email)
    {
        try {
            // Check if email already exists for another user
            $checkSql = "SELECT id FROM users WHERE email = :email AND id != :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':email' => $email, ':id' => $userId]);

            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email address is already used by another account'
                ];
            }

            // Update profile
            $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':id' => $userId
            ]);

            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred'
            ];
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $currentPassword, $newPassword, $confirmPassword)
    {
        try {
            // Validate passwords match
            if ($newPassword !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'New passwords do not match'
                ];
            }

            // Validate password length
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 6 characters long'
                ];
            }

            // Get current user with password
            $sql = "SELECT password FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $updateSql = "UPDATE users SET password = :password WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);

            return [
                'success' => true,
                'message' => 'Password updated successfully'
            ];
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred'
            ];
        }
    }

    /**
     * Update profile with optional password change
     */
    public function updateProfileWithPassword($userId, $data)
    {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');

        // Validate required fields
        if (empty($name) || empty($email)) {
            return [
                'success' => false,
                'message' => 'Name and email are required'
            ];
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        // First update profile
        $profileResult = $this->updateProfile($userId, $name, $email);

        if (!$profileResult['success']) {
            return $profileResult;
        }

        // If password change is requested
        if (!empty($data['new_password'])) {
            $passwordResult = $this->updatePassword(
                $userId,
                $data['current_password'] ?? '',
                $data['new_password'],
                $data['confirm_password'] ?? ''
            );

            if (!$passwordResult['success']) {
                return $passwordResult;
            }
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    }

    /**
     * Get user activity statistics
     */
    public function getUserActivity($userId)
    {
        $stats = [];

        // Get appointments created by user
        $sql = "SELECT COUNT(*) as total FROM appointments WHERE requester_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['appointments_created'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Get recent activity (last 5 appointments)
        $sql = "
            SELECT a.id, a.visit_date, s.service_name, a.created_at
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.requester_id = :user_id
            ORDER BY a.created_at DESC
            LIMIT 5
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get account age
        $sql = "SELECT created_at FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['created_at']) {
            $createdDate = new DateTime($user['created_at']);
            $now = new DateTime();
            $interval = $createdDate->diff($now);
            $stats['account_age_days'] = $interval->days;
        } else {
            $stats['account_age_days'] = 0;
        }

        return $stats;
    }
}
