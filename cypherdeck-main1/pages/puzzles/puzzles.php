<?php
session_start();
require_once("../php/process_db.php");
require_once("../php/session_security.php");
require_once("../php/process_puzzlelist.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puzzles | Cypher Deck</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/style.php">
    <link rel="stylesheet" href="../../css/style_puzzles.php">

</head>

<body>
    <!-- Navbar -->
    <?php require_once("../../pages/templates/navbar.php"); ?>

    <!-- Main content -->
    <main class="container mt-5">
        <h1 class="display-3 fw-bold text-center">Puzzles</h1>
        <p class="lead text-center mb-5">Challenge yourself with our cybersecurity puzzles!</p>

        <!-- Filters, grabs the difficulty and other atrributes from the db, and using bootstrap will only select those to show in the "accordion list" -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search puzzles..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="difficulty" class="form-select">
                            <option value="">All Difficulties</option>
                            <option value="Easy" <?php echo $difficulty === 'Easy' ? 'selected' : ''; ?>>Easy</option>
                            <option value="Medium" <?php echo $difficulty === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="Hard" <?php echo $difficulty === 'Hard' ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="mini" <?php echo $puzzle_type === 'mini' ? 'selected' : ''; ?>>Mini</option>
                            <option value="full" <?php echo $puzzle_type === 'full' ? 'selected' : ''; ?>>Full</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="highest_rated" <?php echo $sort === 'highest_rated' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="most_points" <?php echo $sort === 'most_points' ? 'selected' : ''; ?>>Most Points</option>
                        </select>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
                                onmouseout="this.style.boxShadow='none'">Apply Filters</button>
                        <a href="/cypherdeck-main1/pages/puzzles/puzzles.php" class="btn ms-2" style="border: 2px solid #666; color: #aaa; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                           onmouseover="this.style.boxShadow='0 0 20px rgba(100,100,100,0.6)'" 
                           onmouseout="this.style.boxShadow='none'">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Puzzle list, The massive drop down menu list -->
        <div class="accordion" id="puzzleAccordion">
            <?php if (count($puzzles) > 0): ?>
                <?php foreach ($puzzles as $index => $puzzle): ?>
                    <div class="accordion-item puzzle-card mb-3" id="puzzle-<?php echo $puzzle['id']; ?>">
                        <!-- Collapsed header -->
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $index; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <?php 
                                        $icon = match($puzzle['category']) {
                                            'Cryptography' => '🔐',
                                            'Web Hacking' => '🌐',
                                            'Steganography' => '🖼️',
                                            default => '🎯'
                                        };
                                        ?>
                                        <span class="fs-5"><?php echo $icon . ' ' . htmlspecialchars($puzzle['title']); ?></span>
                                        
                                        <!-- Rating stars -->
                                        <span class="ms-2 rating-stars">
                                            <?php 
                                            $rating = floatval($puzzle['rating']); // Convert to float
                                            $fullStars = floor($rating);
                                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                            
                                            // Full stars
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '★';
                                            }
                                            
                                            // Half star
                                            if ($hasHalfStar) {
                                                echo '½';
                                            }
                                            
                                            // Empty stars
                                            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                            for ($i = 0; $i < $emptyStars; $i++) {
                                                echo '☆';
                                            }
                                            
                                            // Show rating count if available
                                            if ($puzzle['rating_count'] > 0) {
                                                echo " ({$puzzle['rating']} / {$puzzle['rating_count']} ratings)";
                                            } else {
                                                echo " (No ratings yet)";
                                            }
                                            ?>
                                    </span>
                                    </div>
                                    <div>
                                        <!-- Tags, This will take the tags in the db, make an array out of it, and then each individual one has its own box --> 
                                        <?php if ($puzzle['tags']): ?>
                                            <?php foreach (explode(',', $puzzle['tags']) as $tag): ?>
                                                <span class="badge tag-badge"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <!-- Difficulty badge -->
                                        <span class="badge ms-2 <?php 
                                            echo match($puzzle['difficulty']) {
                                                'Easy' => 'bg-success',
                                                'Medium' => 'bg-warning',
                                                'Hard' => 'bg-danger'
                                            };
                                        ?>">
                                            <?php echo $puzzle['difficulty']; ?>
                                        </span>
                                        
                                        <!-- Puzzle type badge -->
                                        <span class="badge bg-info ms-2">
                                            <?php echo strtoupper($puzzle['puzzle_type']); ?>
                                        </span>
                                        
                                        <!-- Points -->
                                        <span class="badge bg-secondary ms-2">
                                            💎 <?php echo $puzzle['base_points']; ?>pts
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        
                        <!-- Expanded content -->
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" 
                             data-bs-parent="#puzzleAccordion">
                            <div class="accordion-body">
                                <p class="mb-2">Created by: @<?php echo htmlspecialchars($puzzle['creator_name']); ?></p>
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($puzzle['description'])); ?></p> <!-- nl2br adds a break everytime theres a new string, neat-->
                                
                                <!-- Mode selection, TODO, need to add a power up sound effect anytime its pressed, the pitch chnages on the difficulty-->
                                <div class="row">
                                    <div class="col-12">
                                        <button class="btn me-2" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
                                                onmouseout="this.style.boxShadow='none'"
                                                onclick="window.location='solve_puzzle.php?id=<?php echo $puzzle['id']; ?>&mode=standard'">
                                            Standard Mode
                                        </button>
                                        <button class="btn" style="border: 2px solid #ffc107; color: #ffc107; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                onmouseover="this.style.boxShadow='0 0 20px rgba(255,193,7,0.6)'" 
                                                onmouseout="this.style.boxShadow='none'"
                                                onclick="toggleOverclock(<?php echo $puzzle['id']; ?>)">
                                            OVER//CLOCK ▼
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- OVER//CLOCK options (hidden by default) -->
                                <div id="overclock-<?php echo $puzzle['id']; ?>" class="mt-3" style="display: none;">
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <button class="btn difficulty-btn me-2" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                    onmouseover="this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" 
                                                    onmouseout="this.style.boxShadow='none'"
                                                    onclick="selectDifficulty(<?php echo $puzzle['id']; ?>, 'base', <?php echo $puzzle['base_points']; ?>)">
                                                Base ⏱️ (<?php echo $puzzle['time_limit_base']; ?>s)
                                            </button>
                                            <button class="btn difficulty-btn me-2" style="border: 2px solid #00bfff; color: #00bfff; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                    onmouseover="this.style.boxShadow='0 0 20px rgba(0,191,255,0.6)'" 
                                                    onmouseout="this.style.boxShadow='none'"
                                                    onclick="selectDifficulty(<?php echo $puzzle['id']; ?>, 'turbo', <?php echo $puzzle['base_points']; ?>)">
                                                Turbo ⚡ (<?php echo $puzzle['time_limit_turbo']; ?>s)
                                            </button>
                                            <button class="btn difficulty-btn me-2" style="border: 2px solid #ffc107; color: #ffc107; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                    onmouseover="this.style.boxShadow='0 0 20px rgba(255,193,7,0.6)'" 
                                                    onmouseout="this.style.boxShadow='none'"
                                                    onclick="selectDifficulty(<?php echo $puzzle['id']; ?>, 'hyper', <?php echo $puzzle['base_points']; ?>)">
                                                Hyper 🔥 (<?php echo $puzzle['time_limit_hyper']; ?>s)
                                            </button>
                                            <button class="btn difficulty-btn" style="border: 2px solid #dc3545; color: #dc3545; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                    onmouseover="this.style.boxShadow='0 0 20px rgba(220,53,69,0.6)'" 
                                                    onmouseout="this.style.boxShadow='none'"
                                                    onclick="selectDifficulty(<?php echo $puzzle['id']; ?>, 'thermal', <?php echo $puzzle['base_points']; ?>)">
                                                Thermal ❄️ (<?php echo $puzzle['time_limit_thermal']; ?>s)
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Restrictions -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="no-tabs-<?php echo $puzzle['id']; ?>"
                                                       onchange="updatePoints(<?php echo $puzzle['id']; ?>, <?php echo $puzzle['base_points']; ?>)">
                                                <label class="form-check-label">
                                                    No Tab Switch (+25 points)
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="no-hints-<?php echo $puzzle['id']; ?>"
                                                       onchange="updatePoints(<?php echo $puzzle['id']; ?>, <?php echo $puzzle['base_points']; ?>)">
                                                <label class="form-check-label">
                                                    No Hints (+25 points)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Points calculation -->
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <p class="mb-0">
                                                Base Points: <?php echo $puzzle['base_points']; ?> 
                                                <span id="bonus-points-<?php echo $puzzle['id']; ?>" class="text-success"></span>
                                                <br>
                                                <small id="multiplier-display-<?php echo $puzzle['id']; ?>" style="color: #999;">Multiplier: 1x</small>
                                                = <span id="total-points-<?php echo $puzzle['id']; ?>" style="font-weight: bold; color: #ffc107;"><?php echo $puzzle['base_points']; ?></span> 💎
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Start button -->
                                    <div class="row">
                                        <div class="col-12">
                                            <button class="btn" id="start-btn-<?php echo $puzzle['id']; ?>" style="display: none; border: 2px solid #ffc107; color: #ffc107; background: rgba(0,0,0,0.7); transition: all 0.3s ease;" 
                                                    onmouseover="this.style.boxShadow='0 0 20px rgba(255,193,7,0.6)'" 
                                                    onmouseout="this.style.boxShadow='none'"
                                                    onclick="startOverclock(<?php echo $puzzle['id']; ?>)">
                                                Start OVER//CLOCK Mode
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No puzzles found.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script type="text/javascript" src="../../pages/puzzles/puzzlemenu.js"></script>
</body>
</html>
