<?php
// User class retrieves user data from database; UserProgress tracks puzzle attempts and statistics

class User {
    // Private properties
    private $id;
    private $username;
    private $email;
    private $role;
    private $conn;

    public function __construct($conn, $id = null) {
        $this->conn = $conn;
        if ($id) {
            $this->loadById($id);
        }
    }

    // Fetch user record from database by ID using prepared statement (prevents SQL injection)
    private function loadById($id) {
        $sql = "SELECT * FROM Users WHERE id = ?";  
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->role = $row['role'];
        }
    }

    // Getters for user properties
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }

    // Instantiate UserProgress object to track this user's puzzle attempts
    public function getProgress() {
        return new UserProgress($this->conn, $this->id);
    }
}

// UserProgress tracks user's puzzle attempts including questions answered, solve status, hints used, and attempts made
class UserProgress {
    private $conn;
    private $userId;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    // Retrieve all puzzle progress records for this user and return array of attempts
    public function getProgress() {
        $sql = "SELECT * FROM UserProgress WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Insert new progress entry: tracks which question, how many attempts, whether solved, hints used
    public function addProgress($questionId, $attempts, $solved, $hintsUsed) {
        $sql = "INSERT INTO UserProgress (user_id, question_id, attempts, solved, hints_used, last_attempt)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiii", $this->userId, $questionId, $attempts, $solved, $hintsUsed);
        return $stmt->execute();
    }
}

?>
