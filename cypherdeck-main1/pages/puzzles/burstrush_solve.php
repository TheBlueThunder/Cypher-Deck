<?php
session_start();
require_once("../php/process_db.php");
require_once("../php/session_security.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Check if we're in a burst rush session
if (!isset($_SESSION['burst_rush_puzzles']) || empty($_SESSION['burst_rush_puzzles'])) {
    header('Location: /cypherdeck-main1/pages/puzzles/burstrush.php');
    exit();
}

$userId = $_SESSION['user_id'];
$currentIndex = (int)($_GET['index'] ?? 0);
$puzzleIds = $_SESSION['burst_rush_puzzles'];
$difficulty = $_SESSION['burst_rush_difficulty'];

// Make sure index is valid
if ($currentIndex < 0 || $currentIndex >= count($puzzleIds)) {
    header('Location: /cypherdeck-main1/pages/puzzles/burstrush_results.php');
    exit();
}

$currentPuzzleId = $puzzleIds[$currentIndex];

// Get puzzle details
$sql = "SELECT p.*, u.username as creator_name FROM PuzzleStore p 
        JOIN Users u ON p.created_by = u.id WHERE p.id = ? AND is_burstrush = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentPuzzleId);
$stmt->execute();
$puzzle = $stmt->get_result()->fetch_assoc();

if (!$puzzle) {
    header('Location: /cypherdeck-main1/pages/puzzles/burstrush.php');
    exit();
}

// Get all questions for this puzzle
$sql = "SELECT * FROM PuzzleQuestions WHERE puzzleid = ? ORDER BY id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentPuzzleId);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle answer submission
$message = '';
$messageType = '';
$answeredQuestionId = null;
$allCorrect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer']) && isset($_POST['question_id'])) {
    $answer = trim($_POST['answer']);
    $questionId = (int)$_POST['question_id'];
    $answeredQuestionId = $questionId;
    
    // Check if answer is correct
    $sql = "SELECT answer FROM PuzzleQuestions WHERE id = ? AND puzzleid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $questionId, $currentPuzzleId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $correctAnswer = strtolower(trim(preg_replace('/\s+/', ' ', $result['answer'])));
        $userAnswer = strtolower(trim(preg_replace('/\s+/', ' ', $answer)));
        
        if ($correctAnswer === $userAnswer) {
            // Correct answer, so increment streak and score
            $messageType = 'success';
            $_SESSION['burst_rush_total_correct'] = ($_SESSION['burst_rush_total_correct'] ?? 0) + 1;
            $_SESSION['burst_rush_streak'] = ($_SESSION['burst_rush_streak'] ?? 0) + 1;
            $_SESSION['burst_rush_total_questions'] = ($_SESSION['burst_rush_total_questions'] ?? 0) + 1;
            
            // Calculate points with streak multiplier
            $basePoints = $puzzle['base_points'] ?? 10;  // Use base_points for consistency
            $streakBonus = min(($_SESSION['burst_rush_streak'] ?? 1) * 0.1, 1.5);  // 10% per streak, cap at 1.5x
            $multiplier = min(1.0 + $streakBonus, 2.5);          // Cap at 2.5x
            $pointsEarned = round($basePoints * $multiplier);
            $_SESSION['burst_rush_total_score'] = ($_SESSION['burst_rush_total_score'] ?? 0) + $pointsEarned;
            
            $message = '✓ Correct! +' . $pointsEarned . ' points (Streak: ' . $_SESSION['burst_rush_streak'] . ')';
            
            // Check if all questions answered correctly
            $allCorrect = ($_SESSION['burst_rush_total_correct'] >= count($questions));
        } else {
            // Wrong answer, so reset streak but allow another attempt
            $messageType = 'error';
            $_SESSION['burst_rush_streak'] = 0;
            $_SESSION['burst_rush_total_questions'] = ($_SESSION['burst_rush_total_questions'] ?? 0) + 1;
            $message = '✗ Wrong answer. Try again or move to next puzzle.';
        }
    }
}

// Check if player wants to move to next puzzle
if (isset($_POST['next_puzzle']) && $_POST['next_puzzle'] === '1') {
    $nextIndex = $currentIndex + 1;
    
    if ($nextIndex >= count($puzzleIds)) {
        // Go to results when all puzzles are completed
        $_SESSION['burst_rush_max_streak'] = max($_SESSION['burst_rush_max_streak'] ?? 0, $_SESSION['burst_rush_streak']);
        header('Location: /cypherdeck-main1/pages/puzzles/burstrush_results.php');
        exit();
    } else {
        // Go to next puzzle
        header('Location: /cypherdeck-main1/pages/puzzles/burstrush_solve.php?id=' . $puzzleIds[$nextIndex] . '&index=' . $nextIndex);
        exit();
    }
}

