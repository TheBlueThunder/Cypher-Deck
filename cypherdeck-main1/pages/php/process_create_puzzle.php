<?php
// Puzzle creation handler that validates details and supports standard puzzles with fixed points and burst rush with multiple questions and time-based scoring
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

require_once("process_db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate all required fields are non-empty
    $requiredFields = ['title', 'description', 'category', 'difficulty', 'puzzle_type'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "All fields are required.";
            header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
            exit();
        }
    }
    
    // Look up user ID from database using username in session
    $username = $_SESSION['username'];
    $sql = "SELECT id FROM Users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    
    // Get puzzle type (standard or burstrush)
    $puzzleType = $_POST['puzzle_type'] ?? 'standard';
    
    // Trim whitespace from all input fields
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $difficulty = trim($_POST['difficulty']);
    
    // Validate title length (3-255 characters)
    if (strlen($title) < 3 || strlen($title) > 255) {
        $_SESSION['error'] = "Title must be between 3 and 255 characters.";
        header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
        exit();
    }
    // Validate description length (10-5000 characters)
    if (strlen($description) < 10 || strlen($description) > 5000) {
        $_SESSION['error'] = "Description must be between 10 and 5000 characters.";
        header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
        exit();
    }
    
    // Standard puzzle type with single question and fixed point reward on completion
    if ($puzzleType === 'standard') {
        $basePoints = (int)($_POST['base_points'] ?? 100);
        
        // Validate base points (10-500 range)
        if ($basePoints < 10 || $basePoints > 500) {
            $basePoints = 100; // Reset to default if invalid to prevent abuse
        }
        
        // Prepared statement prevents SQL injection
        $sql = "INSERT INTO PuzzleStore 
                (title, description, category, difficulty, created_by, base_points, is_burstrush)
                VALUES (?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $title, $description, $category, $difficulty, $userId, $basePoints);
        
    // Burst rush puzzle type with multiple random questions, timed gameplay, and streak-based scoring multipliers
    } else if ($puzzleType === 'burstrush') {
        $pointsPerQuestion = (int)($_POST['points_per_question'] ?? 10);
        $maxQuestions = (int)($_POST['max_questions_per_session'] ?? 10);
        $timePerQuestion = (int)($_POST['time_limit_per_question'] ?? 15);
        
        // Validate points per question (5-50 range)
        if ($pointsPerQuestion < 5 || $pointsPerQuestion > 50) {
            $pointsPerQuestion = 10; // Reset to default if invalid
        }
        // Validate max questions (5-50 range)
        if ($maxQuestions < 5 || $maxQuestions > 50) {
            $maxQuestions = 10; // Reset to default if invalid
        }
        // Validate time limit (5-60 seconds range)
        if ($timePerQuestion < 5 || $timePerQuestion > 60) {
            $timePerQuestion = 15; // Reset to default if invalid
        }
        
        $sql = "INSERT INTO PuzzleStore 
                (title, description, category, difficulty, created_by, is_burstrush, points_per_question, max_questions_per_session, time_limit_per_question)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiii", $title, $description, $category, $difficulty, $userId, $pointsPerQuestion, $maxQuestions, $timePerQuestion);
    } else {
        // Reject and redirect for invalid puzzle type
        header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
        exit();
    }
    
    if ($stmt->execute()) {
        $puzzleId = $conn->insert_id;
        
        // Insert questions for this puzzle
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            // Validate that at least one question was provided
            if (empty($_POST['questions'])) {
                $_SESSION['error'] = "At least one question is required.";
                header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
                exit();
            }
            
            $questionSql = "INSERT INTO PuzzleQuestions (question, answer, hints, puzzleid) VALUES (?, ?, ?, ?)";
            $questionStmt = $conn->prepare($questionSql);
            
            foreach ($_POST['questions'] as $question) {
                $questionText = isset($question['text']) ? trim($question['text']) : '';
                $answer = isset($question['answer']) ? trim($question['answer']) : '';
                
                if (empty($questionText) || empty($answer)) {
                    $_SESSION['error'] = "All questions must have text and answers.";
                    header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
                    exit();
                }
                
                if (strlen($questionText) > 5000 || strlen($answer) > 255) {
                    $_SESSION['error'] = "Question or answer text is too long.";
                    header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
                    exit();
                }
                
                $hints = isset($question['hints']) ? trim($question['hints']) : '';
                if (strlen($hints) > 1000) {
                    $hints = substr($hints, 0, 1000);
                }
                
                $questionStmt->bind_param("sssi", $questionText, $answer, $hints, $puzzleId);
                
                if (!$questionStmt->execute()) {
                    $_SESSION['error'] = "Error creating question: " . $questionStmt->error;
                    header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
                    exit();
                }
            }
            
            $questionStmt->close();
        } else {
            // No questions were provided with this puzzle
            $_SESSION['error'] = "At least one question is required.";
            header('Location: /cypherdeck-main1/pages/puzzles/create_puzzle.php');
            exit();
        }
        
        $_SESSION['puzzle_created'] = true;
        header('Location: /cypherdeck-main1/pages/puzzles/puzzles.php');
        exit();
        
    } else {
        // If the puzzle insert failed, show the database error
        echo "Error creating puzzle: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
