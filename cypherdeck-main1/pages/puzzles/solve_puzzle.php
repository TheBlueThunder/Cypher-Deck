<?php
session_start();
require_once("../php/process_db.php");
require_once("../php/session_security.php");

// Check if puzzle ID and mode are provided to my page
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['mode'])) {
    header('Location: /cypherdeck-main1/pages/puzzles/puzzles.php');
    exit();
}

$puzzleId = (int)$_GET['id']; // The puzzle we click on in the list will automatically have an ID assigned to it
$mode = $_GET['mode'];
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$restrictions = isset($_GET['restrictions']) ? array_filter(explode(',', $_GET['restrictions'])) : [];
$noTabSwitch = isset($_GET['noTabs']) && $_GET['noTabs'] === '1';
$noHints = isset($_GET['noHints']) && $_GET['noHints'] === '1';

// Determine time limit based on difficulty for frontend application
$timeLimits = [
    'base' => 300,      // 5 minutes
    'turbo' => 240,     // 4 minutes
    'hyper' => 180,     // 3 minutes
    'thermal' => 120    // 2 minutes
];

// Grabs the puzzle details from the PuzzleStore
function getPuzzleDetails($puzzleId) {
    global $conn;
    // These are aliases, making everything so much easier to write
    $sql = "SELECT p.*, u.username as creator_name 
            FROM PuzzleStore p 
            JOIN Users u ON p.created_by = u.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $puzzleId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Override time limits with puzzle-specific limits if available
    if ($result && isset($result['time_limit_base'])) {
        global $timeLimits;
        $timeLimits = [
            'base' => $result['time_limit_base'],
            'turbo' => $result['time_limit_turbo'],
            'hyper' => $result['time_limit_hyper'],
            'thermal' => $result['time_limit_thermal']
        ];
    }
    
    return $result;
}

// Get puzzle questions from the PuzzleQuestions
function getPuzzleQuestions($puzzleId) {
    global $conn;
    $sql = "SELECT * FROM PuzzleQuestions WHERE puzzleid = ? ORDER BY id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $puzzleId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$puzzle = getPuzzleDetails($puzzleId);
$questions = getPuzzleQuestions($puzzleId);

if (!$puzzle) {
    header('Location: /cypherdeck-main1/pages/puzzles/puzzles.php');
    exit();
}

// Handle answer submission
$message = '';
$messageType = '';
$answeredQuestionId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer']) && isset($_POST['question_id'])) {
    $answer = trim($_POST['answer']);
    $questionId = (int)$_POST['question_id'];
    $answeredQuestionId = $questionId;
    
    // Check if the answer is correct
    $sql = "SELECT answer FROM PuzzleQuestions WHERE id = ? AND puzzleid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $questionId, $puzzleId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        // Normalize both answers: lowercase, trim, and collapse multiple spaces
        $correctAnswer = strtolower(trim(preg_replace('/\s+/', ' ', $result['answer'])));
        $userAnswer = strtolower(trim(preg_replace('/\s+/', ' ', $answer)));
        
        if ($correctAnswer === $userAnswer) {
            // CORRECT ANSWER
            $messageType = 'success';
            
            // Calculate points based on mode and restrictions
            $points = $puzzle['base_points'];
            if ($mode === 'overclock') {
                // Apply difficulty multiplier for OVER//CLOCK mode
                $multiplier = match($difficulty) {
                    'base' => 1.2,
                    'turbo' => 1.5,
                    'hyper' => 2.0,
                    'thermal' => 2.5,
                    default => 1.0
                };
                $points = $points * $multiplier;
                
                // Add bonus points for restrictions (no tab switching, no hints)
                if (in_array('notabs', $restrictions)) $points += 25;
                if (in_array('nohints', $restrictions)) $points += 25;
            }
            
            $pointsEarned = round($points);
            $message = '✓ Correct answer! +' . $pointsEarned . ' points earned!';
            
            // Track attempt count
            $totalQuestions = count($questions);
            $attemptsUsed = 1;
            
            // Check if user has attempted this puzzle before (for attempt counting)
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                $checkSql = "SELECT attempts_used FROM SolvedPuzzles WHERE user_id = ? AND puzzle_id = ? AND mode = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("iss", $userId, $puzzleId, $mode);
                $checkStmt->execute();
                $existingRecord = $checkStmt->get_result()->fetch_assoc();
                if ($existingRecord) {
                    $attemptsUsed = $existingRecord['attempts_used'] + 1;
                }
            }
            
            // Save progress for this correct answer (if user is logged in)
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                
                // Record puzzle attempt with points earned
                $sql = "INSERT INTO SolvedPuzzles 
                        (user_id, puzzle_id, solved_at, mode, difficulty, points_earned, attempts_used)
                        VALUES (?, ?, NOW(), ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        points_earned = VALUES(points_earned),
                        solved_at = NOW(),
                        attempts_used = VALUES(attempts_used)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissii", $userId, $puzzleId, $mode, $difficulty, $pointsEarned, $attemptsUsed);
                $stmt->execute();
                
                // Update UserProgress table with latest stats
                $updateProgressSql = "UPDATE UserProgress 
                                     SET puzzles_solved = (SELECT COUNT(DISTINCT puzzle_id) FROM SolvedPuzzles WHERE user_id = ?),
                                         attempts = (SELECT SUM(attempts_used) FROM SolvedPuzzles WHERE user_id = ?),
                                         total_points = (SELECT SUM(points_earned) FROM SolvedPuzzles WHERE user_id = ?),
                                         last_attempt = NOW()
                                     WHERE user_id = ?";
                $progressStmt = $conn->prepare($updateProgressSql);
                $progressStmt->bind_param("iiii", $userId, $userId, $userId, $userId);
                $progressStmt->execute();
            }
            
            // Check if this is the last question
            if ($questionId === $questions[count($questions) - 1]['id']) {
                // Show completion and redirect after last question is answered
                $message .= ' 🎉 Puzzle Complete! Redirecting...';
                
                // If overclock mode, go to overclock results page with score breakdown
                if ($mode === 'overclock') {
                    $totalQuestions = count($questions);
                    header("refresh:3;url=/cypherdeck-main1/pages/puzzles/overclock_results.php?points=" . $pointsEarned . "&difficulty=" . $difficulty . "&puzzle_id=" . $puzzleId . "&correct=" . $totalQuestions . "&total=" . $totalQuestions);
                } else {
                    // For standard mode, go back to puzzles
                    header("refresh:3;url=/cypherdeck-main1/pages/puzzles/puzzles.php");
                }
            } else {
                // Show success message and reload page for non-final questions
                if (!isset($_SESSION['user_id'])) {
                    $message .= ' (Login to track progress)';
                }
            }
        } else {
            // WRONG ANSWER
            $messageType = 'danger';
            $message = '✗ Incorrect answer. Try again!';
        }
    } else {
        // Question not found or no answer provided
        $messageType = 'danger';
        $message = '✗ Question not found. Please refresh the page.';
    }
}