// Time limit calculation
$timeLimitMultiplier = match($difficulty) {
    'base' => 1.0,
    'turbo' => 0.5,     // 2x speed = half time
    'hyper' => 0.33,    // 3x speed
    'thermal' => 0.25,  // 4x speed
    default => 1.0
};

$timePerQuestion = (int)($puzzle['time_limit_per_question'] * $timeLimitMultiplier);
$totalTime = $timePerQuestion * count($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burst Rush - <?php echo htmlspecialchars($puzzle['title']); ?> | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        :root {
            --primary-color: #00ff66;
            --primary-glow: rgba(0, 255, 102, 0.5);
        }
        
        :root.difficulty-turbo {
            --primary-color: #00bfff;
            --primary-glow: rgba(0, 191, 255, 0.5);
        }
        
        :root.difficulty-hyper {
            --primary-color: #ffc107;
            --primary-glow: rgba(255, 193, 7, 0.5);
        }
        
        :root.difficulty-thermal {
            --primary-color: #dc3545;
            --primary-glow: rgba(220, 53, 69, 0.5);
        }

        .burst-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .burst-progress {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid var(--primary-color);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .burst-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-shadow: 0 0 10px var(--primary-glow);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #999;
            margin-top: 0.5rem;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: bold;
            animation: slideIn 0.3s ease;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.3);
            border: 2px solid #28a745;
            color: #28a745;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.3);
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .next-puzzle-btn {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .next-puzzle-btn:hover {
            background: var(--primary-glow);
            box-shadow: 0 0 20px var(--primary-glow);
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="main-box mx-auto" style="max-width: 1000px;">
            <!-- Header with progress info -->
            <div class="burst-header">
                <div>
                    <h2 class="text-glow" style="text-shadow: 0 0 20px var(--primary-glow);">
                        <?php echo htmlspecialchars($puzzle['title']); ?>
                    </h2>
                    <small class="text-muted">Puzzle <?php echo ($currentIndex + 1); ?> of <?php echo count($puzzleIds); ?></small>
                </div>
                <div class="burst-progress">
                    <strong style="color: var(--primary-color);"><?php 
                        echo ($currentIndex + 1) . ' / ' . count($puzzleIds); 
                    ?></strong>
                    <div class="progress mt-2" style="height: 20px;">
                        <?php
                        $progressPercent = (($currentIndex + 1) / count($puzzleIds)) * 100;
                        ?>
                        <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%; 
                             background-color: var(--primary-color);
                             box-shadow: 0 0 10px var(--primary-glow);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Burst Rush stats -->
            <div class="burst-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $_SESSION['burst_rush_total_score']; ?></div>
                    <div class="stat-label">Total Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $_SESSION['burst_rush_streak']; ?></div>
                    <div class="stat-label">Current Streak</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $_SESSION['burst_rush_total_correct']; ?></div>
                    <div class="stat-label">Correct</div>
                </div>
            </div>

            <!-- Message feedback -->
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Puzzle questions -->
            <?php foreach ($questions as $question): ?>
                <div class="question-block mb-4">
                    <div class="question-card">
                        <h4 class="mb-3"><?php echo htmlspecialchars($question['question']); ?></h4>
                        
                        <?php if ($answeredQuestionId === $question['id'] && $messageType === 'success'): ?>
                            <!-- Show "Next Puzzle" button after correct answer -->
                            <form method="POST">
                                <input type="hidden" name="next_puzzle" value="1">
                                <button type="submit" class="next-puzzle-btn">
                                    <?php echo ($currentIndex + 1 >= count($puzzleIds)) ? 'See Results' : 'Next Puzzle'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Answer input form -->
                            <form method="POST" class="mt-3">
                                <div class="input-group mb-3">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    <input type="text" 
                                           class="form-control" 
                                           name="answer" 
                                           placeholder="Enter your answer..." 
                                           autocomplete="off"
                                           <?php echo ($answeredQuestionId && $answeredQuestionId !== $question['id']) ? 'disabled' : ''; ?>>
                                    <button class="btn btn-outline-success" type="submit">Submit</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Skip to next puzzle option (always available) -->
            <?php if (!($answeredQuestionId && $messageType === 'success')): ?>
                <form method="POST" class="text-center">
                    <input type="hidden" name="next_puzzle" value="1">
                    <button type="submit" class="btn btn-outline-secondary mt-3">
                        Skip to Next Puzzle
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Set difficulty theme color
        const difficulty = '<?php echo $difficulty; ?>';
        if (difficulty && difficulty !== 'base') {
            document.documentElement.classList.add('difficulty-' + difficulty);
        }
    </script>
</body>
</html>
