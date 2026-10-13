<?php
session_start();
require_once("../php/process_db.php");

// ========== DETERMINE ACTIVE VIEW ==========
// Check which leaderboard view user is requesting (defaults to 'alltime')
$view = $_GET['view'] ?? 'alltime';
$validViews = ['alltime', 'standard', 'overclock', 'burstrush'];
if (!in_array($view, $validViews)) {
    $view = 'alltime';
}

// ========== BUILD DYNAMIC SQL QUERY ==========
// Each view requires different SQL logic based on the game mode

if ($view === 'alltime') {
    // All-time leaderboard: total points across all modes
    $sql = "
        SELECT 
            u.id,
            u.username,
            COUNT(DISTINCT sp.puzzle_id) as puzzles_solved,
            COALESCE(SUM(sp.points_earned), 0) as total_points
        FROM Users u
        LEFT JOIN SolvedPuzzles sp ON u.id = sp.user_id
        GROUP BY u.id, u.username
        HAVING total_points > 0 OR puzzles_solved > 0
        ORDER BY total_points DESC, puzzles_solved DESC, u.username ASC
        LIMIT 20
    ";
    $viewTitle = "All-Time Rankings";
    $viewDesc = "Total points across all game modes";
} elseif ($view === 'standard') {
    // Standard mode leaderboard: points from standard puzzles only
    $sql = "
        SELECT 
            u.id,
            u.username,
            COUNT(DISTINCT sp.puzzle_id) as puzzles_solved,
            COALESCE(SUM(sp.points_earned), 0) as total_points
        FROM Users u
        LEFT JOIN SolvedPuzzles sp ON u.id = sp.user_id AND sp.mode = 'standard'
        GROUP BY u.id, u.username
        HAVING total_points > 0 OR puzzles_solved > 0
        ORDER BY total_points DESC, puzzles_solved DESC, u.username ASC
        LIMIT 20
    ";
    $viewTitle = "Standard Mode Rankings";
    $viewDesc = "Race against others in standard puzzle mode";
} elseif ($view === 'overclock') {
    // Overclock mode leaderboard: points from timed puzzles
    $sql = "
        SELECT 
            u.id,
            u.username,
            COUNT(DISTINCT sp.puzzle_id) as puzzles_solved,
            COALESCE(SUM(sp.points_earned), 0) as total_points,
            MAX(sp.points_earned) as best_single_score
        FROM Users u
        LEFT JOIN SolvedPuzzles sp ON u.id = sp.user_id AND sp.mode = 'overclock'
        GROUP BY u.id, u.username
        HAVING total_points > 0 OR puzzles_solved > 0
        ORDER BY total_points DESC, puzzles_solved DESC, u.username ASC
        LIMIT 20
    ";
    $viewTitle = "Overclock Mode Rankings";
    $viewDesc = "Who can handle the pressure? Fastest timed solves";
} else {
    // Burst Rush mode leaderboard: points from rapid-fire sessions
    $sql = "
        SELECT 
            u.id,
            u.username,
            COUNT(DISTINCT sp.puzzle_id) as puzzles_solved,
            COALESCE(SUM(sp.points_earned), 0) as total_points,
            MAX(sp.streak_count) as best_streak
        FROM Users u
        LEFT JOIN SolvedPuzzles sp ON u.id = sp.user_id AND sp.mode = 'burstrush'
        GROUP BY u.id, u.username
        HAVING total_points > 0 OR puzzles_solved > 0
        ORDER BY total_points DESC, puzzles_solved DESC, u.username ASC
        LIMIT 20
    ";
    $viewTitle = "Burst Rush Mode Rankings";
    $viewDesc = "The fastest fingers and sharpest minds win";
}

