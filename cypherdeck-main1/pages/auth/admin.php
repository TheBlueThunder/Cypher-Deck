<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: /cypherdeck-main1/');
    exit();
}

$userId = $_SESSION['user_id'];
$currentTab = $_GET['tab'] ?? 'overview';

// Get admin statistics for overview
$overviewData = [];

// Total users
$sql = "SELECT COUNT(*) as count FROM Users";
$result = $conn->query($sql);
$overviewData['total_users'] = $result->fetch_assoc()['count'];

// Total puzzles
$sql = "SELECT COUNT(*) as count FROM PuzzleStore";
$result = $conn->query($sql);
$overviewData['total_puzzles'] = $result->fetch_assoc()['count'];

// Burst Rush puzzles
$sql = "SELECT COUNT(*) as count FROM PuzzleStore WHERE is_burstrush = 1";
$result = $conn->query($sql);
$overviewData['burstrush_puzzles'] = $result->fetch_assoc()['count'];

// Total questions
$sql = "SELECT COUNT(*) as count FROM PuzzleQuestions";
$result = $conn->query($sql);
$overviewData['total_questions'] = $result->fetch_assoc()['count'];

// Total puzzles solved
$sql = "SELECT COUNT(*) as count FROM SolvedPuzzles";
$result = $conn->query($sql);
$overviewData['total_solved'] = $result->fetch_assoc()['count'];

// Get recent puzzle activity
$recentActivity = [];
$sql = "SELECT u.username, p.title, sp.solved_at, sp.mode, sp.points_earned
        FROM SolvedPuzzles sp
        JOIN Users u ON sp.user_id = u.id
        JOIN PuzzleStore p ON sp.puzzle_id = p.id
        ORDER BY sp.solved_at DESC LIMIT 10";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recentActivity[] = $row;
}

// Get featured puzzles if tab is featured
$featuredPuzzles = [];
if ($currentTab === 'featured') {
    $sql = "SELECT id, title, is_featured, category, difficulty FROM PuzzleStore ORDER BY title";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $featuredPuzzles[] = $row;
    }
}

// Handle featured puzzle toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentTab === 'featured') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_featured') {
        $puzzleId = (int)$_POST['puzzle_id'];
        $isFeatured = (int)$_POST['is_featured'];
        
        $sql = "UPDATE PuzzleStore SET is_featured = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $isFeatured, $puzzleId);
        $stmt->execute();
        
        header('Location: /cypherdeck-main1/pages/auth/admin.php?tab=featured');
        exit();
    }
}

