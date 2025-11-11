<?php

require_once __DIR__ . '/../../config/database.php';

class User
{
    private $db;
    private $conn;
    private $table = 'users';

    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $password;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function create($data)
    {
        try {
            $sql =
                'INSERT INTO ' .
                $this->table .
                ' (email, password, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)';

            $stmt = $this->db->query($sql, [
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
            ]);

            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('User creation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email, $excludeId = null)
    {
        $sql = 'SELECT user_id FROM users WHERE email = ?';
        $params = [$email];

        if ($excludeId) {
            $sql .= ' AND user_id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch() !== false;
    }

    // ==================== READ ====================

    /**
     * Find user by ID
     *
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function findById($id)
    {
        $sql = 'SELECT * FROM users WHERE user_id = ?';
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * Find user by email
     *
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = ?';
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }

    /**
     * Get all users
     *
     * @param int $limit Number of users to return (optional)
     * @param int $offset Starting point (for pagination)
     * @return array Array of users
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT user_id, email, first_name, last_name, phone, 
                       date_of_birth, profile_photo, is_admin, created_at 
                FROM users 
                ORDER BY created_at DESC";

        if ($limit) {
            $sql .= ' LIMIT ? OFFSET ?';
            $stmt = $this->db->query($sql, [$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get total count of users
     *
     * @return int Total number of users
     */
    public function count()
    {
        $sql = 'SELECT COUNT(*) as total FROM users';
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Search users by name or email
     *
     * @param string $searchTerm Search keyword
     * @return array Array of matching users
     */
    public function search($searchTerm)
    {
        $searchTerm = "%{$searchTerm}%";

        $sql = "SELECT user_id, email, first_name, last_name, phone, created_at 
                FROM users 
                WHERE first_name LIKE ? 
                   OR last_name LIKE ? 
                   OR email LIKE ?
                ORDER BY first_name ASC";

        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    // ==================== UPDATE ====================

    /**
     * Update user information
     *
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE users SET 
                    first_name = ?,
                    last_name = ?,
                    phone = ?,
                    date_of_birth = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?";

            $stmt = $this->db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['phone'] ?? null,
                $data['date_of_birth'] ?? null,
                $id,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('User update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user email
     *
     * @param int $id User ID
     * @param string $newEmail New email address
     * @return bool Success status
     */
    public function updateEmail($id, $newEmail)
    {
        try {
            // Check if email already exists
            if ($this->emailExists($newEmail, $id)) {
                return false;
            }

            $sql = "UPDATE users SET email = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE user_id = ?";
            $stmt = $this->db->query($sql, [$newEmail, $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Email update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     *
     * @param int $id User ID
     * @param string $newPassword New password (plain text, will be hashed)
     * @return bool Success status
     */
    public function updatePassword($id, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE user_id = ?";
            $stmt = $this->db->query($sql, [$hashedPassword, $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Password update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update profile photo
     *
     * @param int $id User ID
     * @param string $photoPath Path to profile photo
     * @return bool Success status
     */
    public function updateProfilePhoto($id, $photoPath)
    {
        try {
            $sql = "UPDATE users SET profile_photo = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE user_id = ?";
            $stmt = $this->db->query($sql, [$photoPath, $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Profile photo update failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== DELETE ====================

    /**
     * Delete user (soft delete - could be implemented)
     *
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id)
    {
        try {
            // Note: In production, you might want soft delete instead
            // (adding a deleted_at column instead of actually deleting)

            $sql = 'DELETE FROM users WHERE user_id = ?';
            $stmt = $this->db->query($sql, [$id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('User deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== AUTHENTICATION ====================

    /**
     * Authenticate user with email and password
     *
     * @param string $email User email
     * @param string $password Plain text password
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * Check if user is admin
     *
     * @param int $id User ID
     * @return bool True if admin, false otherwise
     */
    public function isAdmin($id)
    {
        $user = $this->findById($id);
        return $user && $user['is_admin'] == 1;
    }

    public function validate($data, $isUpdate = false)
    {
        $errors = [];

        // Email validation
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif (!$isUpdate && $this->emailExists($data['email'])) {
            $errors[] = 'Email already registered';
        }

        // Password validation (only for new users or if password is being changed)
        if (!$isUpdate || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($data['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }
        }

        // Name validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }

        // Phone validation (optional but format check if provided)
        if (!empty($data['phone'])) {
            if (!preg_match('/^[0-9+\-\s()]+$/', $data['phone'])) {
                $errors[] = 'Invalid phone number format';
            }
        }

        return $errors;
    }
}

?>
