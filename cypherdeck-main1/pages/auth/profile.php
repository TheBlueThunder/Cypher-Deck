<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user info
$userSql = "SELECT username, email FROM Users WHERE id = ?";
$userStmt = $conn->prepare($userSql);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userInfo = $userResult->fetch_assoc();

// Get total puzzles solved
$totalSql = "SELECT COUNT(*) as total FROM SolvedPuzzles WHERE user_id = ?";
$totalStmt = $conn->prepare($totalSql);
$totalStmt->bind_param("i", $userId);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalPuzzles = $totalResult->fetch_assoc()['total'] ?? 0;

// Get total points
$pointsSql = "SELECT SUM(points_earned) as total_points FROM SolvedPuzzles WHERE user_id = ?";
$pointsStmt = $conn->prepare($pointsSql);
$pointsStmt->bind_param("i", $userId);
$pointsStmt->execute();
$pointsResult = $pointsStmt->get_result();
$totalPoints = $pointsResult->fetch_assoc()['total_points'] ?? 0;

// Get highest single puzzle score
$highestSql = "SELECT MAX(points_earned) as highest_score FROM SolvedPuzzles WHERE user_id = ?";
$highestStmt = $conn->prepare($highestSql);
$highestStmt->bind_param("i", $userId);
$highestStmt->execute();
$highestResult = $highestStmt->get_result();
$highestScore = $highestResult->fetch_assoc()['highest_score'] ?? 0;

// Get best streak
$streakSql = "SELECT MAX(streak_count) as best_streak FROM SolvedPuzzles WHERE user_id = ? AND streak_count IS NOT NULL";
$streakStmt = $conn->prepare($streakSql);
$streakStmt->bind_param("i", $userId);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();
$bestStreak = $streakResult->fetch_assoc()['best_streak'] ?? 0;

// Get recent solved puzzles
$recentSql = "SELECT sp.puzzle_id, sp.solved_at, sp.points_earned, sp.mode, ps.title, ps.difficulty
              FROM SolvedPuzzles sp
              JOIN PuzzleStore ps ON sp.puzzle_id = ps.id
              WHERE sp.user_id = ?
              ORDER BY sp.solved_at DESC
              LIMIT 10";
$recentStmt = $conn->prepare($recentSql);
$recentStmt->bind_param("i", $userId);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();
$recentSolves = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentSolves[] = $row;
}

// Get mode breakdown
$modeSql = "SELECT mode, COUNT(*) as count, SUM(points_earned) as total FROM SolvedPuzzles WHERE user_id = ? GROUP BY mode";
$modeStmt = $conn->prepare($modeSql);
$modeStmt->bind_param("i", $userId);
$modeStmt->execute();
$modeResult = $modeStmt->get_result();
$modeStats = [];
while ($row = $modeResult->fetch_assoc()) {
    $modeStats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        .stat-card {
            background: rgba(0, 0, 0, 0.6);
            border: 2px solid #444;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00ff66;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }

        .user-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(0, 255, 102, 0.05);
            border: 2px solid #00ff66;
            border-radius: 8px;
        }

        .user-name {
            font-size: 2rem;
            color: #00ff66;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
            margin-bottom: 0.5rem;
        }

        .user-email {
            color: #999;
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 1.3rem;
            color: #00ff66;
            text-transform: uppercase;
            border-bottom: 2px solid #00ff66;
            padding-bottom: 0.5rem;
            margin: 2rem 0 1rem 0;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
        }

        .recent-solve {
            background: rgba(0, 0, 0, 0.5);
            border-left: 4px solid #00ff66;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .recent-solve:hover {
            box-shadow: 0 0 10px rgba(0, 255, 102, 0.2);
            transform: translateX(5px);
        }

        .solve-title {
            font-weight: bold;
            color: #00ff66;
            margin-bottom: 0.3rem;
        }

        .solve-meta {
            font-size: 0.85rem;
            color: #999;
        }

        .solve-points {
            color: #ffc107;
            font-weight: bold;
        }

        .mode-badge {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            background: rgba(0, 255, 102, 0.2);
            border: 1px solid #00ff66;
            border-radius: 3px;
            font-size: 0.8rem;
            color: #00ff66;
        }

        .mode-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .logout-btn {
            width: 100%;
            padding: 0.8rem;
            margin-top: 2rem;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid #dc3545;
            color: #dc3545;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.2);
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.6);
            color: #dc3545;
            text-decoration: none;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>

    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="main-box mx-auto" style="max-width: 900px;">

            <!-- User Header -->
            <div class="user-header">
                <div class="user-name"><?php echo htmlspecialchars($userInfo['username'] ?? 'Unknown'); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($userInfo['email'] ?? 'No email'); ?></div>
            </div>

            <!-- Main Stats Grid -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalPuzzles; ?></div>
                        <div class="stat-label">Puzzles Solved</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($totalPoints); ?></div>
                        <div class="stat-label">Total Points</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $highestScore; ?></div>
                        <div class="stat-label">Highest Score</div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $bestStreak; ?></div>
                        <div class="stat-label">Best Streak</div>
                    </div>
                </div>
            </div>

            <!-- Mode Breakdown -->
            <?php if (count($modeStats) > 0): ?>
                <div class="section-title">Mode Breakdown</div>
                <div class="mode-stats">
                    <?php foreach ($modeStats as $stat): ?>
                        <div class="stat-card">
                            <div style="color: #00ff66; text-transform: uppercase; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($stat['mode']); ?>
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <span style="color: #999;">Solved:</span> <span style="color: #00ff66; font-weight: bold;"><?php echo $stat['count']; ?></span>
                            </div>
                            <div>
                                <span style="color: #999;">Points:</span> <span style="color: #ffc107; font-weight: bold;"><?php echo number_format($stat['total'] ?? 0); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Solves -->
            <?php if (count($recentSolves) > 0): ?>
                <div class="section-title">Recent Solves</div>
                <?php foreach ($recentSolves as $solve): ?>
                    <div class="recent-solve">
                        <div class="solve-title"><?php echo htmlspecialchars($solve['title']); ?></div>
                        <div class="solve-meta">
                            <span class="mode-badge"><?php echo htmlspecialchars($solve['mode']); ?></span>
                            <span style="color: #999; margin-left: 1rem;">
                                <?php echo date('M d, Y H:i', strtotime($solve['solved_at'])); ?>
                            </span>
                            <span class="solve-points" style="float: right;">
                                +<?php echo $solve['points_earned']; ?> pts
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center mt-3">No puzzles solved yet. Start solving to build your profile!</p>
            <?php endif; ?>

            <!-- Logout Button -->
            <a href="logout.php" class="logout-btn">LOGOUT</a>

        </div>
    </main>

    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