// Get user's previous solve status for this puzzle (if logged in)
$userHasSolved = false;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT id FROM SolvedPuzzles WHERE user_id = ? AND puzzle_id = ? AND mode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $puzzleId, $mode);
    $stmt->execute();
    $result = $stmt->get_result();
    $userHasSolved = $result->num_rows > 0;
}

require_once("../../pages/templates/navbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($puzzle['title']); ?> - Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.php">
    <link rel="stylesheet" href="../../css/style_puzzle_solve.php">
    <?php
    // Get theme color from URL parameter
    $themeColor = '#00ff66'; // default green
    if ($mode === 'overclock' && isset($_GET['color'])) {
        $themeColor = $_GET['color'];
    }
    ?>
    <style>
        :root {
            --theme-color: <?php echo htmlspecialchars($themeColor); ?>;
        }
        /* Update navbar with theme color */
        .navbar {
            border-bottom: 2px solid var(--theme-color);
            box-shadow: 0 2px 10px var(--theme-color);
        }
        .container {
            margin-top: 120px;
        }
        .main-box {
            background-color: #000;
            border: 2px solid var(--theme-color);
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0 20px var(--theme-color);
        }
        .question-solved {
            border-color: rgba(25, 135, 84, 0.5) !important;
            box-shadow: 0 0 20px rgba(25, 135, 84, 0.3) !important;
        }
        .question-solved .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.5s;
        }
    </style>
