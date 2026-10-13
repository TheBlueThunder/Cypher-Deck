<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Only admins can create puzzles, so check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: /cypherdeck-main1/');
    exit();
}

require_once("../php/process_db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Puzzle | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <base href="/cypherdeck-main1/">
</head>
<body>

    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="main-box mx-auto" style="max-width: 800px;">
            <h1 class="display-4 fw-bold text-center mb-4">Create New Puzzle</h1>
            
            <form action="../pages/php/process_create_puzzle.php" method="POST" id="createPuzzleForm" class="needs-validation" novalidate>
                
                <!-- Puzzle Type Selector -->
                <div class="mb-4">
                    <h3 class="h5 mb-3">Puzzle Type</h3>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="puzzle_type" id="standardType" value="standard" checked>
                        <label class="btn" for="standardType" style="border: 2px solid #00ff66; color: #00ff66; background: rgba(0,0,0,0.7); cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(0,255,102,0.15)'; this.style.boxShadow='0 0 20px rgba(0,255,102,0.6)'" onmouseout="this.style.background='rgba(0,0,0,0.7)'; this.style.boxShadow='none'">Standard Puzzle</label>
                        
                        <input type="radio" class="btn-check" name="puzzle_type" id="burstRushType" value="burstrush">
                        <label class="btn" for="burstRushType" style="border: 2px solid #ffc107; color: #ffc107; background: rgba(0,0,0,0.7); cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,193,7,0.15)'; this.style.boxShadow='0 0 20px rgba(255,193,7,0.6)'" onmouseout="this.style.background='rgba(0,0,0,0.7)'; this.style.boxShadow='none'">Burst Rush Puzzle</label>
                    </div>
                </div>

                <!-- Puzzle Title -->
                <div class="mb-3">
                    <label for="title" class="form-label">Puzzle Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                    <div class="invalid-feedback">Please enter a puzzle title.</div>
                </div>

                <!-- Puzzle Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    <div class="invalid-feedback">Please enter a description.</div>
                </div>

                <!-- Category -->
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">Choose...</option>
                        <option value="Cryptography">Cryptography</option>
                        <option value="Steganography">Steganography</option>
                        <option value="Web Hacking">Web Hacking</option>
                        <option value="Forensics">Forensics</option>
                        <option value="Reverse Engineering">Reverse Engineering</option>
                        <option value="OSINT">OSINT</option>
                        <option value="General">General</option>
                    </select>
                    <div class="invalid-feedback">Please select a category.</div>
                </div>

                <!-- Difficulty -->
                <div class="mb-3">
                    <label for="difficulty" class="form-label">Difficulty</label>
                    <select class="form-select" id="difficulty" name="difficulty" required>
                        <option value="">Choose...</option>
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                    <div class="invalid-feedback">Please select a difficulty.</div>
                </div>

                <!-- Standard Puzzle Fields (Base Points) -->
                <div id="standardFields" class="mb-4">
                    <div class="mb-3">
                        <label for="basePoints" class="form-label">Base Points</label>
                        <input type="number" class="form-control" id="basePoints" name="base_points" min="10" max="500" value="100">
                        <small class="text-muted">Points awarded for solving this puzzle in Standard mode</small>
                    </div>
                </div>

                <!-- Burst Rush Puzzle Fields (hidden by default) -->
                <div id="burstRushFields" style="display:none;" class="mb-4">
                    <div class="mb-3">
                        <label for="pointsPerQuestion" class="form-label">Points Per Question</label>
                        <input type="number" class="form-control" id="pointsPerQuestion" name="points_per_question" min="5" max="50" value="10">
                        <small class="text-muted">Points awarded for each correct answer in Burst Rush mode</small>
                    </div>
                    <div class="mb-3">
                        <label for="maxQuestions" class="form-label">Max Questions Per Session</label>
                        <input type="number" class="form-control" id="maxQuestions" name="max_questions_per_session" min="5" max="50" value="10">
                        <small class="text-muted">Number of questions in a single Burst Rush session</small>
                    </div>
                    <div class="mb-3">
                        <label for="timePerQuestion" class="form-label">Seconds Per Question</label>
                        <input type="number" class="form-control" id="timePerQuestion" name="time_limit_per_question" min="5" max="60" value="15">
                        <small class="text-muted">Time limit for answering each question (in seconds)</small>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="mb-4">
                    <h3 class="h5 mb-3">Puzzle Questions</h3>
                    <div id="questionsContainer">
                        <!-- First question (always present) -->
                        <div class="question-group mb-4 p-3" style="background: #1a1a1a; border-radius: 8px;" data-question-num="1">
                            <h4 class="h6 mb-3">Question 1</h4>
                            
                            <div class="mb-2">
                                <label class="form-label">Question Text</label>
                                <textarea class="form-control" name="questions[0][text]" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Answer</label>
                                <input type="text" class="form-control" name="questions[0][answer]" required>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label">Hints (optional)</label>
                                <textarea class="form-control" name="questions[0][hints]" rows="2" placeholder="Provide hints to help solvers..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-success" id="addQuestionBtn">
                        + Add Another Question
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-lg">Create Puzzle</button>
                    <a href="puzzles.php" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center mt-5 mb-3 text-secondary">
        <small>&copy; <?php echo date('Y'); ?> Cypher Deck</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== FORM VALIDATION ==========
        // Bootstrap form validation to show error messages if fields are empty
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    // Check if form is valid before submitting
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // ========== PUZZLE TYPE TOGGLE ==========
        // Get the radio button elements for puzzle type selection
        const standardRadio = document.getElementById('standardType');
        const burstRushRadio = document.getElementById('burstRushType');
        
        // Get container divs for Standard and Burst Rush specific fields
        const standardFields = document.getElementById('standardFields');
        const burstRushFields = document.getElementById('burstRushFields');
        
        // Listen for changes when Standard Puzzle radio is selected
        standardRadio.addEventListener('change', function() {
            if (this.checked) {
                // Show standard puzzle fields, hide burst rush fields
                standardFields.style.display = 'block';
                burstRushFields.style.display = 'none';
            }
        });
        
        // Listen for changes when Burst Rush Puzzle radio is selected
        burstRushRadio.addEventListener('change', function() {
            if (this.checked) {
                // Hide standard puzzle fields, show burst rush fields
                standardFields.style.display = 'none';
                burstRushFields.style.display = 'block';
            }
        });

        // ========== ADD QUESTION FUNCTIONALITY ==========
        // Initialize question counter starting at 1
        let questionCount = 1;
        
        // Listen for clicks on "Add Another Question" button
        document.getElementById('addQuestionBtn').addEventListener('click', function() {
            const container = document.getElementById('questionsContainer');
            const newQuestion = document.createElement('div');
            newQuestion.className = 'question-group mb-4 p-3';
            newQuestion.style.cssText = 'background: #1a1a1a; border-radius: 8px;';
            newQuestion.setAttribute('data-question-num', questionCount + 1);
            
            // Create HTML for new question with remove button
            newQuestion.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="h6 mb-0">Question ${questionCount + 1}</h4>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remove</button>
                </div>
                
                <div class="mb-2">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="questions[${questionCount}][text]" rows="3" required></textarea>
                </div>
                
                <div class="mb-2">
                    <label class="form-label">Answer</label>
                    <input type="text" class="form-control" name="questions[${questionCount}][answer]" required>
                </div>
                
                <div class="mb-2">
                    <label class="form-label">Hints (optional)</label>
                    <textarea class="form-control" name="questions[${questionCount}][hints]" rows="2" placeholder="Provide hints to help solvers..."></textarea>
                </div>
            `;
            
            // Add new question to container
            container.appendChild(newQuestion);
            questionCount++;
            
            // Add remove button event listener to the new question
            newQuestion.querySelector('.remove-question').addEventListener('click', function() {
                newQuestion.remove();
                updateQuestionNumbers();
            });
        });

        // ========== UPDATE QUESTION NUMBERS ==========
        // Update question display numbers after a question is removed
        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-group');
            questions.forEach((q, index) => {
                // Update the display number in the h4 header
                q.querySelector('h4').textContent = `Question ${index + 1}`;
                q.setAttribute('data-question-num', index + 1);
            });
            // Update the counter to match number of questions
            questionCount = questions.length;
        }
    </script>
</body>
</html>
