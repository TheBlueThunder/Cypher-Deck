<?php
session_start();
require_once("../php/process_db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// Fetch all available rooms
$sql = "SELECT id, name, description, category, icon_emoji, icon_color, 
               (SELECT COUNT(*) FROM ChatMessages WHERE room_id = Rooms.id) as message_count
        FROM Rooms 
        ORDER BY created_at ASC";
$result = $conn->query($sql);
$rooms = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Social Rooms | Cypher Deck</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../css/style.php" rel="stylesheet" type="text/css">
  <style>
    /* Wii-Style Channel Menu Theme */
    body {
      background: linear-gradient(135deg, #000000 0%, #0a0a0a 100%);
      background-attachment: fixed;
      font-family: 'Courier New', monospace;
      overflow-x: hidden;
    }

    .rooms-header {
      text-align: center;
      padding: 4rem 2rem 2rem;
      background: linear-gradient(180deg, rgba(0,255,102,0.1) 0%, rgba(0,0,0,0) 100%);
      border-bottom: 2px solid #00ff66;
      margin-bottom: 3rem;
    }

    .rooms-header h1 {
      font-size: 3.5rem;
      font-weight: bold;
      color: #00ff66;
      text-shadow: 0 0 20px rgba(0,255,102,0.6), 0 0 40px rgba(0,255,102,0.3);
      letter-spacing: 3px;
      margin-bottom: 0.5rem;
    }

    .rooms-header p {
      font-size: 1.1rem;
      color: #aaa;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Channel Grid Container */
    .channels-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      padding: 0 2rem 4rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* Channel Card - Wii Menu Style */
    .channel-card {
      position: relative;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
      transform-style: preserve-3d;
      perspective: 1000px;
    }

    .channel-card:hover {
      transform: translateY(-15px) scale(1.05);
    }

    .channel-card-inner {
      background: linear-gradient(135deg, rgba(0,255,102,0.15) 0%, rgba(0,255,102,0.05) 100%);
      border: 2px solid #00ff66;
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 0 20px rgba(0,255,102,0.3), inset 0 0 20px rgba(0,255,102,0.05);
      transition: all 0.3s ease;
      min-height: 320px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .channel-card:hover .channel-card-inner {
      box-shadow: 0 0 40px rgba(0,255,102,0.6), inset 0 0 30px rgba(0,255,102,0.15);
      border-color: #00ff99;
      background: linear-gradient(135deg, rgba(0,255,102,0.25) 0%, rgba(0,255,102,0.1) 100%);
    }

    /* Channel Icon */
    .channel-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      animation: float 3s ease-in-out infinite;
    }

    .channel-card:hover .channel-icon {
      animation: pulse 0.6s ease-in-out;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.2); }
    }

    /* Channel Name */
    .channel-name {
      font-size: 1.6rem;
      font-weight: bold;
      color: #00ff66;
      text-shadow: 0 0 10px rgba(0,255,102,0.6);
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Channel Category */
    .channel-category {
      font-size: 0.85rem;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }

    /* Channel Description */
    .channel-description {
      font-size: 0.95rem;
      color: #bbb;
      margin-bottom: 1rem;
      min-height: 60px;
      line-height: 1.4;
    }

    /* Channel Stats */
    .channel-stats {
      display: flex;
      justify-content: space-around;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      color: #aaa;
      border-top: 1px solid rgba(0,255,102,0.3);
      padding-top: 1rem;
    }

    .channel-stat {
      text-align: center;
    }

    .channel-stat-value {
      font-size: 1.4rem;
      font-weight: bold;
      color: #00ff66;
      display: block;
    }

    .channel-stat-label {
      font-size: 0.8rem;
      color: #888;
      text-transform: uppercase;
    }

    /* Enter Room Button */
    .enter-room-btn {
      background: linear-gradient(135deg, #00ff66 0%, #00cc55 100%);
      color: #000;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 6px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px rgba(0,255,102,0.5);
    }

    .enter-room-btn:hover {
      background: linear-gradient(135deg, #00ff99 0%, #00dd66 100%);
      box-shadow: 0 0 30px rgba(0,255,102,0.8);
      transform: scale(1.05);
    }

    .enter-room-btn:active {
      transform: scale(0.98);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .rooms-header h1 {
        font-size: 2.2rem;
      }

      .channels-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem 3rem;
      }

      .channel-icon {
        font-size: 3rem;
      }

      .channel-name {
        font-size: 1.3rem;
      }
    }

    /* VHS Overlay Scanlines */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(255,255,255,0.03) 2px,
        rgba(255,255,255,0.03) 4px
      );
      pointer-events: none;
      z-index: 999;
    }

    /* Glitch Effect for Title */
    @keyframes glitch {
      0%, 100% { text-shadow: 0 0 20px rgba(0,255,102,0.6); }
      50% { text-shadow: -2px 0 10px #ff006e, 2px 0 10px #00ff66; }
    }

    .rooms-header h1:hover {
      animation: glitch 0.3s infinite;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <?php require_once("../templates/navbar.php"); ?>

  <!-- Header -->
  <div class="rooms-header">
    <h1>⚡ SOCIAL ROOMS ⚡</h1>
    <p>Select a room to join other hackers and compete</p>
  </div>

  <!-- Rooms Grid -->
  <div class="channels-grid">
    <?php foreach ($rooms as $room): ?>
      <div class="channel-card" onclick="enterRoom(<?php echo $room['id']; ?>)">
        <div class="channel-card-inner">
          <div>
            <div class="channel-icon"><?php echo htmlspecialchars($room['icon_emoji']); ?></div>
            <div class="channel-name"><?php echo htmlspecialchars($room['name']); ?></div>
            <div class="channel-category"><?php echo htmlspecialchars($room['category']); ?></div>
            <div class="channel-description"><?php echo htmlspecialchars($room['description']); ?></div>
          </div>

          <div>
            <div class="channel-stats">
              <div class="channel-stat">
                <span class="channel-stat-value"><?php 
                  // Count how many active users (sessions created in last 30 mins)
                  // Rough estimate by counting unique users in this room's recent messages
                  $usersSql = "SELECT COUNT(DISTINCT user_id) as unique_users FROM ChatMessages WHERE room_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
                  $usersStmt = $conn->prepare($usersSql);
                  $usersStmt->bind_param("i", $room['id']);
                  $usersStmt->execute();
                  $usersResult = $usersStmt->get_result()->fetch_assoc();
                  echo $usersResult['unique_users'] ?? 0;
                ?></span>
                <span class="channel-stat-label">Active</span>
              </div>
              <div class="channel-stat">
                <span class="channel-stat-value"><?php echo $room['message_count']; ?></span>
                <span class="channel-stat-label">Messages</span>
              </div>
              <div class="channel-stat">
                <span class="channel-stat-value"><?php 
                  // Count puzzles tagged with this room's category
                  $puzzlesSql = "SELECT COUNT(*) as puzzle_count FROM PuzzleStore WHERE category = ?";
                  $puzzlesStmt = $conn->prepare($puzzlesSql);
                  $puzzlesStmt->bind_param("s", $room['category']);
                  $puzzlesStmt->execute();
                  $puzzlesResult = $puzzlesStmt->get_result()->fetch_assoc();
                  echo $puzzlesResult['puzzle_count'] ?? 0;
                ?></span>
                <span class="channel-stat-label">Puzzles</span>
              </div>
            </div>

            <button class="enter-room-btn">ENTER ROOM</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Footer -->
  <footer class="text-center mt-5 mb-3 text-secondary">
    <small>&copy; 2025 Cypher Deck - Connect • Compete • Conquer</small>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Navigate to specific room
    function enterRoom(roomId) {
      window.location = `/cypherdeck-main1/pages/social/room.php?id=${roomId}`;
    }
  </script>

</body>

</html>
