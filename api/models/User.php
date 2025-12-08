<?php
// api/models/User.php

class User {
    private $conn;
    private $table_name = "users";  // This should point to your users table

    public $id;
    public $student_id;
    public $full_name;
    public $course;
    public $email;
    public $password;
    public $password_hash;
    public $created_at;
    public $role;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Login method to verify user credentials
    public function login($plainPassword) {
        $query = "SELECT id, student_id, full_name, course, email, password_hash, role, status 
                  FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verify password against the correct hash column
            if (password_verify($plainPassword, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->student_id = $row['student_id'];
                $this->full_name = $row['full_name'];
                $this->course = $row['course'];
                $this->role = $row['role'];
                $this->status = $row['status'];
                return true;
            }
        }
        return false;
    }

    // Check if student ID exists
    public function studentIdExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE student_id = :student_id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET student_id = :student_id,
                      full_name = :full_name,
                      course = :course,
                      email = :email,
                      password_hash = :password_hash,
                      status = 'active',
                      role = 'student'"; // Set default role

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->course = htmlspecialchars(strip_tags($this->course));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":course", $this->course);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    // Hash password
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT id, student_id, full_name, course, email, status 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    // Get user role by ID
    public function getRoleById($id) {
        $query = "SELECT role FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['role'];
        }
        return null;
    }
}