</head>
<body>
    <!-- Timer and Mode Display -->
    <div class="timer-container" id="timer">
        Time: <span id="minutes">00</span>:<span id="seconds">00</span>
    </div>
    
    <div class="mode-badge <?php echo $mode === 'standard' ? 'mode-standard' : 'mode-overclock'; ?>">
        <?php 
        if ($mode === 'standard') {
            echo 'Standard Mode';
        } else {
            echo 'OVER//CLOCK - ' . ucfirst($difficulty);
        }
        ?>
    </div>

    <div class="container">
        <div class="main-box">
            <div class="puzzle-header">
                <h1 class="display-4 fw-bold text-center mb-4"><?php echo htmlspecialchars($puzzle['title']); ?></h1>
            
                <div class="puzzle-details text-center">
                    <span class="difficulty <?php echo strtolower($puzzle['difficulty']); ?>">
                        <?php echo $puzzle['difficulty']; ?>
                    </span>
                    <span class="category ms-2"><?php echo htmlspecialchars($puzzle['category']); ?></span>
                    <p class="creator mt-2">Created by <?php echo htmlspecialchars($puzzle['creator_name']); ?></p>
                </div>

                <div class="puzzle-description mt-4">
                    <?php echo nl2br(htmlspecialchars($puzzle['description'])); ?>
                </div>
            </div>

            <div class="questions mt-4">
                <?php foreach ($questions as $index => $question): 
                    $isLastQuestion = ($question['id'] === $questions[count($questions) - 1]['id']);
                    $showMessage = ($answeredQuestionId === $question['id']);
                ?>
                    <div class="question-card mb-4" id="question-<?php echo $question['id']; ?>">
                        <h3 class="question-number">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h3>
                        <div class="question-text">
                            <?php echo nl2br(htmlspecialchars($question['question'])); ?>
                        </div>
                        
                        <?php if ($showMessage && $message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show mt-3" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($userHasSolved && $index === 0): ?>
                            <div class="alert alert-info mt-3">
                                ✓ You have already solved this puzzle! 
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($question['hints'] && !in_array('nohints', $restrictions)): ?>
                            <div class="hints mt-3">
                                <button type="button" class="hint-button" onclick="toggleHint(<?php echo $question['id']; ?>)">
                                    💡 Show Hint
                                </button>
                                <div id="hint-<?php echo $question['id']; ?>" class="hint-text" style="display: none;">
                                    <?php echo nl2br(htmlspecialchars($question['hints'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="answer-form mt-3">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <div class="input-group">
                                <input type="text" name="answer" class="form-control" 
                                       placeholder="Your answer" 
                                       required
                                       autocomplete="off">
                                <button type="submit" class="btn" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                        onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
                                        onmouseout="this.style.boxShadow='none'">
                                    <?php echo $isLastQuestion ? 'Submit & Complete' : 'Submit Answer'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4 mb-5">
                <a href="/cypherdeck-main1/pages/puzzles/puzzles.php" class="btn" style="border: 2px solid #666; color: #aaa; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                   onmouseover="this.style.boxShadow='0 0 20px rgba(100,100,100,0.6)'" 
                   onmouseout="this.style.boxShadow='none'">
                    ← Back to Puzzles
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ========== TIMER STATE VARIABLES ==========
    // timeRemaining: Seconds left in OVER//CLOCK mode (0 if Standard mode)
    // timerInterval: setInterval ID for clearing the timer when time expires
    let timeRemaining = <?php 
        if ($mode === 'overclock') {
            echo match($difficulty) {
                'base' => $puzzle['time_limit_base'],
                'turbo' => $puzzle['time_limit_turbo'],
                'hyper' => $puzzle['time_limit_hyper'],
                'thermal' => $puzzle['time_limit_thermal'],
                default => 0
            };
        } else {
            echo '0';
        }
    ?>;
    let timerInterval;
    
    // ========== PAGE INITIALIZATION ==========
    // Set up restrictions and start timer on page load if needed
    window.onload = function() {
        // Get game mode from page context (standard or overclock)
        const mode = '<?php echo $mode; ?>';
        
        // Start timer countdown only if OVER//CLOCK mode is active and time > 0
        if (mode === 'overclock' && timeRemaining > 0) {
            startTimer();
            
            // ========== TAB SWITCHING RESTRICTION ==========
            // Monitor if user leaves tab (restricts multi-tasking cheating)
            <?php if (in_array('notabs', $restrictions)): ?>
            document.addEventListener('visibilitychange', function() {
                // visibilitychange fires when tab loses/gains focus
                if (document.hidden) {
                    // Apply 30 second penalty when user switches away from tab
                    alert('Tab switching detected! Time penalty applied.');
                    timeRemaining = Math.max(0, timeRemaining - 30);
                }
            });
            <?php endif; ?>
        }
        
        // ========== HINT BUTTON RESTRICTION ==========
        // If the no-hints restriction is active, disable all hint buttons
        // This makes sure users can't cheat by checking hints when they picked that restriction
        <?php if (in_array('nohints', $restrictions)): ?>
        const hintButtons = document.querySelectorAll('.hint-button');
        hintButtons.forEach(btn => {
            btn.disabled = true;
            btn.title = 'Hints disabled in OVER//CLOCK mode';
        });
        <?php endif; ?>
        
        // ========== AUTO-SCROLL TO ANSWERED QUESTION ==========
        // When you submit an answer, the page scrolls down to that question so you can see the feedback
        <?php if ($answeredQuestionId): ?>
        const answeredQuestion = document.getElementById('question-<?php echo $answeredQuestionId; ?>');
        if (answeredQuestion) {
            // Smooth scroll to center the question on screen
            answeredQuestion.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        <?php endif; ?>
    };
    
    // ========== START TIMER COUNTDOWN ==========
    // When in OVER//CLOCK mode, this starts the countdown timer
    // Every second it ticks down and displays the remaining time
    function startTimer() {
        // Show the initial time right away
        updateTimerDisplay();
        
        // Set up an interval that runs every 1000 milliseconds (1 second)
        timerInterval = setInterval(function() {
            if (timeRemaining > 0) {
                // Subtract 1 second from the remaining time
                timeRemaining--;
                
                // Update what's shown on screen
                updateTimerDisplay();
                
                // If there's 30 seconds or less left, change the timer to red (warning)
                if (timeRemaining <= 30) {
                    document.getElementById('timer').classList.add('timer-warning');
                }
            } else {
                // Stop the timer and handle when time runs out
                clearInterval(timerInterval);
                handleTimeUp();
            }
        }, 1000);
    }
    
    // ========== UPDATE TIMER DISPLAY ==========
    // Takes timeRemaining (in seconds) and shows it as MM:SS format
    function updateTimerDisplay() {
        // Get minutes by dividing total seconds by 60
        const minutes = Math.floor(timeRemaining / 60);
        
        // Get remaining seconds (modulo 60 gives the remainder)
        const seconds = timeRemaining % 60;
        
        // Update the display using padStart to add leading zeros if needed (5 becomes 05)
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }
    
    // ========== HANDLE TIME EXPIRATION ==========
    // When the timer runs out, this locks the puzzle and sends you back
    function handleTimeUp() {
        // Tell the user time is up
        alert('Time\'s up! Your progress has been saved.');
        
        // Find all the answer input boxes on the page
        const inputs = document.querySelectorAll('input[name="answer"]');
        
        // Find all the submit buttons on the page
        const buttons = document.querySelectorAll('button[type="submit"]');
        
        // Disable all input boxes so user can't type anymore
        inputs.forEach(input => input.disabled = true);
        
        // Disable all buttons so user can't submit answers
        buttons.forEach(button => button.disabled = true);
        
        // Wait 2 seconds then send them back to the puzzle list
        setTimeout(() => {
            window.location.href = 'puzzles.php';
        }, 2000);
    }

    // ========== HINT TOGGLE FUNCTIONALITY ==========
    // Click the hint button to show/hide the hint for a question
    function toggleHint(questionId) {
        // If the no-hints restriction is active, don't let them see hints
        <?php if (in_array('nohints', $restrictions)): ?>
        alert('Hints are disabled in OVER//CLOCK mode with No Hints restriction!');
        return;
        <?php endif; ?>
        
        // Get the hint text element
        const hint = document.getElementById('hint-' + questionId);
        
        // Get the button that shows the hint
        const button = hint.previousElementSibling;
        
        // If the hint is hidden, show it
        if (hint.style.display === 'none') {
            hint.style.display = 'block';
            button.innerHTML = '💡 Hide Hint';
        } else {
            // If the hint is showing, hide it
            hint.style.display = 'none';
            button.innerHTML = '💡 Show Hint';
        }
    }

    // ========== SHAKE ANIMATION ON WRONG ANSWER ==========
    // When you get an answer wrong, the question card shakes left and right
    // This gives visual feedback that something went wrong
    <?php if ($messageType === 'danger' && $answeredQuestionId): ?>
    setTimeout(() => {
        // Get the question card that had the wrong answer
        const questionCard = document.getElementById('question-<?php echo $answeredQuestionId; ?>');
        if (questionCard) {
            // Add the shake class which triggers the shake animation in CSS
            questionCard.classList.add('shake');
            
            // Remove the class after 500ms so the animation is done and can be used again
            setTimeout(() => questionCard.classList.remove('shake'), 500);
        }
    }, 100);
    <?php endif; ?>
    </script>
</body>
</html>
