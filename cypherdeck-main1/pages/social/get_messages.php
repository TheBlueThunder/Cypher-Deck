<?php
session_start();
require_once("../../pages/php/process_db.php");

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    http_response_code(400);
    exit();
}

$room_id = (int)$_GET['room_id'];

// Fetch recent chat messages, limited to 50 for better performance
// I'm using a JOIN so I can get both the message and the username in one query
$sql = "SELECT cm.id, cm.user_id, cm.message, cm.created_at, u.username 
        FROM ChatMessages cm
        JOIN Users u ON cm.user_id = u.id
        WHERE cm.room_id = ?
        ORDER BY cm.created_at ASC
        LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Loop through each message and display it
foreach ($messages as $msg): ?>
  <div class="chat-message">
    <div>
      <span class="chat-user"><?php echo htmlspecialchars($msg['username']); ?></span>
      <span class="chat-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
    </div>
    <div class="chat-text"><?php echo htmlspecialchars($msg['message']); ?></div>
  </div>
<?php endforeach;
?>
