<?php
header('Content-Type: text/css');
header('X-Content-Type-Options: nosniff');
?>

/* Puzzle list styles */
.puzzle-card {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(0, 255, 102, 0.2);
    margin-bottom: 1rem;
    overflow: hidden;
    background: rgba(33, 37, 41, 0.95);
}

.puzzle-card:hover:not(.overclock-base):not(.overclock-turbo):not(.overclock-hyper):not(.overclock-thermal) {
    box-shadow: 0 0 20px rgba(0, 255, 102, 0.3);
    transform: translateY(-2px);
}

/* OVER//CLOCK mode color themes */
.puzzle-card.overclock-base {
    border-color: #00ff66;
    box-shadow: 0 0 25px rgba(0, 255, 102, 0.5);
    transform: translateY(-2px);
}

.puzzle-card.overclock-turbo {
    border-color: #00bfff;
    box-shadow: 0 0 25px rgba(0, 191, 255, 0.5);
    transform: translateY(-2px);
}

.puzzle-card.overclock-hyper {
    border-color: #ffc107;
    box-shadow: 0 0 25px rgba(255, 193, 7, 0.5);
    transform: translateY(-2px);
}

.puzzle-card.overclock-thermal {
    border-color: #dc3545;
    box-shadow: 0 0 25px rgba(220, 53, 69, 0.5);
    transform: translateY(-2px);
}

/* Override accordion styles when in OVER//CLOCK mode */
.puzzle-card.overclock-base .accordion-button,
.puzzle-card.overclock-turbo .accordion-button,
.puzzle-card.overclock-hyper .accordion-button,
.puzzle-card.overclock-thermal .accordion-button {
    border: none;
    background: transparent;
}

/* Remove default green glow from accordion elements */
.accordion-button:not(.collapsed) {
    background-color: rgba(33, 37, 41, 0.95);
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: inherit;
}

/* Smooth reveal animation for OVER//CLOCK options */
[id^="overclock-"] {
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

[id^="overclock-"]:not([style*="display: none"]) {
    opacity: 1;
    transform: translateY(0);
}

/* Difficulty button styles */
.difficulty-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.difficulty-btn:hover {
    transform: scale(1.05);
}

.difficulty-btn::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: currentColor;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.difficulty-btn:hover::after {
    opacity: 0.1;
}

.difficulty-btn.active {
    transform: scale(1.05);
    box-shadow: 0 0 15px currentColor;
}

/* Rating stars */
.rating-stars {
    color: #ffd700;
    text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
}

/* Tag badges */
.tag-badge {
    background: rgba(0, 255, 102, 0.1);
    color: #00ff66;
    border: 1px solid #00ff66;
    transition: all 0.3s ease;
}

.tag-badge:hover {
    background: rgba(0, 255, 102, 0.2);
    box-shadow: 0 0 10px rgba(0, 255, 102, 0.3);
}