// Get all users for user management
$allUsers = [];
if ($currentTab === 'users') {
    $sql = "SELECT id, username, email, role, created_at FROM Users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $allUsers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Cypher Deck</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.php" rel="stylesheet" type="text/css">
    <style>
        .admin-header {
            border-bottom: 3px solid #00ff66;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #00ff66;
            text-shadow: 0 0 20px rgba(0, 255, 102, 0.5);
            font-weight: bold;
        }

        .nav-tabs .nav-link {
            color: #999;
            border: none;
            border-bottom: 3px solid transparent;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #00ff66;
            border-bottom-color: #00ff66;
        }

        .nav-tabs .nav-link.active {
            color: #00ff66;
            border-bottom-color: #00ff66;
            background: none;
        }

        .stat-box {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            border-color: #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #00ff66;
            text-shadow: 0 0 10px rgba(0, 255, 102, 0.5);
        }

        .stat-label {
            color: #999;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .activity-table {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 8px;
            overflow: hidden;
        }

        .activity-table table {
            margin: 0;
        }

        .activity-table td {
            color: #ddd;
            border-color: #444;
            padding: 1rem;
        }

        .activity-table thead th {
            background: rgba(0, 255, 102, 0.1);
            color: #00ff66;
            border-color: #00ff66;
            font-weight: bold;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .puzzle-card-admin {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            padding: 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .puzzle-card-admin:hover {
            border-color: #00ff66;
            box-shadow: 0 0 15px rgba(0, 255, 102, 0.3);
        }

        .puzzle-card-admin h5 {
            color: #00ff66;
            margin-bottom: 0.5rem;
        }

        .featured-toggle {
            margin-top: 1rem;
        }

        .featured-badge {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .users-table {
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #444;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 1.5rem;
        }

        .users-table table {
            margin: 0;
        }

        .users-table td, .users-table th {
            border-color: #444;
            padding: 1rem;
        }

        .users-table thead th {
            background: rgba(0, 255, 102, 0.1);
            color: #00ff66;
            border-color: #00ff66;
            font-weight: bold;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .role-badge.admin {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .role-badge.user {
            background: rgba(0, 255, 102, 0.2);
            color: #00ff66;
            border: 1px solid #00ff66;
        }
    </style>
    <base href="/cypherdeck-main1/">
</head>
<body>
    <?php require_once("../templates/navbar.php"); ?>

    <main class="container mt-5">
        <div class="main-box mx-auto" style="max-width: 1200px;">
            <!-- Admin header -->
            <div class="admin-header">
                <h1>⚙️ ADMIN DASHBOARD</h1>
                <small class="text-muted">System administration and analytics</small>
            </div>

            <!-- Navigation tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentTab === 'overview' ? 'active' : ''; ?>" href="?tab=overview">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentTab === 'featured' ? 'active' : ''; ?>" href="?tab=featured">Featured Puzzles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentTab === 'users' ? 'active' : ''; ?>" href="?tab=users">Users</a>
                </li>
            </ul>

            <!-- TAB: Overview -->
            <?php if ($currentTab === 'overview'): ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $overviewData['total_users']; ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $overviewData['total_puzzles']; ?></div>
                            <div class="stat-label">Total Puzzles</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $overviewData['burstrush_puzzles']; ?></div>
                            <div class="stat-label">Burst Rush Puzzles</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $overviewData['total_questions']; ?></div>
                            <div class="stat-label">Total Questions</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $overviewData['total_solved']; ?></div>
                            <div class="stat-label">Puzzles Solved</div>
                        </div>
                    </div>
                </div>

                <!-- Recent activity -->
                <div class="activity-table mt-4">
                    <h4 class="p-3 m-0" style="border-bottom: 2px solid #00ff66; color: #00ff66;">Recent Activity</h4>
                    <table class="table table-dark m-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Puzzle</th>
                                <th>Mode</th>
                                <th>Points</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td><?php echo htmlspecialchars(strtoupper($activity['mode'])); ?></td>
                                    <td><?php echo number_format($activity['points_earned']); ?> pts</td>
                                    <td><?php echo date('M d, Y H:i', strtotime($activity['solved_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- TAB: Featured Puzzles -->
            <?php if ($currentTab === 'featured'): ?>
                <h3 class="mb-4">Manage Featured Puzzles</h3>
                <p class="text-muted">Select which puzzles appear on the featured page. Featured puzzles get more visibility.</p>

                <div class="featured-grid">
                    <?php foreach ($featuredPuzzles as $puzzle): ?>
                        <div class="puzzle-card-admin">
                            <h5><?php echo htmlspecialchars($puzzle['title']); ?></h5>
                            <div>
                                <small class="text-muted">
                                    Category: <?php echo htmlspecialchars($puzzle['category']); ?> | 
                                    Difficulty: <?php echo htmlspecialchars($puzzle['difficulty']); ?>
                                </small>
                            </div>

                            <?php if ($puzzle['is_featured']): ?>
                                <div class="featured-badge">★ FEATURED</div>
                            <?php endif; ?>

                            <form method="POST" class="featured-toggle">
                                <input type="hidden" name="action" value="toggle_featured">
                                <input type="hidden" name="puzzle_id" value="<?php echo $puzzle['id']; ?>">
                                <input type="hidden" name="is_featured" value="<?php echo $puzzle['is_featured'] ? '0' : '1'; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $puzzle['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?> w-100">
                                    <?php echo $puzzle['is_featured'] ? 'Unfeature' : 'Feature This'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- TAB: Users -->
            <?php if ($currentTab === 'users'): ?>
                <h3 class="mb-4">User Management</h3>

                <div class="users-table">
                    <table class="table table-dark m-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                            <?php echo htmlspecialchars(strtoupper($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
