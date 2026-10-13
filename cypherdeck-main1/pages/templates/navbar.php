<nav class="navbar navbar-expand-md navbar-dark bg-black border-bottom border-success"> 
    <div class="container-fluid">

      <!-- Brand/logo -->
      <a class="navbar-brand fw-bold" href="/cypherdeck-main1/index.php">Cypher Deck</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
       aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span> 
      </button>

      <!-- Collapsible content, this is the stuff going into the hamburger menu -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
          <li class="nav-item"><a class="nav-link active" href="/cypherdeck-main1/index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/puzzles/puzzles.php">Puzzles</a></li>
          <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/puzzles/leaderboard.php">Leaderboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/puzzles/burstrush.php">BURST RUSH</a></li>
          <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/social/rooms.php">Social</a></li>
          <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/general/contact.php">Contact</a></li>

          <?php if (isset($_SESSION['username'])): ?>
            <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/auth/profile.php">Profile (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/auth/admin.php" style="color: #ffc107;">⚙️ Admin</a></li>
              <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/puzzles/create_puzzle.php">Create Puzzles</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/php/process_logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/auth/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/cypherdeck-main1/pages/auth/register.php">Register</a></li>
          <?php endif; ?>
          
        </ul>
      </div>
    </div>
</nav>
  