// ========== EXECUTE LEADERBOARD QUERY ==========
// Run the dynamically built query and fetch results
$result = $conn->query($sql);
$leaderboardData = [];
if ($result) {
    $leaderboardData = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leaderboard | Cypher Deck</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../css/style.php" rel="stylesheet" type="text/css">
  <style>
    .nav-pills .nav-link {
      color: #aaa;
      border-radius: 0;
      border-bottom: 2px solid transparent;
      margin: 0 0.5rem;
    }
    .nav-pills .nav-link.active {
      background-color: transparent;
      color: #00ff66;
      border-bottom: 2px solid #00ff66;
    }
    .nav-pills .nav-link:hover {
      color: #00ff66;
    }
    .rank-badge {
      display: inline-block;
      width: 40px;
      height: 40px;
      line-height: 40px;
      text-align: center;
      border-radius: 50%;
      background: rgba(0, 255, 150, 0.1);
      border: 1px solid rgba(0, 255, 150, 0.3);
      font-weight: bold;
      color: #00ff66;
    }
    .rank-1 {
      background: rgba(255, 215, 0, 0.2);
      border-color: #ffd700;
      color: #ffd700;
    }
    .rank-2 {
      background: rgba(192, 192, 192, 0.2);
      border-color: #c0c0c0;
      color: #c0c0c0;
    }
    .rank-3 {
      background: rgba(205, 127, 50, 0.2);
      border-color: #cd7f32;
      color: #cd7f32;
    }
    .leaderboard-row {
      display: grid;
      grid-template-columns: 50px 1fr 150px 150px;
      gap: 1rem;
      align-items: center;
      padding: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      transition: background 0.2s;
    }
    .leaderboard-row:hover {
      background: rgba(0, 255, 150, 0.05);
    }
    .leaderboard-header {
      display: grid;
      grid-template-columns: 50px 1fr 150px 150px;
      gap: 1rem;
      padding: 1rem;
      font-weight: bold;
      border-bottom: 2px solid rgba(0, 255, 150, 0.3);
      color: #00ff66;
      text-transform: uppercase;
      font-size: 0.85rem;
    }
    .username-cell {
      font-weight: 600;
    }
    .score-cell {
      text-align: right;
      color: #00ff66;
      font-size: 1.1rem;
    }
    .info-cell {
      text-align: right;
      font-size: 0.9rem;
      color: #aaa;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <?php require_once("../../pages/templates/navbar.php"); ?>

  <!-- Main content -->
  <main class="container mt-5 mb-5">
    <div class="main-box mx-auto" style="max-width: 900px;">

      <!-- Title -->
      <h1 class="display-4 fw-bold text-center mb-2">Leaderboard</h1>
      <p class="text-center text-success mb-4">See how you rank against other codebreakers!</p>

      <!-- View Selection Tabs -->
      <ul class="nav nav-pills justify-content-center mb-4 flex-wrap">
        <li class="nav-item">
          <a class="nav-link <?php echo $view === 'alltime' ? 'active' : ''; ?>" href="?view=alltime">
            All-Time
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $view === 'standard' ? 'active' : ''; ?>" href="?view=standard">
            Standard Mode
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $view === 'overclock' ? 'active' : ''; ?>" href="?view=overclock">
            Overclock Mode
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $view === 'burstrush' ? 'active' : ''; ?>" href="?view=burstrush">
            Burst Rush Mode
          </a>
        </li>
      </ul>

      <!-- Current View Title and Description -->
      <div class="text-center mb-4">
        <h2 class="h4 fw-bold"><?php echo $viewTitle; ?></h2>
        <p class="text-success"><?php echo $viewDesc; ?></p>
      </div>

      <!-- Leaderboard Table -->
      <?php if (count($leaderboardData) > 0): ?>
        <div class="leaderboard-header">
          <div>#</div>
          <div>Player</div>
          <div style="text-align: right;">Score</div>
          <div style="text-align: right;">Details</div>
        </div>

        <?php foreach ($leaderboardData as $index => $player): ?>
          <div class="leaderboard-row">
            <div>
              <div class="rank-badge 
                <?php 
                  if ($index + 1 === 1) echo 'rank-1';
                  elseif ($index + 1 === 2) echo 'rank-2';
                  elseif ($index + 1 === 3) echo 'rank-3';
                ?>">
                <?php echo $index + 1; ?>
              </div>
            </div>
            <div class="username-cell">
              <?php echo htmlspecialchars($player['username']); ?>
            </div>
            <div class="score-cell">
              <?php echo number_format($player['total_points']); ?>
            </div>
            <div class="info-cell">
              <?php 
                if ($view === 'burstrush' && isset($player['best_streak'])) {
                  echo '🔥 ' . $player['best_streak'] . ' | ' . $player['puzzles_solved'] . ' 🧩';
                } elseif ($view === 'overclock' && isset($player['best_single_score'])) {
                  echo $player['puzzles_solved'] . ' ⏱ | Best: ' . $player['best_single_score'];
                } else {
                  echo $player['puzzles_solved'] . ' puzzles';
                }
              ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info text-center py-5">
          <h5>No scores recorded yet</h5>
          <p class="mb-0">Be the first to compete in <?php echo strtolower($viewTitle); ?>!</p>
        </div>
      <?php endif; ?>

      <!-- Back Button -->
      <div class="text-center mt-5">
        <a href="../../index.php" class="btn" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
           onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
           onmouseout="this.style.boxShadow='none'">Back to Dashboard</a>
      </div>

    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center mt-5 mb-3 text-secondary">
    <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // switch between different leaderboard views
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', function(e) {
        // bootstrap handles the active state for us based on the url
      });
    });
  </script>

</body>

</html>
