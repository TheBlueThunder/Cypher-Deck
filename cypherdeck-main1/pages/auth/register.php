<?php session_start();?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Cypher Deck</title>

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
    <h1 class="display-3 fw-bold">Register</h1>
    <p class="lead">Create your Cypher Deck account to join the puzzle-solving community.</p>

    <!-- Registration Form with Bootstrap Validation -->
    <form action="/cypherdeck-main1/pages/php/process_register.php" method="POST" 
          class="mt-4 needs-validation" novalidate>
      <?php require_once("../../pages/php/csrf_helper.php"); ?>
      <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
      
      <!-- Username -->
      <div class="mb-3">
        <input type="text" class="form-control form-control-lg" 
               name="username" placeholder="Username" required>
        <div class="invalid-feedback">
          Please enter a username.
        </div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <input type="email" class="form-control form-control-lg" 
               name="email" placeholder="Email" required>
        <div class="invalid-feedback">
          Please provide a valid email address.
        </div>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <input type="password" class="form-control form-control-lg" 
               name="password" placeholder="Password" required minlength="8" 
               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)"
               title="Password must be at least 8 characters with uppercase, lowercase, and number">
        <div class="invalid-feedback">
          Password must be at least 8 characters with uppercase, lowercase, and number.
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-lg" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
              onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
              onmouseout="this.style.boxShadow='none'">Register</button>
    </form>
    
    <p class="mt-4">Already have an account? 
      <a href="login.php" class="text-success">Login here</a>.
    </p>
  </div>
  </main>

  <!-- Bootstrap Validation Script -->
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
