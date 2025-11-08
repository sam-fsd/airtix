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
