<?php session_start(); 
require_once("pages/php/session_security.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cypher Deck</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
  <link rel="stylesheet" href="css/style.php">
</head>
<body>

  <?php require_once("pages/templates/navbar.php"); ?> 

  <main class="container text-center mt-5">
    <div class="main-box mx-auto p-5">
      <?php if (isset($_SESSION['username'])): ?>
        <h1 class="display-3 fw-bold mb-3">Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>.</h1>
      <?php else: ?>
        <h1 class="display-3 fw-bold mb-3">Welcome to <span class="text-success">Cypher Deck</span></h1>
      <?php endif; ?>

      <p class="lead mb-4">The digital battlefield of puzzles, ciphers, and timed challenges — where logic meets speed.</p>
      <hr style="border-color: rgba(0,255,102,0.2);">

      <div class="text-start mx-auto" style="max-width: 800px;">
        <p>
          Cypher Deck is a gamified cybersecurity platform built to train problem-solving and mental agility through cryptographic and logic-based challenges.
          Every solve earns you points — but timers, restrictions, and penalties raise the stakes.
        </p>
        <p>
          <strong>Overclock Mode</strong> transforms any full puzzle into a race against the clock. Time penalties apply for switching tabs or hesitating too long.
        </p>
        <p>
          <strong>Burst Rush</strong> pushes the concept further: a randomized stream of mini-puzzles under constant pressure. Keep the streak alive to boost your score multiplier.
        </p>
        <p>
          Track your victories and climb the <strong>Global Leaderboard</strong>. Each second counts — every puzzle solved adds to your reputation across the Deck.
        </p>
      </div>

      <div class="row mt-5 g-4 justify-content-center">
        <div class="col-md-4">
          <div class="mode-card p-4 border rounded-3 h-100">
            <h3 class="fw-bold text-success">Standard Mode</h3>
            <p>Solve at your own pace. Perfect for learning, testing, and refining techniques before the timers start.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mode-card p-4 border rounded-3 h-100">
            <h3 class="fw-bold text-warning">Overclock Mode</h3>
            <p>A timer-driven challenge mode for full puzzles. Manage time, accuracy, and mental composure under pressure.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mode-card p-4 border rounded-3 h-100">
            <h3 class="fw-bold text-info">Burst Rush</h3>
            <p>Face a cascade of mini-puzzles. Stay fast, stay sharp — every mistake costs your streak and your bonus points.</p>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <a href="pages/puzzles/puzzles.php" class="btn btn-lg me-3" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
           onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
           onmouseout="this.style.boxShadow='none'">Enter the Deck</a>
        <a href="pages/puzzles/leaderboard.php" class="btn btn-lg" style="border: 2px solid #ffc107; color: #ffc107; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
           onmouseover="this.style.boxShadow='0 0 20px rgba(255,193,7,0.6)'" 
           onmouseout="this.style.boxShadow='none'">View Leaderboard</a> 
      </div>
    </div>
  </main>

  <footer class="text-center mt-5 mb-3 text-secondary">
    <small>&copy; <?php echo date('Y'); ?> The Dealer's Hand — All Rights Reserved.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  
  </script>

  <style>
    .mode-card {
      background: rgba(255,255,255,0.05);
      border: 2px solid rgba(0,255,102,0.3);
      transition: all 0.3s ease;
    }
    .mode-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 15px rgba(0,255,102,0.3);
    }
  </style>
</body>
</html>
