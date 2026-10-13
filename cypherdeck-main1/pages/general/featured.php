<?php
session_start();
require_once("../php/process_db.php");

// Get featured puzzles from database
$sql = "SELECT p.*, u.username as creator_name FROM PuzzleStore p 
        JOIN Users u ON p.created_by = u.id 
        WHERE p.is_featured = 1 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$featuredPuzzles = [];
while ($row = $result->fetch_assoc()) {
    $featuredPuzzles[] = $row;
}

// Get user's solved puzzles if logged in
$solvedPuzzles = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT puzzle_id FROM SolvedPuzzles WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $solvedResult = $stmt->get_result();
    while ($row = $solvedResult->fetch_assoc()) {
        $solvedPuzzles[$row['puzzle_id']] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Puzzles | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.php">
    <link rel="stylesheet" href="../../css/style_puzzles.php">
    <style>
        .featured-header {
            background: linear-gradient(135deg, rgba(0, 255, 102, 0.1), rgba(0, 191, 255, 0.1));
            border: 2px solid #00ff66;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 255, 102, 0.3);
        }

        .featured-header h1 {
            color: #00ff66;
            text-shadow: 0 0 20px rgba(0, 255, 102, 0.5);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .featured-subtext {
            color: #ddd;
            font-size: 1.1rem;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .puzzle-card {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #00ff66;
            border-radius: 8px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .puzzle-card:hover {
            border-color: #ffc107;
            box-shadow: 0 0 30px rgba(255, 193, 7, 0.5);
            transform: translateY(-5px);
        }

        .puzzle-card.solved {
            border-color: #28a745;
        }

        .puzzle-card.solved::before {
            content: '✓ Solved';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #28a745;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .puzzle-title {
            color: #00ff66;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .puzzle-creator {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .puzzle-category {
            display: inline-block;
            background: rgba(0, 255, 102, 0.2);
            color: #00ff66;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            border: 1px solid #00ff66;
        }

        .puzzle-difficulty {
            display: inline-block;
            margin-left: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .puzzle-difficulty.easy {
            background: rgba(0, 255, 102, 0.2);
            color: #00ff66;
            border: 1px solid #00ff66;
        }

        .puzzle-difficulty.medium {
            background: rgba(0, 191, 255, 0.2);
            color: #00bfff;
            border: 1px solid #00bfff;
        }

        .puzzle-difficulty.hard {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .puzzle-difficulty.extreme {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .puzzle-description {
            color: #ddd;
            font-size: 0.95rem;
            margin: 1rem 0;
            line-height: 1.5;
            min-height: 60px;
        }

        .puzzle-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #444;
            font-size: 0.85rem;
        }

        .stat {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            color: #00ff66;
            font-weight: bold;
            font-size: 1rem;
        }

        .stat-label {
            color: #999;
        }

        .play-btn {
            width: 100%;
            margin-top: 1rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #00ff66, #00bfff);
            color: #000;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(0, 255, 102, 0.6);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state h3 {
            color: #ddd;
            margin-bottom: 1rem;
        }

        .star-badge {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="main-box mx-auto" style="max-width: 1200px;">
            <!-- Featured header -->
            <div class="featured-header">
                <h1>⭐ FEATURED PUZZLES</h1>
                <p class="featured-subtext">Discover hand-picked challenges from the Cypher Deck community</p>
            </div>

            <!-- Featured puzzles grid -->
            <?php if (count($featuredPuzzles) > 0): ?>
                <div class="featured-grid">
                    <?php foreach ($featuredPuzzles as $puzzle): ?>
                        <div class="puzzle-card <?php echo isset($solvedPuzzles[$puzzle['id']]) ? 'solved' : ''; ?>">
                            <div class="star-badge">★ FEATURED</div>
                            
                            <h3 class="puzzle-title"><?php echo htmlspecialchars($puzzle['title']); ?></h3>
                            <div class="puzzle-creator">by <?php echo htmlspecialchars($puzzle['creator_name']); ?></div>
                            
                            <!-- Category and difficulty -->
                            <div>
                                <span class="puzzle-category"><?php echo htmlspecialchars($puzzle['category']); ?></span>
                                <span class="puzzle-difficulty <?php echo strtolower($puzzle['difficulty']); ?>">
                                    <?php echo htmlspecialchars($puzzle['difficulty']); ?>
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="puzzle-description">
                                <?php echo htmlspecialchars(substr($puzzle['description'], 0, 100)); ?>...
                            </div>

                            <!-- Stats -->
                            <div class="puzzle-stats">
                                <div class="stat">
                                    <div class="stat-value"><?php echo $puzzle['base_points']; ?></div>
                                    <div class="stat-label">Points</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-value"><?php echo count(json_decode($puzzle['question_count'] ?? '[]', true)) ?? 0; ?></div>
                                    <div class="stat-label">Questions</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-value"><?php echo $puzzle['is_burstrush'] ? 'Yes' : 'No'; ?></div>
                                    <div class="stat-label">Burst Rush</div>
                                </div>
                            </div>

                            <!-- Play button -->
                            <button class="play-btn" onclick="window.location.href='/cypherdeck-main1/pages/puzzles/solve_puzzle.php?id=<?php echo $puzzle['id']; ?>&mode=standard'">
                                Play Now
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Featured Puzzles Yet</h3>
                    <p>Admins can feature puzzles to highlight them here.</p>
                    <a href="/cypherdeck-main1/pages/puzzles/puzzles.php" class="btn btn-outline-success mt-3">Browse All Puzzles</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
