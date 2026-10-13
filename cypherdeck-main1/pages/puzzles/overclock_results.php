<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Get result data from URL parameters
$totalPoints = (int)($_GET['points'] ?? 0);
$difficulty = $_GET['difficulty'] ?? 'base';
$puzzleId = (int)($_GET['puzzle_id'] ?? 0);
$totalCorrect = (int)($_GET['correct'] ?? 0);
$totalQuestions = (int)($_GET['total'] ?? 1);

// Get puzzle title
$sql = "SELECT title, base_points FROM PuzzleStore WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $puzzleId);
$stmt->execute();
$puzzleData = $stmt->get_result()->fetch_assoc();

$puzzleTitle = $puzzleData['title'] ?? 'Unknown Puzzle';
$basePoints = $puzzleData['base_points'] ?? 0;

// Calculate accuracy percentage
$accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100) : 0;

// Determine difficulty multiplier
$multipliers = [
    'base' => 1.2,
    'turbo' => 1.5,
    'hyper' => 2.0,
    'thermal' => 2.5
];
$multiplier = $multipliers[$difficulty] ?? 1.0;

// Calculate base score before multiplier
$baseScore = $basePoints * $totalCorrect;
$finalScore = round($baseScore * $multiplier);

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

// Difficulty colors
$difficultyColors = [
    'base' => '#00ff66',
    'turbo' => '#00bfff',
    'hyper' => '#ffc107',
    'thermal' => '#dc3545'
];
$themeColor = $difficultyColors[$difficulty] ?? '#00ff66';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OVER//CLOCK Results | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        body {
            --theme-color: <?php echo $themeColor; ?>;
        }

        .results-container {
            max-width: 800px;
            margin: 3rem auto;
        }

        .results-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid var(--theme-color);
            border-radius: 8px;
            box-shadow: 0 0 30px var(--theme-color);
        }

        .results-header h1 {
            color: var(--theme-color);
            text-shadow: 0 0 20px var(--theme-color);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .puzzle-title {
            color: #ddd;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .difficulty-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--theme-color);
            color: #000;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }

        .rank-display {
            font-size: 3rem;
            font-weight: bold;
            text-align: center;
            margin: 2rem 0;
            text-shadow: 0 0 30px;
            letter-spacing: 3px;
        }

        .score-display {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: bold;
            color: #ffc107;
            text-shadow: 0 0 20px rgba(255, 193, 7, 0.5);
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
            border-color: var(--theme-color);
            box-shadow: 0 0 15px var(--theme-color);
        }

        .result-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--theme-color);
            margin-bottom: 0.5rem;
        }

        .result-label {
            color: #999;
            font-size: 0.9rem;
        }

        .breakdown-box {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #333;
            color: #ddd;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            color: #999;
        }

        .breakdown-value {
            color: #ffc107;
            font-weight: bold;
        }

        .accuracy-bar {
            width: 100%;
            height: 40px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .accuracy-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--theme-color), #ffc107);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            transition: width 1s ease;
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
            border: 2px solid var(--theme-color);
            background: rgba(0, 0, 0, 0.7);
            color: var(--theme-color);
            cursor: pointer;
        }

        .btn-custom:hover {
            background: var(--theme-color);
            color: #000;
            text-decoration: none;
            box-shadow: 0 0 20px var(--theme-color);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .results-container {
            animation: slideIn 0.6s ease;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container">
        <div class="results-container main-box">
            <div class="results-header">
                <h1>OVER//CLOCK RESULTS</h1>
                <div class="puzzle-title"><?php echo htmlspecialchars($puzzleTitle); ?></div>
                <div class="difficulty-badge" style="background: <?php echo $themeColor; ?>;">
                    <?php echo htmlspecialchars(strtoupper($difficulty)); ?> Difficulty
                </div>
            </div>

            <!-- Rank display -->
            <div class="rank-display" style="color: <?php echo $rankColor; ?>; text-shadow: 0 0 30px <?php echo $rankColor; ?>;">
                <?php echo $rank; ?>
            </div>

            <!-- Final score -->
            <div class="score-display">
                FINAL SCORE: <?php echo number_format($finalScore); ?> 💎
            </div>

            <!-- Results grid -->
            <div class="results-grid">
                <div class="result-card">
                    <div class="result-value"><?php echo $totalCorrect; ?>/<?php echo $totalQuestions; ?></div>
                    <div class="result-label">Questions Correct</div>
                </div>
                <div class="result-card">
                    <div class="result-value"><?php echo $accuracy; ?>%</div>
                    <div class="result-label">Accuracy</div>
                </div>
            </div>

            <!-- Accuracy bar -->
            <div class="accuracy-bar">
                <div class="accuracy-fill" style="width: <?php echo $accuracy; ?>%;">
                    <?php echo $accuracy; ?>% Accuracy
                </div>
            </div>

            <!-- Score breakdown -->
            <div class="breakdown-box">
                <h4 style="color: var(--theme-color); margin-bottom: 1rem;">Score Breakdown</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Base Points per Question</span>
                    <span class="breakdown-value"><?php echo $basePoints; ?> pts</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Correct Answers</span>
                    <span class="breakdown-value">× <?php echo $totalCorrect; ?></span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Base Score</span>
                    <span class="breakdown-value"><?php echo number_format($baseScore); ?> pts</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Difficulty Multiplier</span>
                    <span class="breakdown-value">× <?php echo $multiplier; ?>x</span>
                </div>
                <div class="breakdown-item" style="border-top: 2px solid var(--theme-color); padding-top: 1rem; margin-top: 0.5rem;">
                    <span class="breakdown-label" style="font-weight: bold;">FINAL SCORE</span>
                    <span class="breakdown-value" style="font-size: 1.2rem;"><?php echo number_format($finalScore); ?> 💎</span>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="button-group">
                <a href="/cypherdeck-main1/pages/puzzles/puzzles.php" class="btn-custom">
                    Browse More Puzzles
                </a>
                <a href="/cypherdeck-main1/pages/auth/profile.php" class="btn-custom">
                    View Profile
                </a>
                <a href="/cypherdeck-main1/pages/puzzles/leaderboard.php" class="btn-custom">
                    Check Leaderboard
                </a>
            </div>
        </div>
    </main>
</body>
</html>
