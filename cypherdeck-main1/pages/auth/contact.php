<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact | Cypher Deck</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Navbar container --> 
  <nav class="navbar navbar-expand-md navbar-dark bg-black border-bottom border-success"> 
    <div class="container-fluid">

      <!-- Brand/logo -->
      <a class="navbar-brand fw-bold" href="index.php">Cypher Deck</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
       aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span> 
      </button>

      <!-- Collapsible content, this is the stuff going into the hamburger menu -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-md-0">
        
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="pages/puzzles/puzzles.php">Puzzles</a></li>
          <li class="nav-item"><a class="nav-link" href="pages/puzzles/leaderboard.php">Leaderboard</a></li>
          <li class="nav-item"><a class="nav-link" href="pages/general/featured.php">Featured</a></li>
          <li class="nav-item"><a class="nav-link" href="pages/puzzles/overclock.php">OVER//CLOCK</a></li>
          <li class="nav-item"><a class="nav-link" href="pages/general/contact.php">Contact</a></li>

          <?php if (isset($_SESSION['username'])): ?>
            <li class="nav-item"><a class="nav-link" href="pages/auth/profile.php">Profile (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/auth/logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="pages/auth/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/auth/register.php">Register</a></li>
          <?php endif; ?>
          
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main content -->
  <main class="container text-center mt-5">
    <div class="main-box mx-auto">
      <h1 class="display-3 fw-bold">Contact</h1>
      <p class="lead">Have questions or feedback? Reach out to the Cypher Deck team and we'll get back to you as soon as possible.</p>
      <a href="#" class="btn btn-outline-success btn-lg mt-3">Send Message</a>
      <p class="mt-4">We value your input and are here to help!</p>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center mt-5 mb-3 text-secondary">
    <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
