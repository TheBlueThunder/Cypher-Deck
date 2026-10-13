// Toggle Overclock menu visibility, showing difficulty selection with fade animation and updating puzzle card styling
function toggleOverclock(puzzleId) {
  // Get the overclock menu DOM element for this puzzle
  const overclockMenu = document.getElementById('overclock-' + puzzleId);
  // Get the puzzle card to apply CSS classes that change border glow color
  const puzzleCard = document.getElementById('puzzle-' + puzzleId);
    
  // Show and fade in the menu if it's currently hidden
  if (overclockMenu.style.display === 'none') {
    // Set opacity to 0 and display block to prepare for CSS transition fade-in effect
    overclockMenu.style.opacity = '0';
    overclockMenu.style.display = 'block';
    
    // Small delay (10ms) allows browser to apply display:block before opacity change, triggering CSS transition
    setTimeout(() => {
      overclockMenu.style.opacity = '1';
    }, 10);
    
    // Remove old difficulty CSS classes from card before applying new one
    puzzleCard.classList.remove('overclock-base', 'overclock-turbo', 'overclock-hyper', 'overclock-thermal');
    // Add base difficulty class which applies green glow styling to card border
    puzzleCard.classList.add('overclock-base');
  } else {
    // Hide and fade out the menu if it's currently visible
    overclockMenu.style.opacity = '0';
    
    // Wait for fade transition to complete (400ms) before actually removing from DOM
    setTimeout(() => {
      overclockMenu.style.display = 'none';
    }, 400);
    
    // Remove all difficulty color styling from the card
    puzzleCard.classList.remove('overclock-base', 'overclock-turbo', 'overclock-hyper', 'overclock-thermal');
    
    // Reset all overclock options when menu closes by deselecting buttons, unchecking checkboxes, and hiding start button
    resetOverclockOptions(puzzleId);
  }
}

// Select difficulty level and apply styling by updating card color and showing start button
function selectDifficulty(puzzleId, difficulty, basePoints) {
  // Get the puzzle card so we can apply the matching color scheme
  const puzzleCard = document.getElementById('puzzle-' + puzzleId);
  
  // Get all the difficulty buttons for this puzzle so we can deselect the old one
  const buttons = document.querySelectorAll(`#overclock-${puzzleId} .difficulty-btn`);
  
  // Loop through all buttons and remove the "active" class from all of them
  // This deselects any previously selected button
  buttons.forEach(btn => btn.classList.remove('active'));
  
  // Remove any old difficulty color from the card (to avoid multiple colors)
  puzzleCard.classList.remove('overclock-base', 'overclock-turbo', 'overclock-hyper', 'overclock-thermal');
  
  // Mark the clicked button as active (add "active" class so it looks pressed/highlighted)
  // event.target is the element the user just clicked
  const selectedBtn = event.target;
  selectedBtn.classList.add('active');
  
  // Apply the corresponding color class to the puzzle card
  // This makes the card's border and glow match your selected difficulty color
  puzzleCard.classList.add(`overclock-${difficulty}`);
  
  // Get the CSS variable color for the selected difficulty
  // CSS variables (custom properties) are defined in the stylesheet, prefixed with --
  // This isn't strictly necessary for functionality but could be useful for other purposes
  window.selectedColor = getComputedStyle(document.documentElement)
    .getPropertyValue('--theme-color');
  
  // Get the "Start OVER//CLOCK Mode" button for this puzzle
  const startBtn = document.getElementById('start-btn-' + puzzleId);
  
  // Make the button invisible first so we can fade it in
  startBtn.style.opacity = '0';
  startBtn.style.display = 'block';  // Make it visible in the DOM
  
  // Fade it in over 300ms by setting opacity to 1 after a tiny delay
  setTimeout(() => {
    startBtn.style.opacity = '1';
  }, 10);
  
  // Recalculate and display the total points based on selected difficulty + restrictions
  updatePoints(puzzleId, basePoints);
}

