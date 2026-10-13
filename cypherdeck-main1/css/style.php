<?php
header('Content-Type: text/css');
?>
<?php
header('Content-Type: text/css');
header('X-Content-Type-Options: nosniff');
?>
:root {
   --theme-color: #00ff66;
 }

body {
  background-color: #000000; /* Dark gray instead of black */
  color: #00ff66; 
  font-family: "Courier New", monospace;
}

/* Navbar styling */
.navbar {
  border-bottom: 2px solid #00ff66;
  /* Add subtle glow effect to the border */
  box-shadow: 0 2px 10px #00ff66;
}

.navbar-brand {
  color: var(--theme-color);
  text-shadow: 0 0 5px var(--theme-color), 0 0 10px var(--theme-color), 0 0 20px var(--theme-color);
  font-family: "Courier New", monospace;
  transition: color 0.3s, text-shadow 0.3s;
}

h1 {
  text-shadow: 0 0 5px var(--theme-color), 0 0 10px var(--theme-color), 0 0 20px var(--theme-color);
  font-family: "Courier New", monospace;
}

/* Button hover effect */
.btn-outline-success:hover {
  background-color: var(--theme-color);
  color: #000;
}

.main-box {
  background-color: #000; /* The inside of the box is black */
  border: 2px solid var(--theme-color); /* This makes the border glow */
  border-radius: 0.5rem; /* Standard rounding of the container */
  padding: 2rem;
  box-shadow: 0 0 20px var(--theme-color); /* subtle glow around the box */
  transition: border-color 0.3s, box-shadow 0.3s;
}

.accordion-button {
  background-color: #000;
  color: var(--theme-color);
  border: 1px solid var(--theme-color);
}
.accordion-button:not(.collapsed) {
  background-color: #111;
  color: #00ff66;
  box-shadow: 0 0 10px var(--theme-color);
}
.accordion-body {
  background-color: #000;
  color: #00ff66;
  border-left: 2px solid var(--theme-color);
}

</style>