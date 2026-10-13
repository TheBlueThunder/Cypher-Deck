<?php
// Fetch and filter puzzles with search/category/difficulty using dynamic SQL queries and prepared statements to prevent injection
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$puzzle_type = isset($_GET['type']) ? $_GET['type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build dynamic SQL query conditionally using WHERE 1=1 to safely add AND clauses
$sql = "SELECT p.*, u.username as creator_name,
          (SELECT COUNT(*) FROM PuzzleQuestions WHERE puzzleid = p.id) as question_count
          FROM PuzzleStore p 
          LEFT JOIN Users u ON p.created_by = u.id
          WHERE 1=1";

if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
    $searchParam = "%$search%";
}
if ($category) {
    $sql .= " AND p.category = ?";
}
if ($difficulty) {
    $sql .= " AND p.difficulty = ?";
}
if ($puzzle_type) {
    $sql .= " AND p.puzzle_type = ?";
}

// Add sorting
switch ($sort) {
    case 'highest_rated':
        $sql .= " ORDER BY p.rating DESC";
        break;
    case 'most_points':
        $sql .= " ORDER BY p.base_points DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

// Bind parameters dynamically
$stmt = $conn->prepare($sql);
$params = array();
$types = "";

if ($search) { 
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}
if ($category) {
    $params[] = $category;
    $types .= "s";
}
if ($difficulty) {
    $params[] = $difficulty;
    $types .= "s";
}
if ($puzzle_type) {
    $params[] = $puzzle_type;
    $types .= "s";
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$puzzles = $result->fetch_all(MYSQLI_ASSOC);

// Get unique categories for filter dropdown
$categoryQuery = "SELECT DISTINCT category FROM PuzzleStore ORDER BY category";
$categories = $conn->query($categoryQuery)->fetch_all(MYSQLI_ASSOC);
?>
