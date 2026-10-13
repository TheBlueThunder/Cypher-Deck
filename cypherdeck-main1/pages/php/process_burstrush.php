<?php
// Burst rush game handler that processes gameplay including starting sessions, submitting answers, calculating points, and ending sessions
session_start();
require_once("process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Initialize burst rush session state in $_SESSION when user selects puzzles from menu
if (isset($_POST['action']) && $_POST['action'] === 'start_session') {
    $puzzleIds = $_POST['puzzle_ids'] ?? [];
    $difficulty = $_POST['difficulty'] ?? 'base';
    
    if (empty($puzzleIds)) {
        die('No puzzles selected');
    }
    
    // Store puzzle selection, difficulty, timestamps, and stat tracking variables in session state
    $_SESSION['burst_rush_puzzles'] = $puzzleIds;
    $_SESSION['burst_rush_difficulty'] = $difficulty;
    $_SESSION['burst_rush_start_time'] = time();
    $_SESSION['burst_rush_current_index'] = 0;
    $_SESSION['burst_rush_total_score'] = 0;
    $_SESSION['burst_rush_total_correct'] = 0;
    $_SESSION['burst_rush_total_questions'] = 0;
    $_SESSION['burst_rush_streak'] = 0;
    $_SESSION['burst_rush_max_streak'] = 0;
    
    $firstPuzzleId = $puzzleIds[0];
    header('Location: /cypherdeck-main1/pages/puzzles/burstrush_solve.php?id=' . $firstPuzzleId . '&index=0');
    exit();
}

// Set JSON response header for AJAX requests to tell browser the response is JSON not HTML
header('Content-Type: application/json');

// Only accept POST requests from JavaScript, returning error response for GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get action parameter to determine which operation to perform (start, answer, or end)
$action = $_POST['action'] ?? '';

// Create new burst rush session, fetch random questions, and return session config to client
if ($action === 'start') {
    $puzzleId = (int)($_POST['puzzle_id'] ?? 0);
    
    if (!$puzzleId) {
        echo json_encode(['error' => 'No puzzle ID provided']);
        exit;
    }
    
    // Fetch burst rush puzzle config (points_per_question, max_questions_per_session, time_limit_per_question)
    $sql = "SELECT * FROM PuzzleStore WHERE id = ? AND is_burstrush = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $puzzleId);
    $stmt->execute();
    $puzzle = $stmt->get_result()->fetch_assoc();
    
    if (!$puzzle) {
        echo json_encode(['error' => 'Puzzle not found']);
        exit;
    }
    
    // Create session record in BurstRushSessions table to track this gameplay attempt
    $sql = "INSERT INTO BurstRushSessions (user_id, puzzle_id, status) VALUES (?, ?, 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $puzzleId);
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to create session']);
        exit;
    }
    
    $sessionId = $conn->insert_id;
    
    // Fetch random questions from puzzle
    $sql = "SELECT id, question, answer 
            FROM PuzzleQuestions 
            WHERE puzzleid = ? 
            ORDER BY RAND() 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $puzzleId, $puzzle['max_questions_per_session']);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    
    // Remove answers from client to prevent cheating by hiding answer in page source
    $questionsForClient = array_map(function($q) {
        return ['id' => $q['id'], 'question' => $q['question']];
    }, $questions);
    
    // ========== RETURN SESSION INFO TO CLIENT ==========
    // Send JSON response with session info and questions for the player
    // Client needs sessionId to send answers back to this script
    // Client also needs time/points config and list of questions to display
    echo json_encode([
        'session_id' => $sessionId,
        'puzzle_id' => $puzzleId,
        'time_per_question' => $puzzle['time_limit_per_question'],
        'points_per_question' => $puzzle['points_per_question'],
        'total_questions' => count($questions),
        'questions' => $questionsForClient
    ]);
    exit;
}

