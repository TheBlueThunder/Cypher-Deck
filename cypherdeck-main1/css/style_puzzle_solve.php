<?php
header('Content-Type: text/css');
header('X-Content-Type-Options: nosniff');
?>

/* General styles */
body {
    background-color: #000000;
    color: var(--theme-color);
}

.container {
    max-width: 1200px;
    margin-top: 120px;
}

/* Puzzle card styles */
.puzzle-header {
    background: rgba(255, 255, 255, 0.1);
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    border: 2px solid rgba(0, 255, 102, 0.3);
    box-shadow: 0 0 20px rgba(0, 255, 102, 0.2);
}

.question-card {
    background: rgba(255, 255, 255, 0.05);
    padding: 2rem;
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.question-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

/* Form styles */
.input-group {
    margin-top: 1rem;
}

.form-control {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    padding: 0.75rem;
}

.form-control:focus {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(0, 255, 102, 0.5);
    color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0, 255, 102, 0.25);
}

/* Button styles */
.btn-success {
    background-color: rgba(0, 255, 102, 0.2);
    border-color: rgba(0, 255, 102, 0.5);
    color: #00ff66;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: rgba(0, 255, 102, 0.3);
    border-color: rgba(0, 255, 102, 0.7);
    transform: translateY(-1px);
    box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
}

/* Hint system styles */
.hint-button {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.hint-button:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(0, 255, 102, 0.5);
}

.hint-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hint-text {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 5px;
    border-left: 3px solid rgba(0, 255, 102, 0.5);
}

/* Alert styles */
.alert {
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: none;
}

.alert-success {
    background: rgba(25, 135, 84, 0.2);
    border: 1px solid rgba(25, 135, 84, 0.5);
    color: #198754;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.2);
    border: 1px solid rgba(220, 53, 69, 0.5);
    color: #dc3545;
}

/* Timer and mode styles */
.timer-container {
    position: fixed;
    top: 80px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    padding: 15px 25px;
    border-radius: 10px;
    color: white;
    font-size: 24px;
    z-index: 999;
    transition: all 0.3s ease;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.timer-warning {
    animation: pulse 1s infinite;
    background: rgba(220, 53, 69, 0.9);
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.5);
}

.mode-badge {
    position: fixed;
    top: 80px;
    left: 20px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: bold;
    z-index: 999;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.mode-standard {
    background: rgba(25, 135, 84, 0.9);
    color: white;
}

.mode-overclock {
    background: rgba(220, 53, 69, 0.9);
    color: white;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Difficulty badges */
.difficulty {
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
}

.difficulty.easy {
    background: rgba(25, 135, 84, 0.2);
    color: #198754;
}

.difficulty.medium {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.difficulty.hard {
    background: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}