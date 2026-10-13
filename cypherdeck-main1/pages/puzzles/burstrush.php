<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get all burst rush puzzles grouped by category
$sql = "SELECT id, title, category, difficulty, is_burstrush FROM PuzzleStore WHERE is_burstrush = 1 ORDER BY category, difficulty";
$result = $conn->query($sql);
$burstPuzzles = [];
$categories = [];

while ($row = $result->fetch_assoc()) {
    $category = $row['category'];
    if (!isset($burstPuzzles[$category])) {
        $burstPuzzles[$category] = [];
        $categories[] = $category;
    }
    $burstPuzzles[$category][] = $row;
}

// Get user's solved puzzles for this session mode
$solvedSql = "SELECT puzzle_id FROM SolvedPuzzles WHERE user_id = ? AND mode = 'burstrush'";
$solvedStmt = $conn->prepare($solvedSql);
$solvedStmt->bind_param("i", $userId);
$solvedStmt->execute();
$solvedResult = $solvedStmt->get_result();
$solvedPuzzles = [];
while ($row = $solvedResult->fetch_assoc()) {
    $solvedPuzzles[$row['puzzle_id']] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burst Rush | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        :root {
            --primary-color: #00ff66;
            --primary-glow: rgba(0, 255, 102, 0.5);
        }

        :root.difficulty-turbo {
            --primary-color: #00bfff;
            --primary-glow: rgba(0, 191, 255, 0.5);
        }

        :root.difficulty-hyper {
            --primary-color: #ffc107;
            --primary-glow: rgba(255, 193, 7, 0.5);
        }

        :root.difficulty-thermal {
            --primary-color: #dc3545;
            --primary-glow: rgba(220, 53, 69, 0.5);
        }

        .burst-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 2rem 0;
        }

        .puzzle-card {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .puzzle-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 15px var(--primary-glow);
            transform: translateY(-2px);
        }

        .puzzle-card.selected {
            border-color: var(--primary-color);
            background: rgba(0, 0, 0, 0.7);
            box-shadow: inset 0 0 10px var(--primary-glow), 0 0 20px var(--primary-glow);
        }

        .puzzle-card.solved {
            opacity: 0.7;
            border-color: #28a745;
        }

        .puzzle-card.solved::after {
            content: "✓";
            position: absolute;
            top: 10px;
            right: 10px;
            color: #28a745;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .puzzle-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .puzzle-difficulty {
            font-size: 0.8rem;
            color: #999;
            margin-top: 0.5rem;
        }

        .difficulty-easy {
            color: #28a745;
        }

        .difficulty-medium {
            color: #ffc107;
        }

        .difficulty-hard {
            color: #dc3545;
        }

        .category-section {
            margin-bottom: 2rem;
        }

        .category-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            text-transform: uppercase;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            text-shadow: 0 0 10px var(--primary-glow);
        }

        .difficulty-selector {
            margin: 2rem 0;
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.6);
            border: 2px solid #444;
            border-radius: 8px;
            text-align: center;
        }

        .difficulty-selector h3 {
            color: var(--primary-color);
            text-shadow: 0 0 10px var(--primary-glow);
        }

        .difficulty-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .difficulty-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .difficulty-btn.base {
            border-color: #00ff66;
            color: #00ff66;
        }

        .difficulty-btn.base:hover,
        .difficulty-btn.base.selected {
            background: rgba(0, 255, 102, 0.2);
            box-shadow: 0 0 20px rgba(0, 255, 102, 0.6);
        }

        .difficulty-btn.turbo {
            border-color: #00bfff;
            color: #00bfff;
        }

        .difficulty-btn.turbo:hover,
        .difficulty-btn.turbo.selected {
            background: rgba(0, 191, 255, 0.2);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.6);
        }

        .difficulty-btn.hyper {
            border-color: #ffc107;
            color: #ffc107;
        }

        .difficulty-btn.hyper:hover,
        .difficulty-btn.hyper.selected {
            background: rgba(255, 193, 7, 0.2);
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.6);
        }

        .difficulty-btn.thermal {
            border-color: #dc3545;
            color: #dc3545;
        }

        .difficulty-btn.thermal:hover,
        .difficulty-btn.thermal.selected {
            background: rgba(220, 53, 69, 0.2);
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.6);
        }

        .go-button {
            width: 100%;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
            border: 2px solid var(--primary-color);
            background: rgba(0, 0, 0, 0.7);
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px var(--primary-glow);
        }

        .go-button:hover:not(:disabled) {
            background: var(--primary-glow);
            box-shadow: 0 0 30px var(--primary-glow);
            transform: scale(1.02);
        }

        .go-button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .selection-info {
            padding: 1rem;
            background: var(--primary-glow);
            border-left: 4px solid var(--primary-color);
            margin: 1rem 0;
            border-radius: 4px;
            display: none;
            color: var(--primary-color);
        }

        .selection-info.active {
            display: block;
        }

        .timer-display {
            position: fixed;
            top: 100px;
            right: 20px;
            font-size: 2rem;
            font-weight: bold;
            padding: 1rem 1.5rem;
            background: rgba(0, 0, 0, 0.9);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            color: var(--primary-color);
            text-shadow: 0 0 15px var(--primary-glow);
            z-index: 100;
            display: none;
        }

        .timer-display.active {
            display: block;
        }

        .timer-display.warning {
            border-color: #dc3545;
            color: #dc3545;
            text-shadow: 0 0 15px rgba(220, 53, 69, 0.8);
            animation: pulse 0.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        h1.burst-title {
            text-shadow: 0 0 20px var(--primary-glow), 0 0 40px var(--primary-glow);
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>

<body>

    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="timer-display" id="timerDisplay">
            <span id="timerMinutes">00</span>:<span id="timerSeconds">00</span>
        </div>
        <div class="main-box mx-auto" style="max-width: 1000px;">
            <h1 class="display-4 fw-bold text-center mb-2 burst-title">BURST RUSH</h1>
            <p class="lead text-center text-success mb-4">Quick-fire challenges. Select puzzles and difficulty, then test your skills!</p>

            <!-- Selection Info -->
            <div class="selection-info" id="selectionInfo">
                <strong>Selected:</strong> <span id="selectedCount">0</span> puzzle(s) | 
                <strong>Difficulty:</strong> <span id="difficultyDisplay">None</span>
            </div>

            <!-- Puzzle Selection -->
            <div id="puzzleSelection">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-section">
                            <div class="category-title"><?php echo htmlspecialchars($category); ?></div>
                            <div class="burst-container">
                                <?php foreach ($burstPuzzles[$category] as $puzzle): ?>
                                    <div class="puzzle-card <?php echo isset($solvedPuzzles[$puzzle['id']]) ? 'solved' : ''; ?>" 
                                         data-puzzle-id="<?php echo $puzzle['id']; ?>"
                                         data-puzzle-title="<?php echo htmlspecialchars($puzzle['title']); ?>"
                                         onclick="togglePuzzle(this)">
                                        <h5 class="puzzle-title"><?php echo htmlspecialchars($puzzle['title']); ?></h5>
                                        <div class="puzzle-difficulty difficulty-<?php echo strtolower($puzzle['difficulty']); ?>">
                                            <?php echo htmlspecialchars($puzzle['difficulty']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No Burst Rush puzzles available yet.</p>
                <?php endif; ?>
            </div>

            <!-- Difficulty Selector -->
            <div class="difficulty-selector">
                <h3 class="mb-3">Select Difficulty</h3>
                <div class="difficulty-buttons">
                    <button class="difficulty-btn base" onclick="selectDifficulty('base')">
                        BASE<br><small>Normal Speed</small>
                    </button>
                    <button class="difficulty-btn turbo" onclick="selectDifficulty('turbo')">
                        TURBO<br><small>2x Speed</small>
                    </button>
                    <button class="difficulty-btn hyper" onclick="selectDifficulty('hyper')">
                        HYPER<br><small>3x Speed</small>
                    </button>
                    <button class="difficulty-btn thermal" onclick="selectDifficulty('thermal')">
                        THERMAL<br><small>4x Speed</small>
                    </button>
                </div>
            </div>

            <!-- Go Button -->
            <button class="go-button" id="goButton" onclick="startBurstRush()" disabled>START BURST RUSH</button>

        </div>
    </main>

    <script>
        let selectedPuzzles = new Set();
        let selectedDifficulty = null;
        let burstRushTimer = null;
        let burstRushTimeRemaining = 0;

        function togglePuzzle(element) {
            const puzzleId = element.dataset.puzzleId;
            
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedPuzzles.delete(puzzleId);
            } else {
                element.classList.add('selected');
                selectedPuzzles.add(puzzleId);
            }
            
            updateSelectionDisplay();
        }

        function selectDifficulty(difficulty) {
            // Remove previous selection
            document.querySelectorAll('.difficulty-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Set new selection
            event.target.closest('.difficulty-btn').classList.add('selected');
            selectedDifficulty = difficulty;
            
            // Update theme colors for entire page including all glowing elements
            document.documentElement.classList.remove('difficulty-turbo', 'difficulty-hyper', 'difficulty-thermal');
            if (difficulty === 'turbo') {
                document.documentElement.classList.add('difficulty-turbo');
            } else if (difficulty === 'hyper') {
                document.documentElement.classList.add('difficulty-hyper');
            } else if (difficulty === 'thermal') {
                document.documentElement.classList.add('difficulty-thermal');
            }
            
            // Update all outline/glow elements dynamically
            updateColorTheme(difficulty);
            updateSelectionDisplay();
        }
        
        function updateColorTheme(difficulty) {
            const colors = {
                'base': '#00ff66',
                'turbo': '#00bfff',
                'hyper': '#ffc107',
                'thermal': '#dc3545'
            };
            const color = colors[difficulty] || colors['base'];
            
            // Update selection info box border color
            const infoBox = document.getElementById('selectionInfo');
            if (infoBox) {
                infoBox.style.borderLeftColor = color;
            }
            
            // Update puzzle card borders on hover
            document.querySelectorAll('.puzzle-card').forEach(card => {
                if (card.classList.contains('selected')) {
                    card.style.borderColor = color;
                }
            });
        }

        function updateSelectionDisplay() {
            const puzzleCount = selectedPuzzles.size;
            document.getElementById('selectedCount').textContent = puzzleCount;
            document.getElementById('difficultyDisplay').textContent = selectedDifficulty ? selectedDifficulty.toUpperCase() : 'None';
            
            const infoBox = document.getElementById('selectionInfo');
            if (puzzleCount > 0 && selectedDifficulty) {
                infoBox.classList.add('active');
                document.getElementById('goButton').disabled = false;
            } else {
                infoBox.classList.remove('active');
                document.getElementById('goButton').disabled = true;
            }
        }

        function startBurstRush() {
            if (selectedPuzzles.size === 0 || !selectedDifficulty) {
                alert('Please select at least one puzzle and a difficulty level.');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/cypherdeck-main1/pages/php/process_burstrush.php';

            // Add puzzle IDs
            selectedPuzzles.forEach(puzzleId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'puzzle_ids[]';
                input.value = puzzleId;
                form.appendChild(input);
            });

            // Add difficulty
            const diffInput = document.createElement('input');
            diffInput.type = 'hidden';
            diffInput.name = 'difficulty';
            diffInput.value = selectedDifficulty;
            form.appendChild(diffInput);

            // Add action
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'start_session';
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>
</html>