// ========== UPDATE POINTS DISPLAY ==========
// This calculates your total points based on base points + difficulty multiplier + restriction bonuses
// Runs whenever user selects a difficulty or checks/unchecks a restriction box
function updatePoints(puzzleId, basePoints) {
  // Start with no bonus points and 1x multiplier (no multiplier)
  let bonus = 0;
  let multiplier = 1;
  
  // Get the currently selected difficulty button (the one marked as "active")
  const activeBtn = document.querySelector(`#overclock-${puzzleId} .difficulty-btn.active`);
  if (activeBtn) {
    // Extract the difficulty name from button text and convert to lowercase
    // The button text is like "Base ⏱️ (720s)" so split by space and grab first word
    const difficulty = activeBtn.textContent.trim().split(' ')[0].toLowerCase();
    // Map each difficulty to its point multiplier
    // Higher difficulty = more points per correct answer
    const multipliers = {
      'base': 1.2,      // Base difficulty: 1.2x multiplier
      'turbo': 1.5,     // Turbo difficulty: 1.5x multiplier
      'hyper': 2.0,     // Hyper difficulty: 2.0x multiplier (double points!)
      'thermal': 2.5    // Thermal difficulty: 2.5x multiplier (most points)
    };
    // Get the multiplier for this difficulty, default to 1 if not found
    multiplier = multipliers[difficulty] || 1;
  }
  
  // ========== CHECK "NO TAB SWITCHING" RESTRICTION ==========
  // If user checks this box, they agree to not switch tabs (harder challenge)
  // Gets +25 bonus points as reward for increased difficulty
  if (document.getElementById('no-tabs-' + puzzleId).checked) {
    bonus += 25;
  }
  
  // ========== CHECK "NO HINTS" RESTRICTION ==========
  // If user checks this box, they'll solve without any hints available (harder)
  // Gets another +25 bonus points for not using hints
  if (document.getElementById('no-hints-' + puzzleId).checked) {
    bonus += 25;
  }
  
  // ========== CALCULATE TOTAL POINTS ==========
  // Formula: (base points * difficulty multiplier) + restriction bonuses
  // Math.round() rounds to nearest whole number
  const totalPoints = Math.round((basePoints * multiplier) + bonus);
  
  // Display the multiplier info (like "Multiplier: 1.5x (Turbo difficulty)")
  const displayMultiplier = multiplier > 1 ? ` (${multiplier}x difficulty)` : '';
  document.getElementById('multiplier-display-' + puzzleId).innerText = 
    'Multiplier: ' + multiplier + 'x' + displayMultiplier;
  
  // Display the bonus points part (empty if no bonuses, or " (+50)" if there are bonuses)
  document.getElementById('bonus-points-' + puzzleId).innerText = 
    bonus > 0 ? ' (+' + bonus + ')' : '';
  
  // Update the total points display on the page
  document.getElementById('total-points-' + puzzleId).innerText = totalPoints;
}

// ========== RESET OVERCLOCK OPTIONS ==========
// When user closes the overclock menu without selecting anything, this resets all options
// Unchecks all restriction boxes, deselects all difficulty buttons, and hides start button
function resetOverclockOptions(puzzleId) {
  // Get all the difficulty buttons for this puzzle
  const buttons = document.querySelectorAll(`#overclock-${puzzleId} .difficulty-btn`);
  
  // Loop through all buttons and remove the "active" class to deselect them
  buttons.forEach(btn => btn.classList.remove('active'));
  
  // Uncheck the "No Tab Switch" restriction checkbox
  document.getElementById('no-tabs-' + puzzleId).checked = false;
  // Uncheck the "No Hints" restriction checkbox
  document.getElementById('no-hints-' + puzzleId).checked = false;
  
  // Hide the "Start OVER//CLOCK Mode" button since no difficulty is selected anymore
  document.getElementById('start-btn-' + puzzleId).style.display = 'none';
  
  // Recalculate points back to base (no bonuses since restrictions are unchecked)
  // Get the base points value from the total-points display
  updatePoints(puzzleId, document.getElementById('total-points-' + puzzleId).innerText);
}

// ========== START OVERCLOCK MODE ==========
// This is the main function that launches an overclock challenge
// It takes your selected difficulty, restrictions, and other settings, then sends you to the puzzle solver
function startOverclock(puzzleId) {
  // Find which difficulty button is currently selected (the one with "active" class)
  const activeBtn = document.querySelector(`#overclock-${puzzleId} .difficulty-btn.active`);
  
  // Safety check: if no difficulty button is selected, don't proceed
  if (!activeBtn) return;
  
  // Extract the difficulty name from the button text
  // The button text looks like "Base ⏱️ (720s)" so split by space and take first word
  // Then convert to lowercase (base, turbo, hyper, or thermal)
  const difficulty = activeBtn.textContent.trim().split(' ')[0].toLowerCase();
  
  // Check if "No Tab Switch" restriction is enabled
  // If checked, the player agrees not to switch tabs during the puzzle
  const noTabs = document.getElementById('no-tabs-' + puzzleId).checked;
  
  // Check if "No Hints" restriction is enabled
  // If checked, the player won't have access to hints
  const noHints = document.getElementById('no-hints-' + puzzleId).checked;
  
  // Map each difficulty level to its theme color (for visual feedback)
  // These colors will be used by the puzzle solver page for styling (navbar glow, borders, etc)
  const colors = {
    'base': '#00ff66',      // Green color for base difficulty
    'turbo': '#00bfff',     // Cyan/blue color for turbo difficulty
    'hyper': '#ffc107',     // Gold/yellow color for hyper difficulty
    'thermal': '#dc3545'    // Red color for thermal difficulty
  };
  
  // ========== BUILD THE URL TO THE PUZZLE SOLVER ==========
  // Construct the full URL with all parameters the puzzle solver needs
  // encodeURIComponent() ensures the color hex code is properly formatted in the URL
  const url = `/cypherdeck-main1/pages/puzzles/solve_puzzle.php?id=${puzzleId}&mode=overclock` +
    `&difficulty=${difficulty}` +
    `&color=${encodeURIComponent(colors[difficulty])}` +
    `&restrictions=${noTabs ? 'notabs,' : ''}${noHints ? 'nohints' : ''}`;
  
  // ========== LAUNCH THE PUZZLE SOLVER PAGE ==========
  // Redirect the browser to the puzzle solver page with all your settings
  // The puzzle solver page will read these URL parameters and apply the settings
  window.location = url;
}
        