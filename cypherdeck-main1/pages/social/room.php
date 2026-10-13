<?php
session_start();
require_once("../../pages/php/process_db.php");

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /cypherdeck-main1/pages/auth/login.php');
    exit();
}

// get the room id from url 
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// fetch the room details 
$room_sql = "SELECT * FROM Rooms WHERE id = ?";
$room_stmt = $conn->prepare($room_sql);
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room = $room_stmt->get_result()->fetch_assoc();

// if room doesn't exist, send to rooms page 
if (!$room) {
    header('Location: /cypherdeck-main1/pages/social/rooms.php');
    exit();
}

// get the last 50 messages so as not to strain the site
$chat_sql = "SELECT cm.id, cm.user_id, cm.message, cm.created_at, u.username 
             FROM ChatMessages cm
             JOIN Users u ON cm.user_id = u.id
             WHERE cm.room_id = ?
             ORDER BY cm.created_at DESC
             LIMIT 50";
$chat_stmt = $conn->prepare($chat_sql);
$chat_stmt->bind_param("i", $room_id);
$chat_stmt->execute();
$messages = array_reverse($chat_stmt->get_result()->fetch_all(MYSQLI_ASSOC));

// get puzzles for this room to add to the list
$puzzle_sql = "SELECT id, title, base_points, difficulty 
               FROM PuzzleStore 
               WHERE category LIKE ? OR tags LIKE ?
               ORDER BY difficulty ASC, base_points DESC
               LIMIT 20";
$puzzle_stmt = $conn->prepare($puzzle_sql);
$category_pattern = '%' . $room['category'] . '%';
$puzzle_stmt->bind_param("ss", $category_pattern, $category_pattern);
$puzzle_stmt->execute();
$puzzles = $puzzle_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// get the sticky notes for this room and their position
$sticky_sql = "SELECT id, title, content, x_position, y_position, color, pinned
               FROM StickyNotes
               WHERE room_id = ? AND user_id = ?
               ORDER BY created_at DESC";
$sticky_stmt = $conn->prepare($sticky_sql);
$sticky_stmt->bind_param("ii", $room_id, $_SESSION['user_id']);
$sticky_stmt->execute();
$sticky_notes = $sticky_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($room['name']); ?> | Cypher Deck</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../css/style.php" rel="stylesheet" type="text/css">
  <link href="../../css/room_style.css" rel="stylesheet" type="text/css">
</head>

<body>

  <!-- Navbar -->
  <?php require_once("../templates/navbar.php"); ?>

  <!-- Desktop Container -->
  <div class="desktop" id="desktop" data-room-id="<?php echo $room_id; ?>">

    <!-- Chat Window -->
    <div class="window" id="chat-window">
      <div class="window-titlebar" id="chat-titlebar">
        <div class="window-title">CHAT: <?php echo htmlspecialchars($room['name']); ?></div>
        <div class="window-controls">
          <div class="window-btn" onclick="minimizeWindow('chat-window')">_</div>
          <div class="window-btn" onclick="maximizeWindow('chat-window')">□</div>
          <div class="window-btn" onclick="closeWindow('chat-window')">×</div>
        </div>
      </div>
      <div class="window-content" id="chat-content">
        <div class="chat-messages" id="messages">
          <?php foreach ($messages as $msg): ?>
            <div class="chat-message">
              <div>
                <span class="chat-user"><?php echo htmlspecialchars($msg['username']); ?></span>
                <span class="chat-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
              </div>
              <div class="chat-text"><?php echo htmlspecialchars($msg['message']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="chat-input-area">
        <input type="text" id="chat-input" placeholder="Type message... (Enter to send)" onkeypress="handleChatInput(event, <?php echo $room_id; ?>)" style="flex: 1; width: 100%; padding: 10px; border: 1px solid #00ff66; background: #000; color: #00ff66;">
      </div>
      <div class="window-resize-handle" onmousedown="startResize(event, 'chat-window')"></div>
    </div>

    <!-- Puzzles Window -->
    <div class="window" id="puzzles-window">
      <div class="window-titlebar" id="puzzles-titlebar">
        <div class="window-title">PUZZLES</div>
        <div class="window-controls">
          <div class="window-btn" onclick="minimizeWindow('puzzles-window')">_</div>
          <div class="window-btn" onclick="maximizeWindow('puzzles-window')">□</div>
          <div class="window-btn" onclick="closeWindow('puzzles-window')">×</div>
        </div>
      </div>
      <div class="window-content" id="puzzles-content">
        <div class="puzzle-list">
          <?php foreach ($puzzles as $puzzle): ?>
            <div class="puzzle-item" onclick="solvePuzzle(<?php echo $puzzle['id']; ?>)">
              <div class="puzzle-title"><?php echo htmlspecialchars($puzzle['title']); ?></div>
              <div class="puzzle-meta">
                <span><?php echo htmlspecialchars($puzzle['difficulty']); ?></span> • 
                <span><?php echo $puzzle['base_points']; ?> pts</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="window-resize-handle" onmousedown="startResize(event, 'puzzles-window')"></div>
    </div>

    <!-- Sticky Notes -->
    <div id="sticky-notes-container">
      <?php foreach ($sticky_notes as $note): ?>
        <div class="sticky-note" data-note-id="<?php echo $note['id']; ?>" style="left: <?php echo $note['x_position']; ?>px; top: <?php echo $note['y_position']; ?>px;">
          <div class="sticky-note-controls">
            <button class="sticky-btn" onclick="editSticky(<?php echo $note['id']; ?>)" title="Edit">✏️</button>
            <button class="sticky-btn" onclick="deleteSticky(<?php echo $note['id']; ?>)" title="Delete">×</button>
          </div>
          <?php if ($note['title']): ?>
            <div class="sticky-note-title"><?php echo htmlspecialchars($note['title']); ?></div>
          <?php endif; ?>
          <div class="sticky-note-content"><?php echo htmlspecialchars($note['content']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>

  <!-- Create Note Button -->
  <button class="create-note-btn" onclick="createNewSticky()">+ NEW NOTE</button>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Room functionality script -->
  <script src="/cypherdeck-main1/pages/social/room.js"></script>

</body>

</html>




