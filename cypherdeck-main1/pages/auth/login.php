<?php session_start();?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Cypher Deck</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../css/style.php">
</head>

<body>

  <!-- Navbar container --> 
  <?php require_once("../../pages/templates/navbar.php"); ?>

  <!-- Main content -->
  <main class="container text-center mt-5">
    <div class="main-box mx-auto">
      <h1 class="display-3 fw-bold">Login</h1>
      <p class="lead">Log in to your Cypher Deck account to track your progress, save your favorite puzzles, and compete on the leaderboard.</p>
      
      <!-- Login Form -->
      <form action="../php/process_login.php" method="POST" class="mt-4 needs-validation" novalidate>
        <?php require_once("../php/csrf_helper.php"); ?>
        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
      
        <!-- Username -->
        <div class="mb-3">
          <input type="text" class="form-control form-control-lg" 
                name="username" placeholder="Username" required>
          <div class="invalid-feedback">
            Please enter a username.
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <input type="password" class="form-control form-control-lg" 
                name="password" placeholder="Password" required minlength="6">
          <div class="invalid-feedback">
            Please enter a password (at least 6 characters).
          </div>
        </div>

        <button type="submit" class="btn btn-lg" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
                onmouseout="this.style.boxShadow='none'">Login</button>
      </form>
      
      <p class="mt-4">Don't have an account? <a href="register.php" class="text-success">Register here</a>.</p>
    </div>
  </main>

  <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')

      Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
  </script>

  <!-- Footer -->
  <footer class="text-center mt-5 mb-3 text-secondary">
    <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