// Process user's answer to a question
else if ($action === 'answer') {
    $sessionId = (int)($_POST['session_id'] ?? 0);
    $questionId = (int)($_POST['question_id'] ?? 0);
    $userAnswer = trim($_POST['answer'] ?? '');
    
    if (!$sessionId || !$questionId) {
        echo json_encode(['error' => 'Invalid session or question ID']);
        exit;
    }
    
    // Fetch correct answer for verification
    $sql = "SELECT answer FROM PuzzleQuestions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        echo json_encode(['error' => 'Question not found']);
        exit;
    }
    
    // Compare answer (case-insensitive)
    $isCorrect = strtolower($result['answer']) === strtolower($userAnswer);
    
    // Update streak counter based on correctness
    if ($isCorrect) {
        // Increment streak, update max if needed
        $sql = "UPDATE BurstRushSessions 
                SET questions_answered = questions_answered + 1,
                    correct_answers = correct_answers + 1,
                    current_streak = current_streak + 1,
                    max_streak = GREATEST(max_streak, current_streak + 1)
                WHERE id = ? AND user_id = ?";
    } else {
        // Reset streak on wrong answer
        $sql = "UPDATE BurstRushSessions 
                SET questions_answered = questions_answered + 1,
                    current_streak = 0
                WHERE id = ? AND user_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    
    // Fetch updated session data
    $sql = "SELECT * FROM BurstRushSessions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    // Fetch puzzle configuration
    $sql = "SELECT * FROM PuzzleStore WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session['puzzle_id']);
    $stmt->execute();
    $puzzle = $stmt->get_result()->fetch_assoc();
    
    // Calculate streak multiplier (1.0x base, max 2.5x with streak)
    $streakMultiplier = 1.0 + ($session['current_streak'] * 0.1);
    $streakMultiplier = min($streakMultiplier, 2.5);
    
    // Calculate points earned for this answer
    $pointsForThisQuestion = $isCorrect ? 
        round($puzzle['points_per_question'] * $streakMultiplier) : 0;
    
    // Update total points
    $newTotalPoints = $session['total_points_earned'] + $pointsForThisQuestion;
    $sql = "UPDATE BurstRushSessions SET total_points_earned = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newTotalPoints, $sessionId);
    $stmt->execute();
    
    // ========== RETURN ANSWER RESULT ==========
    // Send back all the information the client needs to update the UI
    echo json_encode([
        'correct' => $isCorrect,                      // Boolean: did user get it right?
        'points_earned' => $pointsForThisQuestion,    // Points awarded for this answer
        'streak' => $session['current_streak'],       // Current streak count
        'total_points' => $newTotalPoints,            // Running total points in session
        'max_streak' => $session['max_streak'],       // Best streak so far this session
        'questions_answered' => $session['questions_answered']  // Total questions answered
    ]);
    exit;
}

// Finalize session and record score
else if ($action === 'end') {
    // Get the session ID to close
    $sessionId = (int)($_POST['session_id'] ?? 0);
    
    if (!$sessionId) {
        echo json_encode(['error' => 'No session ID']);
        exit;
    }
    
    // Fetch final session stats
    $sql = "SELECT * FROM BurstRushSessions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        echo json_encode(['error' => 'Session not found']);
        exit;
    }
    
    // Mark session as completed
    $sql = "UPDATE BurstRushSessions SET status = 'completed', session_end = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    
    // Record final score to leaderboard/statistics
    $sql = "INSERT INTO SolvedPuzzles 
            (user_id, puzzle_id, solved_at, mode, points_earned, streak_count, questions_correct, total_questions_in_session)
            VALUES (?, ?, NOW(), 'burstrush', ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            points_earned = VALUES(points_earned),
            streak_count = VALUES(streak_count)";
    
    $stmt = $conn->prepare($sql);
    // Bind parameters: user_id, puzzle_id, total_points, max_streak, correct_answers, total_questions
    $stmt->bind_param("iiiiii", $userId, $session['puzzle_id'], $session['total_points_earned'], 
                       $session['max_streak'], $session['correct_answers'], $session['questions_answered']);
    $stmt->execute();
    
    // Return final score summary
    echo json_encode([
        'success' => true,
        'final_score' => $session['total_points_earned'],
        'questions_correct' => $session['correct_answers'],
        'total_questions' => $session['questions_answered'],
        'best_streak' => $session['max_streak']
    ]);
    exit;
}

// Invalid action
echo json_encode(['error' => 'Invalid action']);
?>
