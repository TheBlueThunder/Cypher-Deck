<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Check if we're finishing a burst rush session
if (!isset($_SESSION['burst_rush_puzzles']) || empty($_SESSION['burst_rush_puzzles'])) {
    header('Location: /cypherdeck-main1/pages/puzzles/burstrush.php');
    exit();
}

$userId = $_SESSION['user_id'];
$totalScore = $_SESSION['burst_rush_total_score'] ?? 0;
$totalCorrect = $_SESSION['burst_rush_total_correct'] ?? 0;
$totalQuestions = $_SESSION['burst_rush_total_questions'] ?? 0;
$maxStreak = $_SESSION['burst_rush_max_streak'] ?? 0;
$difficulty = $_SESSION['burst_rush_difficulty'];
$numPuzzles = count($_SESSION['burst_rush_puzzles']);

// Calculate accuracy percentage
$accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100) : 0;

// Record this session in database
$sql = "INSERT INTO SolvedPuzzles (user_id, puzzle_id, solved_at, mode, points_earned, streak_count, questions_correct, total_questions_in_session)
        VALUES (?, ?, NOW(), 'burstrush', ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        points_earned = VALUES(points_earned),
        streak_count = VALUES(streak_count),
        questions_correct = VALUES(questions_correct)";

// Record for each puzzle in the session
foreach ($_SESSION['burst_rush_puzzles'] as $puzzleId) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiii", $userId, $puzzleId, $totalScore, $maxStreak, $totalCorrect, $totalQuestions);
    $stmt->execute();
}

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

// Clear burst rush session data after recording
unset($_SESSION['burst_rush_puzzles']);
unset($_SESSION['burst_rush_difficulty']);
unset($_SESSION['burst_rush_start_time']);
unset($_SESSION['burst_rush_current_index']);
unset($_SESSION['burst_rush_total_score']);
unset($_SESSION['burst_rush_total_correct']);
unset($_SESSION['burst_rush_total_questions']);
unset($_SESSION['burst_rush_streak']);
unset($_SESSION['burst_rush_max_streak']);

// Determine performance rank
if ($accuracy === 100) {
    $rank = 'FLAWLESS';
    $rankColor = '#00ff66';
} elseif ($accuracy >= 80) {
    $rank = 'EXCELLENT';
    $rankColor = '#00bfff';
} elseif ($accuracy >= 60) {
    $rank = 'GOOD';
    $rankColor = '#ffc107';
} else {
    $rank = 'NEEDS IMPROVEMENT';
    $rankColor = '#dc3545';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burst Rush Results | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        .results-container {
            max-width: 800px;
            margin: 3rem auto;
        }

        .rank-display {
            font-size: 3rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 0 30px;
            letter-spacing: 3px;
        }

        .score-display {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: bold;
            color: #00ff66;
            text-shadow: 0 0 20px rgba(0, 255, 102, 0.5);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .result-card {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .result-card:hover {
            border-color: #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
        }

        .result-value {
            font-size: 2rem;
            font-weight: bold;
            color: #00ff66;
            margin-bottom: 0.5rem;
        }

        .result-label {
            color: #999;
            font-size: 0.9rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 0.75rem 1.5rem;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid #00ff66;
            background: rgba(0, 0, 0, 0.7);
            color: #00ff66;
            cursor: pointer;
        }

        .btn-custom:hover {
            background: rgba(0, 255, 102, 0.2);
            box-shadow: 0 0 20px rgba(0, 255, 102, 0.6);
            text-decoration: none;
            color: #00ff66;
        }

        .accuracy-bar {
            width: 100%;
            height: 30px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .accuracy-fill {
            height: 100%;
            background: linear-gradient(90deg, #00ff66, #00bfff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            font-size: 0.9rem;
            transition: width 1s ease;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container">
        <div class="results-container main-box">
            <h1 class="text-center mb-4" style="text-shadow: 0 0 30px rgba(0, 255, 102, 0.5);">BURST RUSH COMPLETE</h1>
            
            <!-- Rank display -->
            <div class="rank-display" style="color: <?php echo $rankColor; ?>; text-shadow: 0 0 30px <?php echo $rankColor; ?>;">
                <?php echo $rank; ?>
            </div>

            <!-- Final score -->
            <div class="score-display">
                FINAL SCORE: <?php echo number_format($totalScore); ?>
            </div>

            <!-- Results grid -->
            <div class="results-grid">
                <div class="result-card">
                    <div class="result-value"><?php echo $numPuzzles; ?></div>
                    <div class="result-label">Puzzles Completed</div>
                </div>
                <div class="result-card">
                    <div class="result-value"><?php echo $maxStreak; ?></div>
                    <div class="result-label">Max Streak</div>
                </div>
                <div class="result-card">
                    <div class="result-value"><?php echo $totalCorrect; ?>/<?php echo $totalQuestions; ?></div>
                    <div class="result-label">Questions Correct</div>
                </div>
                <div class="result-card">
                    <div class="result-value"><?php echo strtoupper($difficulty); ?></div>
                    <div class="result-label">Difficulty</div>
                </div>
            </div>

            <!-- Accuracy bar -->
            <div class="accuracy-bar">
                <div class="accuracy-fill" style="width: <?php echo $accuracy; ?>%;">
                    <?php echo $accuracy; ?>% Accuracy
                </div>
            </div>

            <!-- Action buttons -->
            <div class="button-group">
                <a href="/cypherdeck-main1/pages/puzzles/burstrush.php" class="btn-custom">
                    Try Again
                </a>
                <a href="/cypherdeck-main1/pages/auth/profile.php" class="btn-custom">
                    View Profile
                </a>
                <a href="/cypherdeck-main1/pages/puzzles/puzzles.php" class="btn-custom">
                    Browse Puzzles
                </a>
            </div>
        </div>
    </main>
</body>
</html>
